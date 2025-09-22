<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    /**
     * Display a listing of books
     */
    public function index()
    {
        try {
            // Fetch available books
            $books = Book::where('is_available', true)->get();
    
            Log::info('Available books count: ' . $books->count());
    
            // Annotate books with access information
            $books->transform(function ($book) {
                $user = auth()->user();
                
                if ($user) {
                    // Authenticated users can view all books
                    $book->can_view = true;
                    $book->user_has_access = true;
                    
                    // Check if user has paid for download of this specific book
                    $book->can_download = Transaction::where('user_id', $user->id)
                        ->where('content_type', 'book')
                        ->where('content_id', $book->id)
                        ->where('status', 'paid')
                        ->exists();
                } else {
                    // Guests have no access
                    $book->can_view = false;
                    $book->can_download = false;
                    $book->user_has_access = false;
                }
    
                return $book;
            });
    
            return view('books', compact('books'));
    
        } catch (\Exception $e) {
            Log::error('Error fetching books', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return view('books', ['books' => collect()])
                ->withErrors(['error' => 'Unable to fetch books. Please try again later.']);
        }
    }
    
    /**
     * View book content (free for authenticated users)
     */
    public function view(Book $book)
    {
        try {
            // Check if book is available
            if (!$book->is_available) {
                abort(403, 'This book is not available.');
            }
    
            // Require authentication for viewing
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->with('message', 'Please login to read this book.');
            }
    
            // All authenticated users can view books
            return $this->serveBookContent($book);
    
        } catch (\Exception $e) {
            Log::error('Error accessing book content: ' . $e->getMessage());
            abort(500, 'Unable to access book content.');
        }
    }
    
    /**
     * Download book (requires payment)
     */
    public function downloadBook(Book $book)
    {
        try {
            // Check if book is available
            if (!$book->is_available) {
                abort(403, 'This book is not available.');
            }
    
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login')
                    ->with('message', 'Please login to download this book.');
            }
    
            // Check if user has paid for this specific book download
            $hasPaid = Transaction::where('user_id', $user->id)
                ->where('content_type', 'book')
                ->where('content_id', $book->id)
                ->where('status', 'paid')
                ->exists();
    
            if (!$hasPaid) {
                return redirect()->route('mpesa.payment.book', $book)
                    ->with('error', 'Payment required to download this book.');
            }
    
            // User has paid, allow download
            return $this->downloadBookFile($book);
    
        } catch (\Exception $e) {
            Log::error('Error downloading book: ' . $e->getMessage());
            abort(500, 'Unable to download book.');
        }
    }
    
    /**
     * Check if user has access to a book (API endpoint)
     */
    public function checkAccess(Book $book)
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
                ->where('content_type', 'book')
                ->where('content_id', $book->id)
                ->where('status', 'paid')
                ->exists();
    
            return response()->json([
                'has_access' => true, // All authenticated users can view
                'can_download' => $canDownload,
                'is_free' => $book->is_free,
                'price' => $book->price,
                'book_title' => $book->title,
                'payment_url' => !$canDownload ? route('mpesa.payment.book', $book) : null
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
     * Helper method to check if user can access a book (simplified)
     */
    private function userCanAccessBook($book, $user)
    {
        // All authenticated users can view books
        return $user !== null;
    }
    
    /**
     * Updated private method to check if user has download access
     */
    private function userHasAccess($bookId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
    
        // For viewing: return true (all authenticated users can view)
        // For downloading: check payment
        return Transaction::where('user_id', $user->id)
            ->where('content_type', 'book')
            ->where('content_id', $bookId)
            ->where('status', 'paid')
            ->exists();
    }

    /**
     * Check if user has access to a book (API endpoint)
     */
 

    /**
     * Success page after purchase
     */
    public function purchaseSuccess(Book $book, Transaction $transaction)
    {
        try {
            // Verify the transaction belongs to the current user and is for this book
            if ($transaction->user_id !== Auth::id() || 
                $transaction->content_type !== 'book' || 
                $transaction->content_id !== $book->id) {
                return redirect()->route('books')
                    ->with('error', 'Invalid purchase record.');
            }

            if (!$transaction->isCompleted()) {
                return redirect()->route('books')
                    ->with('error', 'Payment is still pending or failed.');
            }

            return view('books.purchase-success', compact('book', 'transaction'));
        } catch (\Exception $e) {
            Log::error('Purchase success page error: ' . $e->getMessage());
            return redirect()->route('books')
                ->with('error', 'Purchase record not found');
        }
    }

    /**
     * Failed purchase page
     */
    public function purchaseFailed($checkout_request_id)
    {
        try {
            $transaction = Transaction::where('checkout_request_id', $checkout_request_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$transaction) {
                return redirect()->route('books')
                    ->with('error', 'Transaction not found.');
            }

            $book = null;
            if ($transaction->content_type === 'book') {
                $book = Book::find($transaction->content_id);
            }

            return view('books.purchase-failed', compact('transaction', 'book'));
        } catch (\Exception $e) {
            Log::error('Purchase failed page error: ' . $e->getMessage());
            return redirect()->route('books')
                ->with('error', 'Unable to load transaction details.');
        }
    }

    /**
     * Preview book (limited access for potential buyers)
     */
    public function previewBook(Book $book)
    {
        try {
            if (!$book->is_available) {
                abort(404, 'Book not available for preview.');
            }

            // Generate preview content or redirect to a preview page
            return view('books.preview', compact('book'));

        } catch (\Exception $e) {
            Log::error('Book preview error: ' . $e->getMessage());
            abort(500, 'Unable to generate book preview.');
        }
    }

    /**
     * Private method to check if user has access to a book
     */
 

    /**
     * Serve book content to authorized users
     * 
     * 
     */

   

    /**
     * Download book file for authorized users
     */
    private function downloadBookFile($book)
    {
        try {
            // Log download for auditing
            Log::info('Book downloaded', [
                'user_id' => Auth::id(),
                'book_id' => $book->id,
                'book_title' => $book->title,
                'timestamp' => now()
            ]);

            // For Cloudinary URLs, redirect with download parameter
            if (str_contains($book->book_url, 'cloudinary.com')) {
                $downloadUrl = str_replace('/upload/', '/upload/fl_attachment/', $book->book_url);
                return redirect($downloadUrl);
            }

            // For local files, serve via Laravel
            if ($book->is_local && $book->book_file) {
                $filePath = storage_path('app/public/' . $book->book_file);
                
                if (!file_exists($filePath)) {
                    abort(404, 'Book file not found.');
                }

                return response()->download($filePath, $book->original_filename ?: ($book->title . '.pdf'));
            }

            // Fallback
            abort(404, 'Book download not available.');

        } catch (\Exception $e) {
            Log::error('Error downloading book: ' . $e->getMessage());
            abort(500, 'Unable to download book.');
        }
    }
 


private function serveBookContent($book)
{
    try {
        // Ensure book is available
        if (!$book->isAvailable()) {
            Log::warning('Attempted to access unavailable book', [
                'book_id' => $book->id,
                'book_title' => $book->title,
                'is_available' => $book->is_available
            ]);
            abort(403, 'This book is not currently available.');
        }

        // Get the book file URL/path
        $bookUrl = $book->book_url;
        
        // Log detailed book information for debugging
        Log::info('Serving Book Content', [
            'book_id' => $book->id,
            'book_title' => $book->title,
            'book_url' => $bookUrl,
            'is_local' => $book->is_local,
            'book_path' => $book->book_path,
            'book_file' => $book->book_file,
            'is_available' => $book->is_available
        ]);

        // Validate book URL
        if (!$bookUrl) {
            Log::error('Book content not found', [
                'book_id' => $book->id,
                'book_title' => $book->title,
                'book_attributes' => $book->getAttributes(),
                'is_local' => $book->is_local,
                'book_path' => $book->book_path,
                'book_file' => $book->book_file
            ]);
            abort(404, 'Book content URL is missing or invalid.');
        }

        // Validate URL accessibility (optional, depends on your requirements)
        try {
            $headers = @get_headers($bookUrl);
            if ($headers === false) {
                Log::warning('Unable to check book URL headers', [
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'book_url' => $bookUrl
                ]);
                // Proceed with the URL even if headers can't be checked
            } elseif ($headers && strpos($headers[0], '200') === false) {
                Log::warning('Book URL is not accessible', [
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'book_url' => $bookUrl,
                    'headers' => $headers
                ]);
                // Log the issue but still attempt to serve the content
            }
        } catch (\Exception $urlCheckException) {
            Log::warning('Error checking book URL accessibility', [
                'book_id' => $book->id,
                'book_title' => $book->title,
                'book_url' => $bookUrl,
                'error' => $urlCheckException->getMessage()
            ]);
        }

        // Log access for auditing
        Log::info('Book accessed', [
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'book_title' => $book->title,
            'timestamp' => now()
        ]);

        // Return embedded reader view (uses your existing books/reader.blade.php)
        return view('books.reader', [
            'book' => $book,
            'file_url' => $bookUrl
        ]);

    } catch (\Exception $e) {
        Log::error('Error serving book content', [
            'error_message' => $e->getMessage(),
            'book_id' => $book->id,
            'book_title' => $book->title,
            'trace' => $e->getTraceAsString()
        ]);
        abort(500, 'Unable to serve book content. Please contact support.');
    }
}

    /**
     * Alternative method to get all books for debugging
     */
    public function indexAll()
    {
        $books = Book::all();
        return view('books', compact('books'));
    }
}