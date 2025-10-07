<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PDFGeneratorService;
use App\Models\MembershipPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    /**
     * @var PDFGeneratorService
     */
    protected $pdfService;

    /**
     * Constructor
     *
     * @param PDFGeneratorService $pdfService
     */
    public function __construct(PDFGeneratorService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Generate a PDF receipt for a specific payment
     *
     * @param int $paymentId
     * @return \Illuminate\Http\Response
     */
    public function generatePaymentReceipt($paymentId)
    {
        try {
            $payment = MembershipPayment::findOrFail($paymentId);

            Log::info('Generating payment receipt', [
                'payment_id' => $paymentId,
                'user_id' => $payment->user_id
            ]);

            return $this->pdfService->generatePaymentReceipt($payment);
        } catch (\Exception $e) {
            Log::error('Failed to generate payment receipt', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to generate payment receipt: ' . $e->getMessage());
        }
    }

    /**
     * Generate a user-specific payment receipt
     *
     * @param int $paymentId
     * @return \Illuminate\Http\Response
     */
    public function generateUserPaymentReceipt($paymentId)
    {
        try {
            $payment = MembershipPayment::where('user_id', Auth::id())
                ->findOrFail($paymentId);

            Log::info('Generating user payment receipt', [
                'payment_id' => $paymentId,
                'user_id' => $payment->user_id
            ]);

            return $this->pdfService->generatePaymentReceipt($payment);
        } catch (\Exception $e) {
            Log::error('Failed to generate user payment receipt', [
                'payment_id' => $paymentId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to generate payment receipt: ' . $e->getMessage());
        }
    }

    /**
     * Generate a comprehensive payment report
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generatePaymentReport(Request $request)
    {
        try {
            $startDate = $request->input('start_date') 
                ? Carbon::parse($request->input('start_date')) 
                : null;
            
            $endDate = $request->input('end_date') 
                ? Carbon::parse($request->input('end_date')) 
                : null;

            Log::info('Generating payment report', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return $this->pdfService->generatePaymentReport($startDate, $endDate);
        } catch (\Exception $e) {
            Log::error('Failed to generate payment report', [
                'start_date' => $startDate ?? 'null',
                'end_date' => $endDate ?? 'null',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to generate payment report: ' . $e->getMessage());
        }
    }

    /**
     * Generate a user-specific payment report
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateUserPaymentReport(Request $request)
    {
        try {
            $startDate = $request->input('start_date') 
                ? Carbon::parse($request->input('start_date')) 
                : null;
            
            $endDate = $request->input('end_date') 
                ? Carbon::parse($request->input('end_date')) 
                : null;

            Log::info('Generating user payment report', [
                'user_id' => Auth::id(),
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            // Modify the service method to filter by user
            return $this->pdfService->generateUserPaymentReport(Auth::id(), $startDate, $endDate);
        } catch (\Exception $e) {
            Log::error('Failed to generate user payment report', [
                'user_id' => Auth::id(),
                'start_date' => $startDate ?? 'null',
                'end_date' => $endDate ?? 'null',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to generate payment report: ' . $e->getMessage());
        }
    }

    /**
     * Generate a membership report
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateMembershipReport(Request $request)
    {
        try {
            $startDate = $request->input('start_date') 
                ? Carbon::parse($request->input('start_date')) 
                : null;
            
            $endDate = $request->input('end_date') 
                ? Carbon::parse($request->input('end_date')) 
                : null;

            Log::info('Generating membership report', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return $this->pdfService->generateMembershipReport($startDate, $endDate);
        } catch (\Exception $e) {
            Log::error('Failed to generate membership report', [
                'start_date' => $startDate ?? 'null',
                'end_date' => $endDate ?? 'null',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to generate membership report: ' . $e->getMessage());
        }
    }

    public function generateMembershipPaymentReceipt($paymentId)
    {
        // Find the payment and ensure it belongs to the authenticated user
        $payment = MembershipPayment::where('id', $paymentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Ensure the payment is completed
        if (!$payment->isCompleted()) {
            abort(403, 'This payment receipt is not available.');
        }

        // Generate PDF
        $pdf = PDF::loadView('pdf.membership-payment-receipt', [
            'payment' => $payment,
            'user' => $payment->user
        ]);

        // Set filename
        $filename = "membership_receipt_{$payment->transaction_id}.pdf";

        // Stream the PDF
        return $pdf->download($filename);
    }
}
