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
use App\Models\Book;
use App\Models\Video;

use Illuminate\Support\Facades\Route;

// =============================================================================
// PUBLIC ROUTES
// =============================================================================

// Home and Static Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/program', [CourseController::class, 'index'])->name('program');

// FAQ, Contact
Route::get('/faq', [FAQController::class, 'index'])->name('faq');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// CBT Routes (Public)
Route::get('/cbt', [CBTController::class, 'index'])->name('cbt');
Route::get('/cbt/{id}', [CBTController::class, 'start'])->name('cbt.start');
Route::post('/cbt/{id}/submit', [CBTController::class, 'submit'])->name('cbt.submit');

// =============================================================================
// BOOK ROUTES - Free Reading, Paid Downloads
// =============================================================================

// Public book routes (no authentication required)
Route::get('/books', [BookController::class, 'index'])->name('books');
Route::get('/books/preview/{book}', [BookController::class, 'previewBook'])->name('books.preview');

// Authenticated book routes (login required)
Route::middleware(['auth'])->group(function () {
    // Basic book access
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::get('/books/{book}/check-access', [BookController::class, 'checkAccess'])->name('books.check-access');
    
    // Book viewing (free for all authenticated users)
    Route::get('/books/{book}/view', [BookController::class, 'view'])->name('books.view');
    
    // Book downloads require payment
    Route::get('/books/{book}/download', [BookController::class, 'downloadBook'])->name('books.download');
    
    // Purchase routes (for paid books)
    Route::get('/books/{book}/purchase', [BookController::class, 'purchase'])->name('books.purchase');
    Route::post('/books/{book}/process-purchase', [BookController::class, 'processPurchase'])->name('books.process_purchase');
    
    // Purchase result pages
    Route::get('/books/{book}/purchase/success/{transaction}', [BookController::class, 'purchaseSuccess'])->name('books.purchase.success');
    Route::get('/books/purchase/failed/{checkout_request_id}', [BookController::class, 'purchaseFailed'])->name('books.purchase.failed');
});

// =============================================================================
// VIDEO ROUTES - Free Watching, Paid Downloads
// =============================================================================

Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/preview/{video}', [VideoController::class, 'previewVideo'])->name('videos.preview');

// Authenticated video routes (login required)
Route::middleware(['auth'])->group(function () {
    // Basic video access
    Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
    Route::get('/videos/{video}/check-access', [VideoController::class, 'checkAccess'])->name('videos.check-access');
    
    // Video watching (free for all authenticated users)
    Route::get('/videos/{video}/play', [VideoController::class, 'play'])->name('videos.play');
    
    // Video downloads require payment
    Route::get('/videos/{video}/download', [VideoController::class, 'download'])->name('videos.download');
    
    // Purchase routes (for paid videos)
    Route::get('/videos/{video}/purchase', [VideoController::class, 'purchase'])->name('videos.purchase');
    Route::post('/videos/{video}/process-purchase', [VideoController::class, 'processPurchase'])->name('videos.process_purchase');
    
    // Purchase result pages
    Route::get('/videos/{video}/purchase/success/{transaction}', [VideoController::class, 'purchaseSuccess'])->name('videos.purchase.success');
    Route::get('/videos/purchase/failed/{checkout_request_id}', [VideoController::class, 'purchaseFailed'])->name('videos.purchase.failed');
});

// =============================================================================
// EXAM ROUTES
// =============================================================================

Route::middleware(['auth'])->group(function () {
    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
    
    // Membership protected exam routes
    Route::middleware(['membership'])->group(function () {
        Route::get('/exams/{exam}/start', [ExamController::class, 'start'])->name('exams.start');
        Route::post('/exams/{exam}/submit', [ExamController::class, 'submit'])->name('exams.submit');
        Route::get('/exams/{exam}/result', [ExamController::class, 'result'])->name('exams.result');
    });
});

// =============================================================================
// AUTHENTICATION ROUTES
// =============================================================================

Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =============================================================================
// MEMBERSHIP PAYMENT ROUTES (Public - for registration process)
// =============================================================================

// Payment confirmation routes (accessible without full authentication)
Route::post('/confirm-payment', [AuthController::class, 'confirmPayment'])->name('confirm.payment');
Route::get('/membership/payment-status/{transaction_id}', [AuthController::class, 'checkMembershipPaymentStatus'])->name('membership.payment.status');

// M-Pesa Callback (Public - No Authentication Required)
Route::post('/api/mpesa/callback', [MpesaController::class, 'callback'])->name('mpesa.callback');
Route::post('/mpesa/membership/callback', [MpesaController::class, 'membershipCallback'])->name('mpesa.membership.callback');

// =============================================================================
// AUTHENTICATED USER ROUTES
// =============================================================================

