<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
     * Show the user dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get user statistics
        $stats = [
            'total_courses' => 0, // You can update this based on your course model
            'completed_courses' => 0,
            'total_books' => 0,
            'recent_activities' => []
        ];

        return view('user.dashboard', compact('user', 'stats'));
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
}