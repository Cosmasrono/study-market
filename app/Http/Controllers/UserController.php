<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\MembershipPayment;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaction;
use App\Models\Enrollment;
use App\Models\Attempt;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
 
 
    /**
     * Show the user profile page.
     */
    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    /**
     * Update the user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // Update basic info
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        // Update password if provided
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Show user's enrolled courses.
     */
    public function myCourses()
    {
        $user = Auth::user();
        // This would typically get courses from a pivot table or enrollments table
        $courses = collect(); // Empty collection for now
        
        return view('user.courses', compact('user', 'courses'));
    }

    /**
     * Show user's purchased books.
     */
    public function myBooks()
    {
        $user = Auth::user();
        // This would typically get books from a purchases table
        $books = collect(); // Empty collection for now
        
        return view('user.books', compact('user', 'books'));
    }

    /**
     * Show user's transaction history
     */
    public function transactions()
    {
        $user = Auth::user();
        $transactions = \App\Models\Transaction::where('user_id', $user->id)
            ->with(['book', 'video', 'course'])
            ->latest()
            ->paginate(15);

        $upcomingPayments = \App\Models\Transaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->orderBy('expires_at', 'asc')
            ->get();

        return view('user.transactions', compact('transactions', 'upcomingPayments', 'user'));
    }

    /**
     * Renew a specific payment
     */
    public function renewPayment($transactionId)
    {
        try {
            $transaction = \App\Models\Transaction::findOrFail($transactionId);

            // Ensure the transaction belongs to the current user
            if ($transaction->user_id !== Auth::id()) {
                return back()->with('error', 'Unauthorized access to transaction.');
            }

            // Redirect to appropriate payment page based on content type
            switch ($transaction->content_type) {
                case 'book':
                    return redirect()->route('books.purchase', ['book' => $transaction->content_id]);
                case 'video':
                    return redirect()->route('videos.purchase', ['video' => $transaction->content_id]);
                case 'membership':
                    return redirect()->route('membership.renew');
                default:
                    return back()->with('error', 'Unable to renew this type of transaction.');
            }
        } catch (\Exception $e) {
            \Log::error('Payment renewal error', [
                'transaction_id' => $transactionId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to process payment renewal.');
        }
    }

    public function generateReceipt($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        
        // Ensure the payment belongs to the authenticated user
        if ($payment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        // Generate PDF receipt
        $pdf = PDF::loadView('receipts.payment', [
            'payment' => $payment,
            'user' => Auth::user()
        ]);

        return $pdf->download("receipt_{$payment->id}.pdf");
    }

    public function renewMembership()
    {
        $user = Auth::user();

        // Redirect to membership plans or payment page
        return view('membership.renew', [
            'user' => $user,
            'currentMembership' => $user->membership_type
        ]);
    }

    public function listOrders()
    {
        $user = Auth::user();
        $orders = \App\Models\Order::where('user_id', $user->id)
            ->latest()
            ->paginate(10);
        
        return view('user.orders', compact('orders'));
    }

    public function listTestResults()
    {
        $user = Auth::user();
        $testAttempts = \App\Models\TestAttempt::where('user_id', $user->id)
            ->with('exam')
            ->latest()
            ->paginate(10);
        
        return view('user.test-results', compact('testAttempts'));
    }
 

    /**
     * Fetch comprehensive user data from multiple tables
     */
    public function getUserData()
    {
        $user = Auth::user();

        // Fetch user details with related data
        $userData = User::with([
            'membershipPayments' => function($query) {
                $query->latest()->take(10);
            },
            'transactions' => function($query) {
                $query->latest()->take(10);
            },
            'enrollments.course',
            'attempts.exam'
        ])->find($user->id);

        // Aggregate data from different tables
        $aggregateData = [
            'total_courses' => Enrollment::where('user_id', $user->id)->count(),
            'total_transactions' => Transaction::where('user_id', $user->id)->count(),
            'total_test_attempts' => Attempt::where('user_id', $user->id)->count(),
            'total_spent' => Transaction::where('user_id', $user->id)
                ->where('status', 'paid')
                ->sum('amount'),
        ];

        // Complex query joining multiple tables
        $comprehensiveReport = DB::table('users')
            ->leftJoin('membership_payments', 'users.id', '=', 'membership_payments.user_id')
            ->leftJoin('transactions', 'users.id', '=', 'transactions.user_id')
            ->leftJoin('enrollments', 'users.id', '=', 'enrollments.user_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(DISTINCT membership_payments.id) as membership_payment_count'),
                DB::raw('COUNT(DISTINCT transactions.id) as transaction_count'),
                DB::raw('COUNT(DISTINCT enrollments.id) as enrollment_count'),
                DB::raw('SUM(transactions.amount) as total_spending')
            )
            ->where('users.id', $user->id)
            ->groupBy('users.id', 'users.name', 'users.email')
            ->first();

        // Recent activity across different tables
        $recentActivity = collect([
            'membership_payments' => MembershipPayment::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get(),
            'transactions' => Transaction::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get(),
            'enrollments' => Enrollment::where('user_id', $user->id)
                ->with('course')
                ->latest()
                ->take(5)
                ->get(),
            'attempts' => Attempt::where('user_id', $user->id)
                ->with('exam')
                ->latest()
                ->take(5)
                ->get()
        ]);

        return response()->json([
            'user_details' => $userData,
            'aggregate_data' => $aggregateData,
            'comprehensive_report' => $comprehensiveReport,
            'recent_activity' => $recentActivity
        ]);
    }

    /**
     * Advanced filtering and searching across tables
     */
    public function searchUserData(Request $request)
    {
        $query = $request->input('query');
        $type = $request->input('type', 'all');

        $results = [];

        // Search across multiple tables
        switch($type) {
            case 'transactions':
                $results = Transaction::where('user_id', Auth::id())
                    ->where(function($q) use ($query) {
                        $q->where('content_type', 'LIKE', "%{$query}%")
                          ->orWhere('amount', 'LIKE', "%{$query}%");
                    })
                    ->get();
                break;

            case 'enrollments':
                $results = Enrollment::with('course')
                    ->where('user_id', Auth::id())
                    ->whereHas('course', function($q) use ($query) {
                        $q->where('title', 'LIKE', "%{$query}%");
                    })
                    ->get();
                break;

            default:
                // Comprehensive search across tables
                $results = [
                    'transactions' => Transaction::where('user_id', Auth::id())
                        ->where(function($q) use ($query) {
                            $q->where('content_type', 'LIKE', "%{$query}%")
                              ->orWhere('amount', 'LIKE', "%{$query}%");
                        })
                        ->get(),
                    'enrollments' => Enrollment::with('course')
                        ->where('user_id', Auth::id())
                        ->whereHas('course', function($q) use ($query) {
                            $q->where('title', 'LIKE', "%{$query}%");
                        })
                        ->get(),
                    'membership_payments' => MembershipPayment::where('user_id', Auth::id())
                        ->where(function($q) use ($query) {
                            $q->where('transaction_id', 'LIKE', "%{$query}%")
                              ->orWhere('amount', 'LIKE', "%{$query}%");
                        })
                        ->get()
                ];
                break;
        }

        return response()->json($results);
    }




    /**
 * Show the user dashboard.
 */
public function dashboard()
{
    $user = Auth::user();
    
    // Eager load relationships to avoid N+1 queries
    $user->load([
        'membershipPayments' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        },
        'enrollments.course',
        'transactions' => function($query) {
            $query->latest()->limit(10);
        },
        'attempts.exam' => function($query) {
            $query->latest()->limit(10);
        }
    ]);
    
    // Debug log (remove in production)
    \Log::info('Dashboard Data Check', [
        'user_id' => $user->id,
        'membership_status' => $user->membership_status,
        'subscription_end_date' => $user->subscription_end_date ? $user->subscription_end_date->toDateTimeString() : null,
        'current_subscription_type' => $user->current_subscription_type,
        'is_subscription_active' => $user->is_subscription_active,
        'has_membership' => $user->hasMembership(),
        'days_until_expiry' => $user->days_until_expiry,
        'total_membership_payments' => $user->membershipPayments()->count(),
        'completed_payments' => $user->membershipPayments()->where('status', 'completed')->count(),
        'latest_payment' => $user->membershipPayments()->latest()->first()?->toArray(),
    ]);
    
    return view('user.dashboard', compact('user'));
}

/**
 * Show user's membership payments
 */
public function getMembershipPayments()
{
    $user = Auth::user();
    
    // Fetch membership payments with proper ordering
    $membershipPayments = MembershipPayment::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    
    // Debug log
    \Log::info('Membership Payments Query', [
        'user_id' => $user->id,
        'total_count' => MembershipPayment::where('user_id', $user->id)->count(),
        'completed_count' => MembershipPayment::where('user_id', $user->id)->where('status', 'completed')->count(),
        'pending_count' => MembershipPayment::where('user_id', $user->id)->where('status', 'pending')->count(),
    ]);
    
    return view('user.membership-payments', compact('membershipPayments', 'user'));
}
}