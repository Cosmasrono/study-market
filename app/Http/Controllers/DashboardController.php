<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the user dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get user statistics
        $stats = [
            'membership_status' => $user->membership_status,
            'subscription_type' => $user->current_subscription_type,
            'subscription_expires' => $user->subscription_end_date,
            'days_until_expiry' => $user->days_until_expiry,
            'is_active' => $user->is_subscription_active,
            'total_payments' => $user->membershipPayments()->count(),
            'total_spent' => $user->membershipPayments()->where('status', 'completed')->sum('amount'),
        ];

        // Get recent payments
        $recentPayments = $user->membershipPayments()
            ->latest()
            ->take(5)
            ->get();

        // Get membership status details
        $membershipDetails = $user->membership_status_with_days;

        return view('user.dashboard', compact('user', 'stats', 'recentPayments', 'membershipDetails'));
    }

    /**
     * Show user orders
     */
    public function orders()
    {
        $user = Auth::user();
        
        $orders = $user->transactions()
            ->where('status', 'paid')
            ->latest()
            ->paginate(10);

        return view('user.orders', compact('orders'));
    }

    /**
     * Show user test results
     */
    public function results()
    {
        $user = Auth::user();
        
        $results = $user->attempts()
            ->with('exam')
            ->latest()
            ->paginate(10);

        return view('user.results', compact('results'));
    }

    /**
     * Generate and download user receipts
     */
    public function printReceipts()
    {
        $user = Auth::user();
        
        // Fetch paid transactions (receipts)
        $receipts = $user->transactions()
            ->where('status', 'paid')
            ->latest()
            ->get();
        
        // Generate PDF
        $pdf = PDF::loadView('user.receipts', compact('receipts', 'user'));
        
        // Download PDF
        return $pdf->download('receipts_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Generate and download user reports
     */
    public function printReports()
    {
        $user = Auth::user();
        
        // Comprehensive user report
        $stats = [
            'membership_status' => $user->membership_status,
            'subscription_type' => $user->current_subscription_type,
            'total_payments' => $user->membershipPayments()->count(),
            'total_spent' => $user->membershipPayments()->where('status', 'completed')->sum('amount'),
            'exam_attempts' => $user->attempts()->count(),
            'successful_exams' => $user->attempts()->where('score_percentage', '>=', 50)->count(), // Consider 50% and above as successful
        ];
        
        // Fetch detailed transaction and exam history
        $transactions = $user->transactions()->latest()->take(10)->get();
        $examAttempts = $user->attempts()->with('exam')->latest()->take(10)->get();
        
        // Generate PDF
        $pdf = PDF::loadView('user.report', compact('user', 'stats', 'transactions', 'examAttempts'));
        
        // Download PDF
        return $pdf->download('user_report_' . date('Y-m-d') . '.pdf');
    }
}