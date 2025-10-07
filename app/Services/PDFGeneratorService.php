<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\MembershipPayment;
use App\Models\User;
use Carbon\Carbon;

class PDFGeneratorService
{
    /**
     * Generate a PDF receipt for a specific payment
     *
     * @param MembershipPayment $payment
     * @return \Illuminate\Http\Response
     */
    public function generatePaymentReceipt(MembershipPayment $payment)
    {
        $user = $payment->user;

        $data = [
            'payment' => $payment,
            'user' => $user,
            'receipt_number' => 'RCPT-' . $payment->id . '-' . now()->format('Ymd'),
            'generated_at' => now()
        ];

        $pdf = PDF::loadView('pdfs.payment_receipt', $data);
        
        return $pdf->download("receipt_{$payment->id}.pdf");
    }

    /**
     * Generate a comprehensive payment report
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return \Illuminate\Http\Response
     */
    public function generatePaymentReport($startDate = null, $endDate = null)
    {
        // Default to last 30 days if no dates provided
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        // Fetch payments within date range
        $payments = MembershipPayment::with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Aggregate data
        $reportData = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_payments' => $payments->count(),
            'total_revenue' => $payments->sum('amount'),
            'payments_by_method' => $payments->groupBy('payment_method')
                ->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'total' => $group->sum('amount')
                    ];
                }),
            'payments' => $payments
        ];

        $pdf = PDF::loadView('pdfs.payment_report', $reportData);
        
        return $pdf->download('payment_report_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Generate a user-specific payment report
     *
     * @param int $userId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return \Illuminate\Http\Response
     */
    public function generateUserPaymentReport($userId, $startDate = null, $endDate = null)
    {
        // Default to last 30 days if no dates provided
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        // Fetch user
        $user = User::findOrFail($userId);

        // Fetch payments within date range for this user
        $payments = MembershipPayment::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Aggregate data
        $reportData = [
            'user' => $user,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_payments' => $payments->count(),
            'total_spent' => $payments->sum('amount'),
            'payments_by_method' => $payments->groupBy('payment_method')
                ->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'total' => $group->sum('amount')
                    ];
                }),
            'payments' => $payments
        ];

        $pdf = PDF::loadView('pdfs.user_payment_report', $reportData);
        
        return $pdf->download('user_payment_report_' . $userId . '_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Generate a user membership report
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return \Illuminate\Http\Response
     */
    public function generateMembershipReport($startDate = null, $endDate = null)
    {
        // Default to last 30 days if no dates provided
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        // Fetch users with membership data
        $users = User::with('membershipPayments')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $reportData = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_users' => $users->count(),
            'active_memberships' => $users->filter(fn($user) => $user->hasMembership())->count(),
            'expired_memberships' => $users->filter(fn($user) => $user->membershipExpired())->count(),
            'users' => $users
        ];

        $pdf = PDF::loadView('pdfs.membership_report', $reportData);
        
        return $pdf->download('membership_report_' . now()->format('Ymd') . '.pdf');
    }
}
