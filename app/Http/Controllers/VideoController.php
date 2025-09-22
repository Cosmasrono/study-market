<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Add this import
use App\Models\Transaction;
 

class VideoController extends Controller
{
    public function index()
    {
        try {
            // Fetch available videos
            $videos = Video::where('is_active', true)->get(); // Changed from is_available to is_active
    
            Log::info('Available videos count: ' . $videos->count());
    
            // Annotate videos with access information
            $videos->transform(function ($video) {
                $user = auth()->user();
                
                if ($user) {
                    // Authenticated users can view all videos
                    $video->can_view = true;
                    $video->user_has_access = true;
                    
                    // Check if user has paid for download of this specific video
                    $video->can_download = Transaction::where('user_id', $user->id)
                        ->where('content_type', 'video')
                        ->where('content_id', $video->id)
                        ->where('status', 'paid')
                        ->exists();
                } else {
                    // Guests have no access
                    $video->can_view = false;
                    $video->can_download = false;
                    $video->user_has_access = false;
                }
    
                return $video;
            });
    
            // Fix: Use the correct view path that matches your route structure
            return view('videos.index', compact('videos')); 
    
        } catch (\Exception $e) {
            Log::error('Error fetching videos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return view('videos.index', ['videos' => collect()])
                ->withErrors(['error' => 'Unable to fetch videos. Please try again later.']);
        }
    } 

/**
 * Show a specific video (with access control)
 */
public function show(Video $video)
{
    try {
        // Check if video is available
        if (!$video->is_available) {
            return redirect()->route('videos.index')
                ->with('error', 'This video is not available.');
        }

        // Check user access
        $hasAccess = $this->userHasAccess($video->id);

        return view('videos.show', compact('video', 'hasAccess'));

    } catch (\Exception $e) {
        Log::error('Error showing video: ' . $e->getMessage());
        return redirect()->route('videos.index')
            ->with('error', 'Video not found.');
    }
}

/**
 * Play video content (protected endpoint)
 */
/**
 * Play video content (protected endpoint)
 */
public function play(Video $video)
{
    try {
        Log::info('Video play attempt', [
            'video_id' => $video->id,
            'video_title' => $video->title,
            'user_id' => auth()->id(),
            'is_active' => $video->is_active ?? 'null',
            'is_available' => $video->is_available ?? 'null',
            'status' => $video->status ?? 'null'
        ]);

        // Check video availability - try multiple fields
        $isAvailable = false;
        
        if (isset($video->is_available)) {
            $isAvailable = (bool) $video->is_available;
        } elseif (isset($video->is_active)) {
            $isAvailable = (bool) $video->is_active;
        } elseif (isset($video->status)) {
            $isAvailable = in_array($video->status, ['active', 'published', 'available']);
        } else {
            // If no availability field is set, assume it's available
            $isAvailable = true;
        }

        if (!$isAvailable) {
            Log::warning('Video not available', [
                'video_id' => $video->id,
                'is_active' => $video->is_active ?? 'null',
                'is_available' => $video->is_available ?? 'null',
                'status' => $video->status ?? 'null'
            ]);
            abort(403, 'This video is not available.');
        }

        $user = auth()->user();
        
        if (!$user) {
            Log::warning('Unauthenticated user trying to play video', ['video_id' => $video->id]);
            return redirect()->route('login')
                ->with('message', 'Please login to watch this video.');
        }

        Log::info('Video access granted', [
            'user_id' => $user->id,
            'video_id' => $video->id
        ]);

        // All authenticated users can watch videos
        return $this->serveVideoContent($video);

    } catch (\Exception $e) {
        Log::error('Error accessing video content', [
            'video_id' => $video->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        abort(500, 'Unable to access video content: ' . $e->getMessage());
    }
}

/**
 * Download a specific video (requires payment)
 */
public function download(Video $video)
{
    try {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('message', 'Please login to download this video.');
        }

        // Check if user has paid for this video
        $hasPaid = Transaction::where('user_id', $user->id)
            ->where('content_type', 'video')
            ->where('content_id', $video->id)
            ->where('status', 'paid')
            ->exists();

        if (!$hasPaid) {
            return redirect()->route('videos.purchase', $video)
                ->with('error', 'You must purchase this video to download.');
        }

        // Ensure video file exists
        if (!$video->video_path || !Storage::exists($video->video_path)) {
            return redirect()->route('videos.index')
                ->with('error', 'Video file is not available for download.');
        }

        // Log download attempt
        Log::info('Video download initiated', [
            'user_id' => $user->id,
            'video_id' => $video->id,
            'video_title' => $video->title
        ]);

        // Download the video file
        return Storage::download($video->video_path, $video->title . '.mp4');

    } catch (\Exception $e) {
        Log::error('Error downloading video: ' . $e->getMessage());
        return redirect()->route('videos.index')
            ->with('error', 'Unable to download video.');
    }
}

/**
 * Check if user has access to a video (API endpoint)
 */
public function checkAccess(Video $video)
{
    try {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'has_access' => false,
                'can_download' => false,
                'message' => 'Please login first',
                'redirect_url' => route('login')
            ]);
        }

        // Check if user has paid for download
        $canDownload = Transaction::where('user_id', $user->id)
            ->where('content_type', 'video')
            ->where('content_id', $video->id)
            ->where('status', 'paid')
            ->exists();

        return response()->json([
            'has_access' => true, // All authenticated users can watch
            'can_download' => $canDownload,
            'is_free' => $video->is_free,
            'price' => $video->price,
            'video_title' => $video->title,
            'payment_url' => !$canDownload ? route('mpesa.payment.video', $video) : null
        ]);

    } catch (\Exception $e) {
        Log::error('Access check error: ' . $e->getMessage());
        return response()->json([
            'has_access' => false,
            'can_download' => false,
            'message' => 'Error checking access'
        ], 500);
    }
}

