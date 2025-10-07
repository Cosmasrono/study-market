<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CBTController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayheroController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Clean, organized, and well-documented route definitions
|--------------------------------------------------------------------------
*/

// =============================================================================
// PUBLIC ROUTES
// =============================================================================

// Home & Static Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/program', [CourseController::class, 'index'])->name('program');

// FAQ & Contact
Route::get('/faq', [FAQController::class, 'index'])->name('faq');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Public Testimonials
Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');

// CBT (Computer Based Testing)
Route::get('/cbt', [CBTController::class, 'index'])->name('cbt');
Route::get('/cbt/{id}', [CBTController::class, 'start'])->name('cbt.start');
Route::post('/cbt/{id}/submit', [CBTController::class, 'submit'])->name('cbt.submit');

// Public Books & Videos (Browse Only)
Route::get('/books', [BookController::class, 'index'])->name('books');
Route::get('/books/preview/{book}', [BookController::class, 'previewBook'])->name('books.preview');
Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/preview/{video}', [VideoController::class, 'previewVideo'])->name('videos.preview');

// =============================================================================
// AUTHENTICATION ROUTES
// =============================================================================

// Registration
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Login & Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// =============================================================================
// PAYMENT ROUTES (Public - No Auth Required)
// =============================================================================

// PayHero Payment Gateway
Route::prefix('payhero')->name('payhero.')->group(function () {
    
    // WEBHOOK CALLBACK - POST (For PayHero)
    Route::post('callback', function (Request $request) {
        // Log IMMEDIATELY when webhook is received
        Log::info('=== PayHero Webhook Received ===', [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'all_data' => $request->all(),
            'raw_content' => $request->getContent(),
            'query_params' => $request->query->all(),
        ]);
        
        try {
            // Forward to controller for processing
            return app(PayheroController::class)->callback($request);
            
        } catch (\Exception $e) {
            Log::error('PayHero Callback Processing Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    })->name('callback');
    
    // TEST ENDPOINT - GET (For browser testing)
    Route::get('callback', function (Request $request) {
        Log::info('PayHero Callback Test - GET Request', [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'query_params' => $request->query->all(),
        ]);
        
        return response()->json([
            'status' => 'ok',
            'message' => 'PayHero callback endpoint is active and reachable',
            'timestamp' => now()->toDateTimeString(),
            'endpoint' => url()->current(),
            'accepts' => 'POST requests from PayHero',
            'note' => 'This is a test endpoint. PayHero webhooks should use POST method.',
        ]);
    });
    
    // Payment Status Check
    Route::get('status/{reference}', [PayheroController::class, 'checkStatus'])->name('check-status');
});

// Payment Pages
Route::get('/payment/confirmation/{reference}', [PayheroController::class, 'showConfirmation'])
    ->name('payment.confirmation');
Route::get('/payment/manual/{reference}', [AuthController::class, 'showManualPayment'])
    ->name('payment.manual');
Route::post('/payment/complete', [PayheroController::class, 'completePayment'])
    ->name('confirm.payment'); // Keep old name for backward compatibility

// Membership Payment Status (AJAX)
Route::get('/membership/payment-status/{reference}', [PayheroController::class, 'checkStatus'])
    ->name('membership.payment.status');

// M-Pesa Callbacks (Public - No Auth)
Route::post('/api/mpesa/callback', [MpesaController::class, 'callback'])->name('mpesa.callback');
Route::post('/mpesa/membership/callback', [MpesaController::class, 'membershipCallback'])
    ->name('mpesa.membership.callback');

// =============================================================================
// AUTHENTICATED USER ROUTES
// =============================================================================

Route::middleware(['auth'])->group(function () {
    // Dashboard & Account
    Route::get('/account', [DashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/account/orders', [DashboardController::class, 'orders'])->name('orders');
    Route::get('/account/results', [DashboardController::class, 'results'])->name('results');
    // User Profile
    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
    
    // New routes for printing receipts and reports
    Route::get('/account/print-receipts', [DashboardController::class, 'printReceipts'])->name('account.print-receipts');
    Route::get('/account/print-reports', [DashboardController::class, 'printReports'])->name('account.print-reports');
    
    // Membership Management
    Route::prefix('membership')->name('membership.')->group(function () {
        Route::get('payment', [AuthController::class, 'showMembershipPayment'])->name('payment');
        Route::post('pay', [AuthController::class, 'processMembershipPayment'])->name('pay');
        Route::get('renew', [AuthController::class, 'showMembershipRenewal'])->name('renew');
        Route::post('renew', [AuthController::class, 'processMembershipRenewal'])->name('renew.process');
        Route::get('status', [AuthController::class, 'membershipStatus'])->name('status');
        Route::get('history', [AuthController::class, 'membershipHistory'])->name('history');
        Route::get('payments', [UserController::class, 'getMembershipPayments'])->name('payments');
        Route::get('payments/{payment}/receipt', [PDFController::class, 'generateMembershipPaymentReceipt'])
            ->name('payments.receipt');
    });
    
    // Books Management (Authenticated)
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::get('/books/{book}/check-access', [BookController::class, 'checkAccess'])->name('books.check-access');
    Route::get('/books/{book}/view', [BookController::class, 'view'])->name('books.view');
    Route::get('/books/{book}/download', [BookController::class, 'downloadBook'])->name('books.download');
    Route::get('/books/{book}/purchase', [BookController::class, 'purchase'])->name('books.purchase');
    Route::post('/books/{book}/process-purchase', [BookController::class, 'processPurchase'])->name('books.process_purchase');
    Route::get('/books/{book}/purchase/success/{transaction}', [BookController::class, 'purchaseSuccess'])
        ->name('books.purchase.success');
    Route::get('/books/purchase/failed/{checkout_request_id}', [BookController::class, 'purchaseFailed'])
        ->name('books.purchase.failed');
    
    // Videos Management (Authenticated)
    Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
    Route::get('/videos/{video}/check-access', [VideoController::class, 'checkAccess'])->name('videos.check-access');
    Route::get('/videos/{video}/play', [VideoController::class, 'play'])->name('videos.play');
    Route::get('/videos/{video}/download', [VideoController::class, 'download'])->name('videos.download');
    Route::get('/videos/{video}/purchase', [VideoController::class, 'purchase'])->name('videos.purchase');
    Route::post('/videos/{video}/process-purchase', [VideoController::class, 'processPurchase'])->name('videos.process_purchase');
    Route::get('/videos/{video}/purchase/success/{transaction}', [VideoController::class, 'purchaseSuccess'])
        ->name('videos.purchase.success');
    Route::get('/videos/purchase/failed/{checkout_request_id}', [VideoController::class, 'purchaseFailed'])
        ->name('videos.purchase.failed');
    
    // Exams (Basic Access)
    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
    
    // Membership-Protected Exam Features
    Route::middleware(['membership'])->group(function () {
        Route::get('/exams/{exam}/start', [ExamController::class, 'start'])->name('exams.start');
        Route::post('/exams/{exam}/submit', [ExamController::class, 'submit'])->name('exams.submit');
        Route::get('/exams/{exam}/result', [ExamController::class, 'result'])->name('exams.result');
    });
    
    // User Content & Transactions
    Route::get('/my-courses', [UserController::class, 'myCourses'])->name('user.courses');
    Route::get('/my-books', [UserController::class, 'myBooks'])->name('user.books');
    Route::get('/transactions', [UserController::class, 'transactions'])->name('user.transactions');
    Route::get('/orders', [UserController::class, 'listOrders'])->name('orders.index');
    Route::get('/test-results', [UserController::class, 'listTestResults'])->name('test.results');
    Route::get('/payments/renew/{transaction}', [UserController::class, 'renewPayment'])->name('payments.renew');
    
    // User Data API
    Route::get('/user/data', [UserController::class, 'getUserData'])->name('user.data');
    Route::get('/user/search', [UserController::class, 'searchUserData'])->name('user.search');
    
    // M-Pesa Payment Routes (Authenticated)
    Route::prefix('mpesa')->name('mpesa.')->group(function () {
        Route::get('payment/book/{book}', [MpesaController::class, 'showPaymentForm'])->name('payment.book');
        Route::get('payment/video/{video}', [MpesaController::class, 'showVideoPaymentForm'])->name('payment.video');
        Route::post('initiate', [MpesaController::class, 'initiatePayment'])->name('initiate');
        Route::get('status/{checkout_request_id}', [MpesaController::class, 'showPaymentStatus'])->name('status');
        Route::get('check-status/{checkout_request_id}', [MpesaController::class, 'checkPaymentStatus'])
            ->name('check.status');
        Route::get('success/{checkout_request_id}', [MpesaController::class, 'handleSuccessfulPayment'])
            ->name('success');
        Route::get('failed/{checkout_request_id}', [MpesaController::class, 'handleFailedPayment'])
            ->name('failed');
    });
    
    // User Testimonials
    Route::get('/testimonials/create', [TestimonialController::class, 'create'])->name('testimonials.create');
    Route::post('/testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
    Route::get('/my-testimonials', [TestimonialController::class, 'myTestimonials'])->name('testimonials.my');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
});

// =============================================================================
// ADMIN ROUTES
// =============================================================================

Route::prefix('admin')->name('admin.')->group(function () {
    
    // Admin Authentication (Public)
    Route::get('login', [AdminController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminController::class, 'login'])->name('login.submit');
    Route::post('logout', [AdminController::class, 'logout'])->name('logout');
    
    // Protected Admin Routes
    Route::middleware(['auth:admin'])->group(function () {
        
        // Dashboard
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Membership Management
        Route::get('/memberships', [AdminController::class, 'membershipList'])->name('memberships');
        Route::get('/memberships/{user}', [AdminController::class, 'membershipDetails'])->name('memberships.details');
        Route::post('/memberships/{user}/activate', [AdminController::class, 'activateMembership'])->name('memberships.activate');
        Route::post('/memberships/{user}/suspend', [AdminController::class, 'suspendMembership'])->name('memberships.suspend');
        Route::post('/memberships/{user}/extend', [AdminController::class, 'extendMembership'])->name('memberships.extend');
        
        // User Management
        Route::get('/users', [AdminController::class, 'userList'])->name('users');
        Route::get('/users/{user}', [AdminController::class, 'userDetails'])->name('users.details');
        Route::post('/users/{user}/update-membership', [AdminController::class, 'updateUserMembership'])
            ->name('users.update.membership');
        
        // Payment Management
        Route::get('/payments', [AdminController::class, 'paymentList'])->name('payments');
        Route::get('/payments/{payment}', [AdminController::class, 'paymentDetails'])->name('payments.details');
        Route::post('/payments/{payment}/verify', [AdminController::class, 'verifyPayment'])->name('payments.verify');
        
        // Book Management
        Route::get('/books', [AdminController::class, 'bookList'])->name('books');
        Route::get('/books/create', [AdminController::class, 'bookCreateForm'])->name('books.create');
        Route::post('/books/upload', [AdminController::class, 'uploadBook'])->name('books.upload');
        Route::get('/books/download/{id}', [AdminController::class, 'downloadBook'])->name('books.download');
        Route::get('/books/{book}/edit', [AdminController::class, 'editBook'])->name('books.edit');
        Route::put('/books/{book}/update', [AdminController::class, 'updateBook'])->name('books.update');
        Route::delete('/books/{book}', [AdminController::class, 'deleteBook'])->name('books.delete');
        
        // Video Management
        Route::get('/videos', [AdminController::class, 'videoList'])->name('videos');
        Route::get('/videos/create', [AdminController::class, 'videoCreateForm'])->name('videos.create');
        Route::post('/videos/upload', [AdminController::class, 'uploadVideo'])->name('videos.upload');
        Route::get('/videos/{video}/edit', [AdminController::class, 'editVideo'])->name('videos.edit');
        Route::put('/videos/{video}/update', [AdminController::class, 'updateVideo'])->name('videos.update');
        Route::delete('/videos/{id}', [AdminController::class, 'deleteVideo'])->name('videos.delete');
        
        // Reports
        Route::get('/reports', [AdminController::class, 'showReports'])->name('reports');
        Route::match(['GET', 'POST'], '/reports/download', [AdminController::class, 'downloadReport'])
            ->name('reports.download');
        
        // Testimonials Management
        Route::get('/testimonials', [AdminController::class, 'testimonialsList'])->name('testimonials.index');
        Route::get('/testimonials/create', [AdminController::class, 'testimonialsCreate'])->name('testimonials.create');
        Route::post('/testimonials', [AdminController::class, 'testimonialsStore'])->name('testimonials.store');
        Route::get('/testimonials/{testimonial}/edit', [AdminController::class, 'testimonialsEdit'])->name('testimonials.edit');
        Route::put('/testimonials/{testimonial}', [AdminController::class, 'testimonialsUpdate'])->name('testimonials.update');
        Route::delete('/testimonials/{testimonial}', [AdminController::class, 'testimonialsDestroy'])->name('testimonials.destroy');
        Route::post('/testimonials/{testimonial}/approve', [AdminController::class, 'testimonialsApprove'])->name('testimonials.approve');
        Route::post('/testimonials/{testimonial}/reject', [AdminController::class, 'testimonialsReject'])->name('testimonials.reject');
    });
});

// =============================================================================
// PDF GENERATION ROUTES (Admin Only)
// =============================================================================

Route::prefix('pdf')->name('pdf.')->middleware(['auth:admin'])->group(function () {
    Route::get('/receipt/{paymentId}', [PDFController::class, 'generatePaymentReceipt'])->name('receipt');
    Route::get('/payment-report', [PDFController::class, 'generatePaymentReport'])->name('payment-report');
    Route::get('/membership-report', [PDFController::class, 'generateMembershipReport'])->name('membership-report');
});

// =============================================================================
// SESSION MANAGEMENT
// =============================================================================

Route::get('/session/expired', [AuthController::class, 'handleSessionExpired'])->name('session.expired');
Route::post('/session/extend', [AuthController::class, 'extendSession'])
    ->name('session.extend')
    ->middleware('auth');



    // In routes/web.php or routes/api.php
Route::post('/payhero/callback', [PayheroController::class, 'callback'])->name('payhero.callback');

// =============================================================================
// DEVELOPMENT & TESTING ROUTES
// =============================================================================

if (app()->environment(['local', 'testing'])) {
    
    // Test Payment
    Route::get('/test-payment/{book}', function(\App\Models\Book $book) {
        return response()->json([
            'book' => $book->title,
            'price' => $book->price,
            'status' => 'Test route working'
        ]);
    });



 
    
    // Test Membership
    Route::get('/test-membership/{user_id?}', function($user_id = null) {
        $userId = $user_id ?: auth()->id();
        
        if (!$userId) {
            return response()->json(['error' => 'No user ID provided and not authenticated'], 400);
        }
        
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'membership_status' => $user->membership_status,
            'membership_expires_at' => $user->membership_expires_at?->format('Y-m-d H:i:s'),
            'has_membership' => $user->hasMembership(),
            'membership_pending' => $user->membershipPending(),
            'membership_expired' => $user->membershipExpired(),
            'days_until_expiry' => $user->days_until_expiry,
            'recent_payments' => $user->membershipPayments()
                ->latest()
                ->take(5)
                ->get(['id', 'amount', 'status', 'created_at', 'paid_at'])
        ]);
    });
    
    // Test Payment Form
    Route::get('/pay', function () {
        return view('Payment.pay');
    })->name('mpesa.form');
    
    // Test PayHero Webhook (Simulate webhook)
    Route::get('/test-payhero-webhook/{reference?}', function($reference = null) {
        $testData = [
            'status' => 'COMPLETED',
            'reference' => $reference ?? 'TEST-' . strtoupper(uniqid()),
            'external_reference' => $reference ?? 'PH-' . strtoupper(uniqid()),
            'amount' => 1,
            'phone_number' => '254757450716',
            'customer_name' => 'Test User',
            'transaction_id' => 'TEST-TXN-' . time(),
            'timestamp' => now()->toIso8601String(),
        ];
        
        Log::info('Test PayHero Webhook Triggered', $testData);
        
        // Simulate POST request to callback
        $response = app(PayheroController::class)->callback(
            Request::create('/payhero/callback', 'POST', $testData)
        );
        
        return response()->json([
            'message' => 'Test webhook sent',
            'test_data' => $testData,
            'callback_response' => $response->getContent(),
        ]);
    });
}