Route::middleware(['auth'])->group(function () {
    
    // =============================================================================
    // MEMBERSHIP ROUTES (For users with pending/expired memberships)
    // =============================================================================
    
    Route::prefix('membership')->name('membership.')->group(function () {
        Route::get('/payment', [AuthController::class, 'showMembershipPayment'])->name('payment');
        Route::post('/pay', [AuthController::class, 'processMembershipPayment'])->name('pay');
        Route::get('/renew', [AuthController::class, 'showMembershipRenewal'])->name('renew');
        Route::post('/renew', [AuthController::class, 'processMembershipRenewal'])->name('renew.process');
        Route::get('/status', [AuthController::class, 'membershipStatus'])->name('status');
        Route::get('/history', [AuthController::class, 'membershipHistory'])->name('history');
    });
    
    // =============================================================================
    // USER DASHBOARD ROUTES
    // =============================================================================
    
        Route::get('/account', [DashboardController::class, 'index'])->name('user.dashboard');
        Route::get('/account/orders', [DashboardController::class, 'orders'])->name('orders');
        Route::get('/account/results', [DashboardController::class, 'results'])->name('results');
    
    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::get('/my-courses', [UserController::class, 'myCourses'])->name('user.courses');
    Route::get('/my-books', [UserController::class, 'myBooks'])->name('user.books');
    Route::get('/transactions', [UserController::class, 'transactions'])->name('user.transactions');
    Route::get('/payments/renew/{transaction}', [UserController::class, 'renewPayment'])->name('payments.renew');
    
    // =============================================================================
    // M-PESA PAYMENT ROUTES (For individual purchases)
    // =============================================================================
    
    Route::get('/mpesa/payment/book/{book}', [MpesaController::class, 'showPaymentForm'])->name('mpesa.payment.book');
    Route::get('/mpesa/payment/video/{video}', [MpesaController::class, 'showVideoPaymentForm'])->name('mpesa.payment.video');
    Route::post('/mpesa/initiate', [MpesaController::class, 'initiatePayment'])->name('mpesa.initiate');
    Route::get('/mpesa/status/{checkout_request_id}', [MpesaController::class, 'showPaymentStatus'])->name('mpesa.status');
    Route::get('/mpesa/check-status/{checkout_request_id}', [MpesaController::class, 'checkPaymentStatus'])->name('mpesa.check.status');
    Route::get('/mpesa/success/{checkout_request_id}', [MpesaController::class, 'handleSuccessfulPayment'])->name('mpesa.success');
    Route::get('/mpesa/failed/{checkout_request_id}', [MpesaController::class, 'handleFailedPayment'])->name('mpesa.failed');
});

// =============================================================================
// ADMIN ROUTES
// =============================================================================

Route::prefix('admin')->name('admin.')->group(function () {
    
    // Admin Authentication
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Membership Management
        Route::get('/memberships', [AdminController::class, 'membershipList'])->name('memberships');
        Route::get('/memberships/{user}', [AdminController::class, 'membershipDetails'])->name('memberships.details');
        Route::post('/memberships/{user}/activate', [AdminController::class, 'activateMembership'])->name('memberships.activate');
        Route::post('/memberships/{user}/suspend', [AdminController::class, 'suspendMembership'])->name('memberships.suspend');
        Route::post('/memberships/{user}/extend', [AdminController::class, 'extendMembership'])->name('memberships.extend');
        
        // Payment Management
        Route::get('/payments', [AdminController::class, 'paymentList'])->name('payments');
        Route::get('/payments/{payment}', [AdminController::class, 'paymentDetails'])->name('payments.details');
        Route::post('/payments/{payment}/verify', [AdminController::class, 'verifyPayment'])->name('payments.verify');
        
        // Book Management
// Book Management - Fix ALL the route names
Route::get('/books', [AdminController::class, 'bookList'])->name('admin.books');
Route::get('/books/create', [AdminController::class, 'bookCreateForm'])->name('admin.books.create');
Route::post('/books/upload', [AdminController::class, 'uploadBook'])->name('admin.books.upload');
Route::get('/books/download/{id}', [AdminController::class, 'downloadBook'])->name('admin.books.download');
Route::get('/books/{book}/edit', [AdminController::class, 'editBook'])->name('admin.books.edit');
Route::put('/books/{book}/update', [AdminController::class, 'updateBook'])->name('admin.books.update');
Route::delete('/books/{book}', [AdminController::class, 'deleteBook'])->name('admin.books.delete');    
 

// Video Management Routes - Fix ALL route names to use admin. prefix
 
// Video Management Routes - ALL should have single names (no admin. prefix)
Route::get('/videos', [AdminController::class, 'videoList'])->name('videos');
Route::get('/videos/create', [AdminController::class, 'videoCreateForm'])->name('videos.create');
Route::post('/videos/upload', [AdminController::class, 'uploadVideo'])->name('videos.upload');
Route::delete('/videos/{id}', [AdminController::class, 'deleteVideo'])->name('videos.delete');
Route::get('/videos/{video}/edit', [AdminController::class, 'editVideo'])->name('videos.edit');
Route::put('/videos/{video}/update', [AdminController::class, 'updateVideo'])->name('videos.update');



// User Management
        Route::get('/users', [AdminController::class, 'userList'])->name('users');
        Route::get('/users/{user}', [AdminController::class, 'userDetails'])->name('users.details');
        Route::post('/users/{user}/update-membership', [AdminController::class, 'updateUserMembership'])->name('users.update.membership');
    });
});