/**
 * Private method to check if user has access to a video
 */
private function userHasAccess($videoId)
{
    $user = Auth::user();
    
    if (!$user) {
        return false;
    }

    // For watching: return true (all authenticated users can watch)
    // For downloading: check payment
    return Transaction::where('user_id', $user->id)
        ->where('content_type', 'video')
        ->where('content_id', $videoId)
        ->where('status', 'paid')
        ->exists();
}

/**
 * Serve video content to authorized users
 */
private function serveVideoContent($video)
{
    try {
        // Get the video file URL/path
        $videoUrl = $video->video_url;
        
        if (!$videoUrl) {
            abort(404, 'Video content not found.');
        }

        // Log access for auditing
        Log::info('Video accessed', [
            'user_id' => Auth::id(),
            'video_id' => $video->id,
            'video_title' => $video->title,
            'timestamp' => now()
        ]);

        // Return embedded player view
        return view('videos.player', [
            'video' => $video,
            'file_url' => $videoUrl
        ]);

    } catch (\Exception $e) {
        Log::error('Error serving video content: ' . $e->getMessage());
        abort(500, 'Unable to serve video content.');
    }
}

/**
 * Download video file for authorized users
 */
private function downloadVideoFile($video)
{
    try {
        // Log download for auditing
        Log::info('Video downloaded', [
            'user_id' => Auth::id(),
            'video_id' => $video->id,
            'video_title' => $video->title,
            'timestamp' => now()
        ]);

        // For Cloudinary URLs, redirect with download parameter
        if (str_contains($video->video_url, 'cloudinary.com')) {
            $downloadUrl = str_replace('/upload/', '/upload/fl_attachment/', $video->video_url);
            return redirect($downloadUrl);
        }

        // For local files, serve via Laravel
        if ($video->is_local && $video->video_file) {
            $filePath = storage_path('app/public/' . $video->video_file);
            
            if (!file_exists($filePath)) {
                abort(404, 'Video file not found.');
            }

            return response()->download($filePath, $video->original_filename ?: ($video->title . '.mp4'));
        }

        // Fallback
        abort(404, 'Video download not available.');

    } catch (\Exception $e) {
        Log::error('Error downloading video: ' . $e->getMessage());
        abort(500, 'Unable to download video.');
    }
}
 
    private function userHasVideoAccess($userId, $videoId, $accessType = 'watch')
    {
        Log::info('=== USER ACCESS CHECK START ===', [
            'user_id' => $userId,
            'video_id' => $videoId,
            'access_type' => $accessType
        ]);

        $query = Transaction::where('user_id', $userId)
            ->where('content_type', 'video')
            ->where('content_id', $videoId)
            ->where('purchase_type', $accessType)
            ->where('status', 'paid');

        $transactions = $query->get();
        Log::info('Found transactions', [
            'user_id' => $userId,
            'video_id' => $videoId,
            'transaction_count' => $transactions->count(),
            'transactions' => $transactions->toArray()
        ]);

        $hasAccess = $query->exists();
        
        Log::info('=== USER ACCESS CHECK END ===', [
            'user_id' => $userId,
            'video_id' => $videoId,
            'has_access' => $hasAccess
        ]);

        return $hasAccess;
    }

    /**
     * Play video for authenticated users
     */
    // public function play(Video $video)
    // {
    //     try {
    //         Log::info('=== VIDEO PLAY START ===', [
    //             'video_id' => $video->id,
    //             'video_title' => $video->title,
    //             'user_id' => auth()->id()
    //         ]);

    //         $user = auth()->user();
            
    //         if (!$user || !$user->hasMembership()) {
    //             Log::warning('Play access denied - no membership', [
    //                 'video_id' => $video->id,
    //                 'user_id' => $user ? $user->id : null,
    //                 'has_membership' => $user ? $user->hasMembership() : false
    //             ]);
    //             return redirect()->route('membership.payment')
    //                 ->with('error', 'Active membership required to watch videos.');
    //         }

    //         // Check if user can watch this video
    //         if (!$video->is_free && !$this->userHasVideoAccess($user->id, $video->id, 'watch')) {
    //             Log::warning('Play access denied - video not purchased', [
    //                 'video_id' => $video->id,
    //                 'user_id' => $user->id,
    //                 'is_free' => $video->is_free
    //             ]);
    //             return redirect()->route('mpesa.payment.video', $video->id)
    //                 ->with('error', 'You need to purchase this video to watch it.');
    //         }

    //         Log::info('Video play granted', [
    //             'user_id' => $user->id,
    //             'video_id' => $video->id
    //         ]);

    //         Log::info('=== VIDEO PLAY END ===');

    //         return view('videos.play', [
    //             'video' => $video,
    //             'can_download' => $this->userHasVideoAccess($user->id, $video->id, 'download')
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Error playing video', [
    //             'video_id' => $video->id,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'user_id' => auth()->id()
    //         ]);
    //         return redirect()->route('videos.index')
    //             ->with('error', 'Unable to play video. Please try again later.');
    //     }
    // }

    /**
     * Preview a specific video
     */
    public function previewVideo(Video $video)
    {
        try {
            // Check if video is available for preview
            if (!$video->is_active) {
                return redirect()->route('videos.index')
                    ->with('error', 'This video is not available for preview.');
            }

            // Return preview view with limited video details
            return view('videos.preview', [
                'video' => $video,
                'preview_only' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error previewing video: ' . $e->getMessage());
            return redirect()->route('videos.index')
                ->with('error', 'Unable to preview video.');
        }
    }

    /**
     * Debug video information
     */
    public function testVideo($id)
    {
        Log::info('=== TEST VIDEO START ===', ['video_id' => $id]);

        $video = Video::findOrFail($id);
        $user = auth()->user();

        $debug = [
            'id' => $video->id,
            'title' => $video->title,
            'description' => $video->description ?? 'No description',
            'price' => $video->price ?? 0,
            'is_free' => (bool)$video->is_free,
            'is_active' => (bool)$video->is_active,
            'is_local' => (bool)$video->is_local,
            'video_path' => $video->video_path,
            'video_url' => $video->video_url,
            'thumbnail' => $video->thumbnail,
            'duration' => $video->duration ?? $video->duration_minutes ?? null,
            'category' => $video->category ?? 'General',
            'user_authenticated' => (bool)$user,
            'user_has_membership' => $user ? $user->hasMembership() : false,
            'user_has_access' => $user ? $this->userHasVideoAccess($user->id, $video->id, 'watch') : false,
        ];

        Log::info('Test video debug data', $debug);
        Log::info('=== TEST VIDEO END ===');

        return response()->json($debug);
    }

    /**
     * Show purchase page for a specific video
     */
    public function purchase(Video $video)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return redirect()->route('login')
                    ->with('message', 'Please login to purchase this video.');
            }

            // Check if user already has access
            $hasPaid = Transaction::where('user_id', $user->id)
                ->where('content_type', 'video')
                ->where('content_id', $video->id)
                ->where('status', 'paid')
                ->exists();

            if ($hasPaid) {
                return redirect()->route('videos.show', $video)
                    ->with('message', 'You already have access to this video.');
            }

            return view('videos.purchase', [
                'video' => $video,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing video purchase page: ' . $e->getMessage());
            return redirect()->route('videos.index')
                ->with('error', 'Unable to process video purchase.');
        }
    }

    /**
     * Process video purchase
     */
    public function processPurchase(Request $request, Video $video)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return redirect()->route('login')
                    ->with('message', 'Please login to purchase this video.');
            }

            // Validate purchase request
            $request->validate([
                'payment_method' => 'required|in:mpesa,card,paypal'
            ]);

            // Check if user already has access
            $existingTransaction = Transaction::where('user_id', $user->id)
                ->where('content_type', 'video')
                ->where('content_id', $video->id)
                ->where('status', 'paid')
                ->first();

            if ($existingTransaction) {
                return redirect()->route('videos.show', $video)
                    ->with('message', 'You already have access to this video.');
            }

            // Create a new transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'content_type' => 'video',
                'content_id' => $video->id,
                'amount' => $video->price,
                'status' => 'pending',
                'payment_method' => $request->input('payment_method')
            ]);

            // Redirect to payment gateway based on method
            switch ($request->input('payment_method')) {
                case 'mpesa':
                    return redirect()->route('mpesa.payment.video', $video);
                case 'card':
                    // Implement card payment redirect
                    return redirect()->route('card.payment.video', $video);
                case 'paypal':
                    // Implement PayPal payment redirect
                    return redirect()->route('paypal.payment.video', $video);
                default:
                    throw new \Exception('Invalid payment method');
            }

        } catch (\Exception $e) {
            Log::error('Error processing video purchase: ' . $e->getMessage());
            return redirect()->route('videos.purchase', $video)
                ->with('error', 'Unable to process purchase. Please try again.');
        }
    }

    /**
     * Handle successful video purchase
     */
    public function purchaseSuccess(Transaction $transaction)
    {
        try {
            // Update transaction status
            $transaction->update([
                'status' => 'paid',
                'completed_at' => now()
            ]);

            // Retrieve the video
            $video = Video::findOrFail($transaction->content_id);

            return view('videos.purchase_success', [
                'transaction' => $transaction,
                'video' => $video
            ]);

        } catch (\Exception $e) {
            Log::error('Error handling video purchase success: ' . $e->getMessage());
            return redirect()->route('videos.index')
                ->with('error', 'Purchase completed, but there was an issue confirming the transaction.');
        }
    }

    /**
     * Handle failed video purchase
     */
    public function purchaseFailed($checkout_request_id)
    {
        try {
            // Find the related transaction
            $transaction = Transaction::where('checkout_request_id', $checkout_request_id)
                ->where('content_type', 'video')
                ->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'failed',
                    'completed_at' => now()
                ]);
            }

            return view('videos.purchase_failed', [
                'transaction' => $transaction,
                'checkout_request_id' => $checkout_request_id
            ]);

        } catch (\Exception $e) {
            Log::error('Error handling video purchase failure: ' . $e->getMessage());
            return redirect()->route('videos.index')
                ->with('error', 'There was an issue processing your purchase.');
        }
    }
}