<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MembershipPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // =============================================================================
    // REGISTRATION
    // =============================================================================

    public function showRegistrationForm()
    {
        return view('auth.register', [
            'membership_fee' => User::MEMBERSHIP_FEE
        ]);
    }

    public function register(Request $request)
    {
        Log::info('Registration started', ['email' => $request->email]);
        
        try {
            $validatedData = $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required', 
                    'confirmed', 
                    Password::min(8)->letters()->mixedCase()->numbers()->symbols()
                ],
                'phone_number' => 'required|regex:/^254[0-9]{9}$/',
                'payment_method' => 'required|in:mpesa,card',
                'terms' => 'required|accepted'
            ], [
                'password' => 'Password must contain uppercase, lowercase, numbers, and symbols.',
                'phone_number.regex' => 'Enter valid Kenyan number starting with 254.',
                'email.unique' => 'Email already registered. Please login instead.',
                'terms.accepted' => 'You must accept terms and conditions.'
            ]);

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'name' => explode('@', $validatedData['email'])[0],
                'membership_status' => 'pending'
            ]);

            // Create payment record
            $payment = MembershipPayment::create([
                'user_id' => $user->id,
                'amount' => User::MEMBERSHIP_FEE,
                'payment_method' => $validatedData['payment_method'],
                'transaction_id' => MembershipPayment::generateTransactionId(),
                'phone_number' => $validatedData['phone_number'],
                'status' => 'pending'
            ]);

            DB::commit();
            Log::info('User and payment created', ['user_id' => $user->id, 'payment_id' => $payment->id]);

            // Process payment
            return $this->processPayment($user, $payment, $validatedData);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())
                        ->withInput($request->except('password', 'password_confirmation'));
                        
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Registration failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])
                        ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    // =============================================================================
    // PAYMENT PROCESSING
    // =============================================================================

    private function processPayment(User $user, MembershipPayment $payment, array $data)
    {
        if ($data['payment_method'] === 'mpesa') {
            return $this->processMpesaPayment($user, $payment, $data['phone_number']);
        } else {
            return $this->processCardPayment($user, $payment);
        }
    }

    private function processMpesaPayment(User $user, MembershipPayment $payment, $phoneNumber)
    {
        Log::info('Processing M-Pesa payment', ['user_id' => $user->id, 'payment_id' => $payment->id]);

        try {
            $mpesaService = new \App\Services\MpesaService();
            
            $result = $mpesaService->stkPush(
                $phoneNumber,
                $payment->amount,
                'MEMBERSHIP_' . $user->id . '_' . time(),
                'Annual Membership Fee'
            );

            if ($result['success']) {
                $payment->update([
                    'payment_gateway' => 'safaricom',
                    'reference_id' => $result['checkout_request_id'],
                    'payment_data' => $result
                ]);

                return view('auth.payment-confirmation', [
                    'user' => $user,
                    'payment' => $payment,
                    'phone_number' => $phoneNumber,
                    'checkout_request_id' => $result['checkout_request_id'],
                    'message' => $result['message'] ?? 'Check your phone for M-Pesa prompt'
                ]);
            } else {
                Log::error('STK Push failed', ['error' => $result['message'] ?? 'Unknown error']);
                return back()->withErrors(['error' => 'M-Pesa payment failed: ' . ($result['message'] ?? 'Please try again.')])
                            ->withInput();
            }

        } catch (\Exception $e) {
            Log::error('M-Pesa exception', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Payment system error. Please try again.'])
                        ->withInput();
        }
    }

    private function processCardPayment(User $user, MembershipPayment $payment)
    {
        Log::info('Processing card payment', ['user_id' => $user->id, 'payment_id' => $payment->id]);
        
        return view('auth.card-payment', [
            'user' => $user,
            'payment' => $payment
        ]);
    }

    public function confirmPayment(Request $request)
    {
        try {
            $request->validate([
                'transaction_id' => 'required|exists:membership_payments,transaction_id'
            ]);

            $payment = MembershipPayment::where('transaction_id', $request->transaction_id)->first();
            
            if ($payment->status === 'completed') {
                Auth::login($payment->user);
                return redirect('/')->with('success', 'Welcome! Your membership is active.');
            }

            // Mark as completed (in production, verify with payment provider)
            $payment->markAsCompleted();
            Auth::login($payment->user);

            Log::info('Payment confirmed and user logged in', ['user_id' => $payment->user->id]);

            return redirect('/')->with('success', 'Registration completed successfully! Welcome to our platform.');

        } catch (\Exception $e) {
            Log::error('Payment confirmation failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Payment confirmation failed. Please try again.']);
        }
    }

    // =============================================================================
    // LOGIN & LOGOUT
    // =============================================================================

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            
            Log::info('User logged in', ['user_id' => $user->id, 'membership_status' => $user->membership_status]);
            
            // Redirect based on membership status
            if ($user->membershipPending()) {
                return redirect()->route('membership.payment')
                    ->with('warning', 'Please complete your membership payment.');
            }

            if ($user->membershipExpired()) {
                return redirect()->route('membership.payment')
                    ->with('warning', 'Your membership has expired. Please renew to continue.');
            }

            if ($user->membershipSuspended()) {
                return redirect()->route('home')
                    ->with('error', 'Your membership is suspended. Contact support for assistance.');
            }

            return redirect()->intended('/')->with('success', 'Welcome back!');
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        Log::info('User logged out', ['user_id' => $userId]);
        return redirect('/')->with('success', 'Logged out successfully.');
    }

    // =============================================================================
    // MEMBERSHIP MANAGEMENT
    // =============================================================================

    public function showMembershipPayment()
    {
        $user = Auth::user();
        
        if ($user->hasMembership()) {
            return redirect('/')->with('info', 'You already have an active membership.');
        }

        $pendingPayment = $user->getPendingMembershipPayment();

        return view('auth.membership-payment', [
            'user' => $user,
            'membership_fee' => User::MEMBERSHIP_FEE,
            'payment' => $pendingPayment
        ]);
    }

    public function processMembershipPayment(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'payment_method' => 'required|in:mpesa,card',
                'phone_number' => 'required_if:payment_method,mpesa|regex:/^254[0-9]{9}$/',
            ]);

            $user = Auth::user();

            // Use existing pending payment or create new one
            $payment = $user->getPendingMembershipPayment() ?? MembershipPayment::create([
                'user_id' => $user->id,
                'amount' => User::MEMBERSHIP_FEE,
                'payment_method' => $validatedData['payment_method'],
                'transaction_id' => MembershipPayment::generateTransactionId(),
                'phone_number' => $validatedData['phone_number'] ?? null,
                'status' => 'pending'
            ]);

            return $this->processPayment($user, $payment, $validatedData);

        } catch (\Exception $e) {
            Log::error('Membership payment processing failed', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            return back()->withErrors(['error' => 'Payment processing failed. Please try again.']);
        }
    }

    // =============================================================================
    // API ENDPOINTS
    // =============================================================================

    public function membershipStatus()
    {
        $user = Auth::user();
        
        return response()->json([
            'membership_status' => $user->membership_status,
            'membership_expires_at' => $user->membership_expires_at?->format('Y-m-d H:i:s'),
            'has_membership' => $user->hasMembership(),
            'days_until_expiry' => $user->days_until_expiry,
            'pending_payments' => $user->membershipPayments()->where('status', 'pending')->count()
        ]);
    }

    public function checkMembershipPaymentStatus($transaction_id)
    {
        $payment = MembershipPayment::where('transaction_id', $transaction_id)->first();
        
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        
        return response()->json([
            'status' => $payment->status,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
            'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s')
        ]);
    }
}