// =============================================================================
// DEBUG & TEST ROUTES (Remove in production)
// =============================================================================

if (app()->environment(['local', 'testing'])) {
    
    Route::get('/test-payment/{book}', function(Book $book) {
        return "Book found: " . $book->title;
    });
    
    Route::get('/test-membership/{user_id?}', function($user_id = null) {
        $userId = $user_id ?: auth()->id();
        
        if (!$userId) {
            return response()->json(['error' => 'User not found']);
        }
        
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found']);
        }
        
        return response()->json([
            'user_id' => $user->id,
            'email' => $user->email,
            'membership_status' => $user->membership_status,
            'membership_expires_at' => $user->membership_expires_at?->format('Y-m-d H:i:s'),
            'has_membership' => $user->hasMembership(),
            'membership_pending' => $user->membershipPending(),
            'membership_expired' => $user->membershipExpired(),
            'days_until_expiry' => $user->days_until_expiry,
            'membership_payments' => $user->membershipPayments()->latest()->take(5)->get()
        ]);
    });
    
    Route::get('/test-activate-membership/{user_id}', function($user_id) {
        $user = \App\Models\User::find($user_id);
        
        if (!$user) {
            return response()->json(['error' => 'User not found']);
        }
        
        $user->activateMembership();
        
        return response()->json([
            'message' => 'Membership activated',
            'user' => $user->fresh()
        ]);
    });
    
    Route::get('/video-debug', function() {
        $videos = \App\Models\Video::all();
        $videoDebug = $videos->map(function($video) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'video_path' => $video->video_path,
                'video_url' => $video->video_url,
                'embed_url' => $video->embed_url,
                'is_local' => $video->is_local,
                'file_exists' => $video->video_path ? file_exists(storage_path('app/public/' . $video->video_path)) : 'N/A',
                'file_url' => $video->video_path ? asset('storage/' . $video->video_path) : 'N/A'
            ];
        });
        return response()->json($videoDebug);
    })->name('video.debug');
    
    Route::get('/test-callback/{checkout_request_id?}', function($checkout_request_id = null) {
        $checkoutId = $checkout_request_id ?: 'ws_CO_' . time();
        
        $testData = [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => $checkoutId,
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is processed successfully.',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 100],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'TEST' . time()],
                            ['Name' => 'TransactionDate', 'Value' => date('YmdHis')],
                            ['Name' => 'PhoneNumber', 'Value' => '254712345678']
                        ]
                    ]
                ]
            ]
        ];
        
        $controller = new \App\Http\Controllers\MpesaController(new \App\Services\MpesaService());
        $request = new \Illuminate\Http\Request();
        $request->merge($testData);
        
        return $controller->callback($request);
    });
    
    Route::get('/test-transaction/{id}', function($id) {
        $transaction = \App\Models\Transaction::find($id);
        
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found']);
        }
        
        $transaction->update([
            'status' => 'paid',
            'mpesa_receipt_number' => 'TEST' . time(),
            'completed_at' => now(),
            'response_data' => [
                'test' => true,
                'simulated_at' => now()->toDateTimeString()
            ]
        ]);
        
        return response()->json([
            'message' => 'Transaction marked as paid',
            'transaction' => $transaction->fresh()
        ]);
    });
    
    Route::get('/test-access/{type}/{id}', function($type, $id) {
        if (!auth()->check()) {
            return response()->json(['error' => 'Please login first']);
        }
        
        $hasAccess = \App\Models\Transaction::where('user_id', auth()->id())
            ->where('content_type', $type)
            ->where('content_id', $id)
            ->where('status', 'paid')
            ->exists();
            
        $transactions = \App\Models\Transaction::where('user_id', auth()->id())->get();
            
        return response()->json([
            'user_id' => auth()->id(),
            'content_type' => $type,
            'content_id' => $id,
            'has_access' => $hasAccess,
            'total_transactions' => $transactions->count(),
            'paid_transactions' => $transactions->where('status', 'paid')->count(),
            'transactions' => $transactions->map(function($t) {
                return [
                    'id' => $t->id,
                    'content_type' => $t->content_type,
                    'content_id' => $t->content_id,
                    'status' => $t->status,
                    'amount' => $t->amount,
                    'created_at' => $t->created_at->format('Y-m-d H:i:s')
                ];
            })
        ]);
    });
    
    Route::get('/pay', function () {
        return view('Payment.pay');
    })->name('mpesa.form');
}
