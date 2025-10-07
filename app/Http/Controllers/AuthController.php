<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MembershipPayment;
use App\Services\PayheroService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Register new user with PayHero payment
     */
/**
 * Register new user with PayHero payment
 */
public function register(Request $request)
{
    try {
        // Validate registration data
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'phone_number' => 'required|regex:/^254[17]\d{8}$/|unique:users,phone_number',
            'subscription_duration' => 'required|in:1_year,6_months',
            'payment_method' => 'required|in:payhero',
            'terms' => 'required|accepted'
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
            'phone_number.regex' => 'Invalid phone number. Use format: 254712345678 or 254101234567',
            'phone_number.unique' => 'This phone number is already registered.'
        ]);

        // Calculate subscription amount
        $subscriptionAmount = 1; // 1 KSh for testing
        $subscriptionDuration = $validated['subscription_duration']; // '1_year' or '6_months'

        // Generate name from email if not provided
        if (empty($validated['name'])) {
            $validated['name'] = ucfirst(explode('@', $validated['email'])[0]);
        }

        // Start database transaction
        DB::beginTransaction();

        try {
            // Create user account (inactive until payment)
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone_number' => $validated['phone_number'], // ✅ Changed from 'phone' to 'phone_number'
                'membership_status' => 'pending',
                'is_subscription_active' => false,
                'membership_fee_paid' => 0,
            ]);

            // Generate unique payment reference
            $transactionReference = 'PH-' . strtoupper(uniqid());

            // Create membership payment record
            $membershipPayment = MembershipPayment::create([
                'user_id' => $user->id,
                'amount' => $subscriptionAmount,
                'phone_number' => $validated['phone_number'],
                'reference' => $transactionReference,
                'transaction_reference' => $transactionReference,
                'status' => 'pending',
                'payment_method' => 'payhero',
                'subscription_duration' => $subscriptionDuration
            ]);

            Log::info('User Registration Created - Initiating PayHero Payment', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $validated['phone_number'],
                'amount' => $subscriptionAmount,
                'subscription' => $subscriptionDuration,
                'reference' => $transactionReference
            ]);

            // Initialize PayHero payment
            $payheroService = new PayheroService();
            
            $paymentResult = $payheroService->initiatePayment([
                'user_id' => $user->id,
                'amount' => $subscriptionAmount,
                'phone_number' => $validated['phone_number'],
                'transaction_reference' => $transactionReference,
                'customer_name' => $user->name,
                'email' => $user->email,
                'description' => 'Membership - ' . $subscriptionDuration
            ]);

            DB::commit();

            Log::info('Payment Initiated Successfully - STK Push Sent', [
                'user_id' => $user->id,
                'reference' => $transactionReference,
                'status' => $paymentResult['status'] ?? 'unknown',
                'fallback' => $paymentResult['fallback'] ?? false,
                'phone' => $validated['phone_number']
            ]);

            // Redirect to payment confirmation page
            return redirect()->route('payment.confirmation', ['reference' => $transactionReference])
                ->with('success', 'Registration successful! Please complete M-Pesa payment on your phone.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Registration/Payment Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $validated['email']
            ]);

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', 'Registration failed: ' . $e->getMessage());
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()
            ->withErrors($e->validator)
            ->withInput($request->except(['password', 'password_confirmation']));
    }
}

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return redirect()->intended(route('user.dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully!');
    }

   
  
 
    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Handle session expired
     */
    public function handleSessionExpired()
    {
        return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
    }

    /**
     * Extend session
     */
    public function extendSession(Request $request)
    {
        $request->session()->regenerate();
        return response()->json(['success' => true]);
    }




    /**
 * Show membership payment page
 */
public function showMembershipPayment()
{
    if (!auth()->check()) {
        return redirect()->route('login')->with('error', 'Please login to continue');
    }

    $user = auth()->user();
    
    if ($user->hasMembership()) {
        return redirect()->route('user.dashboard')
            ->with('info', 'You already have an active membership');
    }

    $membershipPrice = 1; // 1 KSh for testing
    
    return view('membership.payment', compact('membershipPrice'));
}

/**
 * Process membership payment
 */
public function processMembershipPayment(Request $request)
{
    try {
        $validated = $request->validate([
            'phone' => 'required|regex:/^254[17]\d{8}$/',
            'amount' => 'required|numeric|min:1'
        ]);

        $user = auth()->user();

        if ($user->hasMembership()) {
            return back()->with('error', 'You already have an active membership');
        }

        // Generate unique reference
        $reference = 'PH-' . strtoupper(uniqid());

        // Create membership payment record
        $payment = MembershipPayment::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'phone_number' => $validated['phone'],
            'reference' => $reference,
            'transaction_reference' => $reference,
            'status' => 'pending',
            'payment_method' => 'payhero',
            'subscription_duration' => '1_year' // Changed to match your User model
        ]);

        Log::info('Membership Payment Created', [
            'payment_id' => $payment->id,
            'reference' => $reference,
            'user_id' => $user->id,
            'amount' => $validated['amount']
        ]);

        // Initialize PayHero payment
        try {
            $payheroService = new PayheroService();
            
            $paymentResult = $payheroService->initiatePayment([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'phone_number' => $validated['phone'],
                'transaction_reference' => $reference,
                'customer_name' => $user->name ?? 'Customer',
                'description' => 'Annual Membership Payment'
            ]);

            Log::info('PayHero Payment Initiated', [
                'reference' => $reference,
                'result' => $paymentResult
            ]);

            // Redirect to confirmation page
            return redirect()->route('payment.confirmation', ['reference' => $reference])
                ->with('success', 'Payment initiated. Please complete payment on your phone.');

        } catch (\Exception $e) {
            Log::error('PayHero Initiation Failed', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to initiate payment. Please try again.');
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()->withErrors($e->validator)->withInput();
    } catch (\Exception $e) {
        Log::error('Membership payment processing failed', [
            'error' => $e->getMessage(),
            'user_id' => auth()->id()
        ]);

        return back()->with('error', 'Payment processing failed. Please try again.');
    }
}

/**
 * Show membership renewal page
 */
public function showMembershipRenewal()
{
    $user = auth()->user();
    $membershipPrice = 1; // 1 KSh for testing
    
    return view('membership.renew', compact('user', 'membershipPrice'));
}

/**
 * Process membership renewal
 */
public function processMembershipRenewal(Request $request)
{
    try {
        $validated = $request->validate([
            'phone' => 'required|regex:/^254[17]\d{8}$/',
            'amount' => 'required|numeric|min:1'
        ]);

        $user = auth()->user();
        $reference = 'REN-' . strtoupper(uniqid());

        // Create renewal payment record
        $payment = MembershipPayment::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'phone_number' => $validated['phone'],
            'reference' => $reference,
            'transaction_reference' => $reference,
            'status' => 'pending',
            'payment_method' => 'payhero',
            'subscription_duration' => '1_year',
            'is_renewal' => true
        ]);

        // Initialize payment
        try {
            $payheroService = new PayheroService();
            
            $paymentResult = $payheroService->initiatePayment([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'phone_number' => $validated['phone'],
                'transaction_reference' => $reference,
                'customer_name' => $user->name ?? 'Customer',
                'description' => 'Membership Renewal'
            ]);

            return redirect()->route('payment.confirmation', ['reference' => $reference])
                ->with('success', 'Renewal payment initiated. Please complete payment on your phone.');

        } catch (\Exception $e) {
            Log::error('PayHero Renewal Failed', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to initiate payment. Please try again.');
        }

    } catch (\Exception $e) {
        Log::error('Membership renewal failed', [
            'error' => $e->getMessage(),
            'user_id' => auth()->id()
        ]);

        return back()->with('error', 'Renewal processing failed. Please try again.');
    }
}

/**
 * Show membership status
 */
public function membershipStatus()
{
    $user = auth()->user();
    
    $membershipPayments = $user->membershipPayments()
        ->latest()
        ->paginate(10);
    
    return view('membership.status', compact('user', 'membershipPayments'));
}

/**
 * Show membership history
 */
public function membershipHistory()
{
    $user = auth()->user();
    
    $payments = $user->membershipPayments()
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    
    return view('membership.history', compact('payments'));
}

/**
 * Show manual payment page
 */
public function showManualPayment($reference)
{
    $payment = MembershipPayment::where('reference', $reference)
        ->orWhere('transaction_reference', $reference)
        ->firstOrFail();
    
    return view('payment.manual', compact('payment'));
}
}