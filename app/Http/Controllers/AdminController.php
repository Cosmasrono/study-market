<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Video;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\CloudinaryService;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // Add more detailed logging
            Log::info('Admin Login Attempt', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'timestamp' => now()
            ]);

            // Check if admin exists
            $admin = Admin::where('email', $credentials['email'])->first();
            
            if (!$admin) {
                Log::warning('Admin login failed - No admin account found', [
                    'email' => $credentials['email'],
                    'ip' => $request->ip()
                ]);

                return back()->withErrors([
                    'email' => 'No admin account found with this email address.'
                ])->withInput($request->only('email'));
            }

            // Manually verify password
            if (!Hash::check($credentials['password'], $admin->password)) {
                Log::warning('Admin login failed - Incorrect password', [
                    'email' => $credentials['email'],
                    'ip' => $request->ip(),
                    'password_hash_match' => Hash::check($credentials['password'], $admin->password)
                ]);

                return back()->withErrors([
                    'password' => 'The provided password is incorrect.'
                ])->withInput($request->only('email'));
            }

            // Attempt login with admin guard
            if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                Log::info('Admin login successful', [
                    'email' => $credentials['email'],
                    'ip' => $request->ip()
                ]);

                return redirect()->intended('/admin/dashboard')->with('success', 'Login successful!');
            }

            // Fallback error
            Log::error('Admin login failed - Unknown reason', [
                'email' => $credentials['email'],
                'ip' => $request->ip()
            ]);

            return back()->withErrors([
                'email' => 'Unable to log in. Please try again.'
            ])->withInput($request->only('email'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            Log::warning('Admin login validation failed', [
                'errors' => $e->errors(),
                'email' => $request->input('email'),
                'ip' => $request->ip()
            ]);

            return back()->withErrors($e->validator)->withInput($request->only('email'));
        } catch (\Exception $e) {
            // Log any unexpected errors
            Log::error('Unexpected admin login error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->input('email'),
                'ip' => $request->ip()
            ]);

            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    public function dashboard()
    {
        $booksCount = Book::count();
        $videosCount = Video::count();
        return view('admin.dashboard', compact('booksCount', 'videosCount'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login')->with('success', 'Logout successful!');
    }




    public function uploadBook(Request $request)
    {
        try {
            // Enhanced validation with expanded file type support
            $validatedData = $request->validate([
                'title' => 'required|string|max:500',
                'description' => 'nullable|string|max:2000',
                'book_files' => 'nullable|array|max:10', // Limit to 10 files for performance
                'book_files.*' => [
                    'file',
                    'max:204800', // 200MB
                    function ($attribute, $value, $fail) {
                        if (!$value->isValid()) {
                            $fail('The uploaded file is invalid.');
                            return;
                        }
                        
                        // Expanded list of allowed extensions
                        $allowedExtensions = [
                            'pdf', 'epub', 'mobi', 'docx', 'doc', 'txt', 'rtf',
                            'ppt', 'pptx', 'xls', 'xlsx', 'csv',
                            'odt', 'ods', 'odp', // OpenDocument formats
                            'pages', 'numbers', 'key', // Apple formats
                            'html', 'htm', 'xml', 'json',
                            'md', 'markdown', 'tex', 'latex'
                        ];
                        
                        // Expanded MIME types
                        $allowedMimeTypes = [
                            // PDF
                            'application/pdf',
                            
                            // E-book formats
                            'application/epub+zip',
                            'application/x-mobipocket-ebook',
                            
                            // Microsoft Office
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
                            'application/msword', // doc
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // pptx
                            'application/vnd.ms-powerpoint', // ppt
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
                            'application/vnd.ms-excel', // xls
                            
                            // Text formats
                            'text/plain',
                            'text/rtf',
                            'application/rtf',
                            'text/html',
                            'text/xml',
                            'application/xml',
                            'text/csv',
                            'application/json',
                            'text/markdown',
                            
                            // OpenDocument formats
                            'application/vnd.oasis.opendocument.text', // odt
                            'application/vnd.oasis.opendocument.spreadsheet', // ods
                            'application/vnd.oasis.opendocument.presentation', // odp
                            
                            // Apple iWork formats
                            'application/x-iwork-pages-sffpages', // pages
                            'application/x-iwork-numbers-sffnumbers', // numbers
                            'application/x-iwork-keynote-sffkey', // key
                            
                            // LaTeX
                            'application/x-tex',
                            'application/x-latex',
                            
                            // Generic fallback
                            'application/octet-stream'
                        ];
                        
                        $extension = strtolower($value->getClientOriginalExtension());
                        $mimeType = $value->getMimeType();
                        
                        // Check extension first
                        if (!in_array($extension, $allowedExtensions)) {
                            $fail('The file must be a document format. Allowed types: ' . implode(', ', $allowedExtensions));
                            return;
                        }
                        
                        // For stricter validation, also check MIME type if it's not generic
                        if ($mimeType !== 'application/octet-stream' && !in_array($mimeType, $allowedMimeTypes)) {
                            // Log for debugging but don't fail - some valid files have unexpected MIME types
                            Log::info('File with unexpected MIME type allowed', [
                                'filename' => $value->getClientOriginalName(),
                                'extension' => $extension,
                                'mime_type' => $mimeType
                            ]);
                        }
                        
                        // Additional file content validation for security
                        $this->validateFileContent($value, $extension);
                    }
                ],
                'book_type' => 'required|in:free,paid',
                'price' => 'nullable|numeric|min:0.01|max:9999.99',
                'is_available' => [
                    'nullable', 
                    function ($attribute, $value, $fail) {
                        // Convert checkbox/string input to boolean
                        if (is_string($value)) {
                            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        }
                        
                        if ($value === null) {
                            $fail('The is available field must be true or false.');
                        }
                    }
                ],
                'upload_mode' => 'nullable|string|in:single,individual',
                'individual_titles' => 'nullable|array',
                'individual_titles.*' => 'nullable|string|max:500'
            ]);
    
            // Determine book type and pricing
            $bookType = $request->input('book_type');
            $price = $request->input('price', 0);
            
            // Set free/paid status based on selection
            if ($bookType === 'free') {
                $isFree = true;
                $finalPrice = 0;
            } else {
                $isFree = false;
                $finalPrice = $price;
                
                // Validate price for paid books
                if ($finalPrice <= 0) {
                    return back()->withErrors(['price' => 'Paid books must have a price greater than 0'])
                        ->withInput();
                }
            }
    
            // Prepare to track uploaded books
            $uploadedBooks = [];
            $failedUploads = [];
    
            // Check if book files are uploaded
            if ($request->hasFile('book_files')) {
                $cloudinaryService = new CloudinaryService();
                $individualTitles = $request->input('individual_titles', []);
    
                foreach ($request->file('book_files') as $index => $bookFile) {
                    try {
                        // Additional file validation
                        if (!$bookFile->isValid()) {
                            $failedUploads[] = [
                                'filename' => $bookFile->getClientOriginalName(),
                                'error' => 'File is corrupted or invalid'
                            ];
                            continue;
                        }
                        
                        // Get ALL file info BEFORE upload
                        $originalName = $bookFile->getClientOriginalName();
                        $fileSize = $bookFile->getSize();
                        $fileMimeType = $bookFile->getMimeType();
                        $fileExtension = $bookFile->getClientOriginalExtension();
                        
                        // Determine title for this specific book
                        $bookTitle = trim($validatedData['title']);
                        if (isset($individualTitles[$index]) && !empty(trim($individualTitles[$index]))) {
                            $bookTitle = trim($individualTitles[$index]);
                        } elseif (count($request->file('book_files')) > 1) {
                            // Add filename to title if multiple files and no individual title
                            $bookTitle .= ' - ' . pathinfo($originalName, PATHINFO_FILENAME);
                        }
                        
                        Log::info('Starting document file upload', [
                            'original_name' => $originalName,
                            'book_title' => $bookTitle,
                            'file_size_mb' => round($fileSize / (1024 * 1024), 2),
                            'mime_type' => $fileMimeType,
                            'extension' => $fileExtension,
                            'is_free' => $isFree,
                            'price' => $finalPrice
                        ]);
    
                        // Upload to Cloudinary with appropriate resource type
                        $uploadResult = $this->uploadDocumentToCloudinary($cloudinaryService, $bookFile, $fileExtension);
    
                        // Verify upload result
                        if (!isset($uploadResult['url']) || !isset($uploadResult['public_id'])) {
                            $failedUploads[] = [
                                'filename' => $originalName,
                                'error' => 'Failed to upload to cloud storage'
                            ];
                            continue;
                        }
    
                        // Prepare book data
                        $bookData = [
                            'title' => $bookTitle,
                            'description' => $validatedData['description'] ? trim($validatedData['description']) : null,
                            'price' => $finalPrice,
                            'is_free' => $isFree,
                            'is_available' => filter_var($request->input('is_available', false), FILTER_VALIDATE_BOOLEAN),
                            'book_url' => $uploadResult['url'],
                            'book_path' => $uploadResult['public_id'],
                            'is_local' => false,
                            'file_size' => $fileSize,
                            'original_filename' => $originalName,
                            'mime_type' => $fileMimeType,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
    
                        // Create the book record
                        $book = Book::create($bookData);
    
                        // Verify book was created
                        if (!$book) {
                            $failedUploads[] = [
                                'filename' => $originalName,
                                'error' => 'Failed to create database record'
                            ];
                            continue;
                        }
    
                        $uploadedBooks[] = $book;
    
                    } catch (\Exception $e) {
                        Log::error('Individual document upload failed', [
                            'filename' => $bookFile->getClientOriginalName() ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                        
                        $failedUploads[] = [
                            'filename' => $bookFile->getClientOriginalName() ?? 'unknown',
                            'error' => 'Upload failed: ' . $e->getMessage()
                        ];
                    }
                }
            } else {
                // Create a book without files (metadata only)
                $bookData = [
                    'title' => trim($validatedData['title']),
                    'description' => $validatedData['description'] ? trim($validatedData['description']) : null,
                    'price' => $finalPrice,
                    'is_free' => $isFree,
                    'is_available' => filter_var($request->input('is_available', false), FILTER_VALIDATE_BOOLEAN),
                    'is_local' => true,
                    'file_size' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
    
                $book = Book::create($bookData);
                $uploadedBooks[] = $book;
            }
    
            // Prepare comprehensive success/error message
            $messages = [];
            
            if (count($uploadedBooks) > 0) {
                if (count($uploadedBooks) === 1) {
                    $book = $uploadedBooks[0];
                    $bookTypeText = $book->is_free ? 'free' : 'paid (KSh ' . number_format($book->price, 2) . ')';
                    $messages[] = "Document \"{$book->title}\" uploaded successfully as a {$bookTypeText} document!";
                } else {
                    $bookTypeText = $isFree ? 'free' : 'paid (KSh ' . number_format($finalPrice, 2) . ')';
                    $messages[] = count($uploadedBooks) . " documents uploaded successfully as {$bookTypeText} documents!";
                }
            }
    
            if (count($failedUploads) > 0) {
                $failedCount = count($failedUploads);
                $messages[] = "{$failedCount} document(s) failed to upload:";
                foreach ($failedUploads as $failed) {
                    $messages[] = "• {$failed['filename']}: {$failed['error']}";
                }
            }
    
            // Determine redirect type based on results
            if (count($uploadedBooks) > 0 && count($failedUploads) === 0) {
                // All successful
                return redirect()->route('admin.books')
                    ->with('success', implode(' ', $messages));
            } elseif (count($uploadedBooks) > 0 && count($failedUploads) > 0) {
                // Partial success
                return redirect()->route('admin.books')
                    ->with('warning', implode("\n", $messages));
            } else {
                // All failed
                return redirect()->back()
                    ->with('error', implode("\n", $messages))
                    ->withInput($request->except(['book_files']));
            }
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log detailed validation errors
            Log::error('Document upload validation failed', [
                'errors' => $e->validator->errors()->toArray(),
                'input' => $request->except(['book_files']),
                'files' => array_map(function($file) {
                    return [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'extension' => $file->getClientOriginalExtension(),
                        'is_valid' => $file->isValid()
                    ];
                }, $request->file('book_files') ?? [])
            ]);
            
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput($request->except(['book_files']));
    
        } catch (\Exception $e) {
            // Log comprehensive error details
            Log::error('Document upload failed', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'request_data' => $request->except(['book_files']),
                'files_info' => array_map(function($file) {
                    return [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'extension' => $file->getClientOriginalExtension(),
                        'is_valid' => $file->isValid()
                    ];
                }, $request->hasFile('book_files') ? $request->file('book_files') : []),
                'user_id' => auth()->id() ?? 'guest'
            ]);
    
            // Return user-friendly error message
            $errorMessage = 'Failed to upload document(s). Please try again.';
            
            // Provide more specific error messages for common issues
            if (str_contains($e->getMessage(), 'Cloudinary')) {
                $errorMessage = 'File upload service is currently unavailable. Please try again later.';
            } elseif (str_contains($e->getMessage(), 'file')) {
                $errorMessage = 'There was an issue with the uploaded file(s). Please check the files and try again.';
            }
    
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput($request->except(['book_files']));
        }
    }
    
    /**
     * Validate file content for security
     */
    private function validateFileContent($file, $extension)
    {
        $filePath = $file->getPathname();
        
        // Basic file header validation
        $fileSignatures = [
            'pdf' => ['%PDF'],
            'docx' => ['PK'], // ZIP-based format
            'pptx' => ['PK'], // ZIP-based format
            'xlsx' => ['PK'], // ZIP-based format
            'doc' => ["\xD0\xCF\x11\xE0"], // OLE format
            'ppt' => ["\xD0\xCF\x11\xE0"], // OLE format
            'xls' => ["\xD0\xCF\x11\xE0"], // OLE format
            'rtf' => ['{\rtf'],
            'html' => ['<!DOCTYPE', '<html'],
            'xml' => ['<?xml'],
            'json' => ['{', '['],
        ];
        
        if (isset($fileSignatures[$extension])) {
            $fileHeader = file_get_contents($filePath, false, null, 0, 20);
            $validSignature = false;
            
            foreach ($fileSignatures[$extension] as $signature) {
                if (strpos($fileHeader, $signature) === 0) {
                    $validSignature = true;
                    break;
                }
            }
            
            if (!$validSignature && $extension !== 'txt') {
                throw new \Exception("File content doesn't match the expected format for .$extension files");
            }
        }
    }
    
    /**
     * Upload document to Cloudinary with appropriate settings
     */
    private function uploadDocumentToCloudinary($cloudinaryService, $file, $extension)
    {
        // Determine resource type based on file extension
        $resourceType = 'raw'; // Default for documents
        
        // Some formats might need special handling
        $specialFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        if (in_array(strtolower($extension), $specialFormats)) {
            $resourceType = 'image';
        }
        
        // For video formats (if you want to support them in the future)
        $videoFormats = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        if (in_array(strtolower($extension), $videoFormats)) {
            $resourceType = 'video';
        }
        
        // Use the existing uploadBook method but with enhanced options
        return $cloudinaryService->uploadBook($file, [
            'resource_type' => $resourceType,
            'folder' => 'documents/' . date('Y/m'),
            'use_filename' => true,
            'unique_filename' => true,
            'overwrite' => false,
            'tags' => ['document', $extension, 'uploaded_' . date('Y-m-d')]
        ]);
    }



    public function uploadVideo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'video_file' => 'required|file|mimetypes:video/mp4,video/mpeg,video/quicktime|max:500000', // 500MB max
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'is_free' => 'boolean'
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please fix the validation errors and try again.');
            }

            // Validate file upload
            if (!$request->hasFile('video_file') || !$request->file('video_file')->isValid()) {
                return back()
                    ->withInput()
                    ->with('error', 'Video file upload failed. Please try again with a different file.');
            }

            $videoFile = $request->file('video_file');

            // Use CloudinaryService to upload
            $cloudinaryService = new CloudinaryService();
            $uploadResult = $cloudinaryService->uploadVideo($videoFile);

            // Create video record in database
            $video = Video::create([
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'price' => $request->is_free ? 0 : ($request->price ?? 0),
                'is_free' => $request->is_free ?? false,
                'video_url' => $uploadResult['url'],
                'video_path' => $uploadResult['public_id'], // Store Cloudinary public ID
                'is_local' => false, // Cloudinary upload is not local
                'file_size' => $uploadResult['size'],
                'is_active' => true
            ]);

            return redirect()
                ->route('admin.videos')
                ->with('success', 'Video uploaded successfully to Cloudinary!');

        } catch (\Exception $e) {
            Log::error('Video Upload Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['video_file'])
            ]);

            return back()
                ->withInput()
                ->with('error', 'An error occurred while uploading the video. Please try again.');
        }
    }

    public function deleteVideo($id)
    {
        try {
            $video = Video::findOrFail($id);
            
            // Delete from Cloudinary if it's a cloud video
            if (!$video->is_local && $video->video_path) {
                try {
                    $cloudinaryService = new CloudinaryService();
                    $cloudinaryService->deleteVideo($video->video_path);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete video from Cloudinary', [
                        'video_id' => $id,
                        'public_id' => $video->video_path,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with database deletion even if Cloudinary deletion fails
                }
            }
            
            // Delete local files if they exist
            if ($video->is_local) {
                if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
                    Storage::disk('public')->delete($video->video_path);
                }
                
                if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
                    Storage::disk('public')->delete($video->thumbnail);
                }
            }

            $video->delete();

            Log::info('Video deleted successfully', [
                'video_id' => $id,
                'title' => $video->title
            ]);

            return redirect()->back()->with('success', 'Video deleted successfully!');
            
        } catch (\Exception $e) {
            Log::error('Video deletion failed', [
                'video_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to delete video: ' . $e->getMessage());
        }
    }

    /**
     * Delete a book
     */
    public function deleteBook($id)
    {
        try {
            $book = Book::findOrFail($id);

            // If the book is not local (Cloudinary-hosted), delete from Cloudinary
            if (!$book->is_local && !empty($book->book_path)) {
                $cloudinaryService = new CloudinaryService();
                $cloudinaryService->deleteBook($book->book_path);
            }

            // Delete the book record from the database
            $book->delete();

            // Log the deletion
            Log::info('Book deleted successfully', [
                'book_id' => $id,
                'title' => $book->title
            ]);

            // Redirect with success message
            return redirect()->route('admin.books')
                ->with('success', 'Book deleted successfully!');

        } catch (\Exception $e) {
            // Log any errors
            Log::error('Book deletion failed', [
                'book_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Redirect back with error message
            return redirect()->back()
                ->with('error', 'Failed to delete book: ' . $e->getMessage());
        }
    }

    private function safelyStoreUploadedFile($file, $storagePath)
    {
        if (!$file || !$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $uniqueFileName = uniqid() . '_' . time() . '.' . $extension;
        
        $fullStoragePath = $storagePath . '/' . $uniqueFileName;
        
        // Store using Laravel's storage system
        $stored = Storage::disk('public')->putFileAs($storagePath, $file, $uniqueFileName);
        
        if (!$stored) {
            throw new \Exception("Failed to store uploaded file");
        }

        Log::info('File stored successfully', [
            'original_name' => $originalName,
            'stored_name' => $uniqueFileName,
            'storage_path' => $fullStoragePath
        ]);

        return $fullStoragePath;
    }

    public function bookList()
    {
        $books = Book::latest()->paginate(10);
        return view('admin.books', compact('books'));
    }

    public function bookCreateForm()
    {
        return view('admin.books.create');
    }

    public function videoList()
    {
        $videos = Video::latest()->paginate(10);
        return view('admin.videos', compact('videos'));
    }

    public function videoCreateForm()
    {
        return view('admin.videos.create');
    }

    public function downloadBook($id)
    {
        try {
            $book = Book::findOrFail($id);

            if (!$book->book_file) {
                return back()->with('error', 'No book file available.');
            }

            $filePath = Storage::disk('public')->path($book->book_file);

            if (!Storage::disk('public')->exists($book->book_file)) {
                return back()->with('error', 'Book file not found.');
            }

            return response()->download($filePath, pathinfo($book->book_file, PATHINFO_BASENAME));
            
        } catch (\Exception $e) {
            Log::error('Book download failed', [
                'book_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to download book: ' . $e->getMessage());
        }
    }



    public function membershipList()
{
    // Get users with their membership payments (not transactions)
    $users = \App\Models\User::with(['membershipPayments' => function($query) {
        $query->latest();
    }])
    ->latest()
    ->paginate(15);

    return view('admin.memberships', compact('users'));
}

public function membershipDetails($userId)
{
    $user = \App\Models\User::with(['membershipPayments' => function($query) {
        $query->latest();
    }])->findOrFail($userId);

    return view('admin.memberships.details', compact('user'));
}

public function userList()
{
    $users = \App\Models\User::with(['transactions', 'membershipPayments'])->latest()->paginate(15);
    return view('admin.users', compact('users'));
}

public function userDetails($userId)
{
    $user = \App\Models\User::with(['transactions', 'membershipPayments'])->findOrFail($userId);
    return view('admin.users.details', compact('user'));
}

public function paymentList()
{
    $payments = \App\Models\Transaction::with('user')->latest()->paginate(15);
    return view('admin.payments', compact('payments'));
}

public function paymentDetails($paymentId)
{
    $payment = \App\Models\Transaction::with('user')->findOrFail($paymentId);
    return view('admin.payments.details', compact('payment'));
}



 

public function activateMembership($userId)
{
    try {
        $user = \App\Models\User::findOrFail($userId);
        
        // Activate membership for 1 year from now
        $user->update([
            'membership_status' => 'active',
            'membership_expires_at' => now()->addYear()
        ]);

        \Log::info('Admin activated membership', [
            'user_id' => $userId,
            'admin_id' => auth('admin')->id()
        ]);

        return back()->with('success', 'Membership activated successfully!');
    } catch (\Exception $e) {
        \Log::error('Failed to activate membership', [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);
        return back()->with('error', 'Failed to activate membership: ' . $e->getMessage());
    }
}

public function suspendMembership($userId)
{
    try {
        $user = \App\Models\User::findOrFail($userId);
        
        $user->update([
            'membership_status' => 'suspended'
        ]);

        \Log::info('Admin suspended membership', [
            'user_id' => $userId,
            'admin_id' => auth('admin')->id()
        ]);

        return back()->with('success', 'Membership suspended successfully!');
    } catch (\Exception $e) {
        \Log::error('Failed to suspend membership', [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);
        return back()->with('error', 'Failed to suspend membership: ' . $e->getMessage());
    }
}

public function extendMembership(Request $request, $userId)
{
    try {
        $request->validate([
            'months' => 'required|integer|min:1|max:24'
        ]);

        $user = \App\Models\User::findOrFail($userId);
        
        // Extend membership
        $currentExpiry = $user->membership_expires_at ?? now();
        $newExpiry = $currentExpiry->addMonths($request->months);
        
        $user->update([
            'membership_status' => 'active',
            'membership_expires_at' => $newExpiry
        ]);

        \Log::info('Admin extended membership', [
            'user_id' => $userId,
            'months' => $request->months,
            'admin_id' => auth('admin')->id()
        ]);

        return back()->with('success', "Membership extended by {$request->months} months!");
    } catch (\Exception $e) {
        \Log::error('Failed to extend membership', [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);
        return back()->with('error', 'Failed to extend membership: ' . $e->getMessage());
    }
}
 
public function verifyPayment($paymentId)
{
    try {
        $payment = \App\Models\Transaction::findOrFail($paymentId);
        
        $payment->update([
            'status' => 'paid',
            'completed_at' => now()
        ]);
        return back()->with('success', 'Payment verified successfully!');
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to verify payment: ' . $e->getMessage());
    }
}

public function updateUserMembership(Request $request, $userId)
{
    try {
        $request->validate([
            'membership_status' => 'required|in:active,suspended,expired,pending',
            'membership_expires_at' => 'nullable|date'
        ]);
        $user = \App\Models\User::findOrFail($userId);
        
        $user->update([
            'membership_status' => $request->membership_status,
            'membership_expires_at' => $request->membership_expires_at
        ]);
        return back()->with('success', 'User membership updated successfully!');
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to update membership: ' . $e->getMessage());
    }

}

    /**
     * Show book edit form
     */
    public function editBook($id)
    {
        try {
            $book = Book::findOrFail($id);
            return view('admin.books.edit', compact('book'));
        } catch (\Exception $e) {
            \Log::error('Book edit error', [
                'book_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.books')
                ->with('error', 'Unable to find the specified book.');
        }
    }

    /**
     * Update book details
     */
    public function updateBook(Request $request, $id)
    {
        try {
            $book = Book::findOrFail($id);
    
            // Validate input
            $validatedData = $request->validate([
                'title' => 'required|string|max:500',
                'description' => 'nullable|string|max:2000',
                'book_type' => 'required|in:free,paid',
                'price' => 'nullable|numeric|min:0|max:9999.99',
                'is_available' => [
                    'nullable', 
                    function ($attribute, $value, $fail) {
                        // Convert checkbox/string input to boolean
                        if (is_string($value)) {
                            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        }
                        
                        if ($value === null) {
                            $fail('The is available field must be true or false.');
                        }
                    }
                ]
            ]);
    
            // Determine book type and pricing
            $bookType = $request->input('book_type');
            $price = $request->input('price', 0);
            
            // Set free/paid status based on selection
            $isFree = $bookType === 'free';
            $finalPrice = $isFree ? 0 : $price;
    
            // Validate price for paid books
            if (!$isFree && $finalPrice <= 0) {
                return redirect()->back()
                    ->withErrors(['price' => 'Paid books must have a price greater than 0'])
                    ->withInput();
            }
    
            // Update book details
            $book->update([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'price' => $finalPrice,
                'is_free' => $isFree,
                'is_available' => filter_var($request->input('is_available', false), FILTER_VALIDATE_BOOLEAN)
            ]);
    
            // Log the update
            \Log::info('Book updated successfully', [
                'book_id' => $book->id,
                'title' => $book->title,
                'is_free' => $book->is_free,
                'price' => $book->price
            ]);
    
            return redirect()->route('admin.books')
                ->with('success', "Book \"{$book->title}\" updated successfully!");
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            \Log::warning('Book update validation failed', [
                'book_id' => $id,
                'errors' => $e->validator->errors()->toArray(),
                'input' => $request->except(['book_file'])
            ]);
            
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput($request->except(['book_file']));
    
        } catch (\Exception $e) {
            // Unexpected errors
            \Log::error('Book update failed', [
                'book_id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);
    
            return redirect()->back()
                ->with('error', 'Failed to update book. Please try again.')
                ->withInput($request->except(['book_file']));
        }
    }

    /**
     * Show video edit form
     */
    public function editVideo($id)
    {
        try {
            $video = Video::findOrFail($id);
            return view('admin.videos.edit', compact('video'));
        } catch (\Exception $e) {
            \Log::error('Video edit error', [
                'video_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.videos')
                ->with('error', 'Unable to find the specified video.');
        }
    }

    /**
     * Update video details
     */
    public function updateVideo(Request $request, $id)
    {
        try {
            $video = Video::findOrFail($id);
    
            // Validate input
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:100',
                'video_type' => 'required|in:free,paid',
                'price' => 'nullable|numeric|min:0|max:9999.99',
                'is_active' => 'boolean'
            ]);
    
            // Determine video type and pricing
            $videoType = $request->input('video_type');
            $price = $request->input('price', 0);
            
            // Set free/paid status based on selection
            $isFree = $videoType === 'free';
            $finalPrice = $isFree ? 0 : $price;
    
            // Validate price for paid videos
            if (!$isFree && $finalPrice <= 0) {
                return redirect()->back()
                    ->withErrors(['price' => 'Paid videos must have a price greater than 0'])
                    ->withInput();
            }
    
            // Update video details
            $video->update([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'category' => $validatedData['category'] ?? null,
                'price' => $finalPrice,
                'is_free' => $isFree,
                'is_active' => $request->boolean('is_active', true)
            ]);
    
            // Log the update
            \Log::info('Video updated successfully', [
                'video_id' => $video->id,
                'title' => $video->title,
                'is_free' => $video->is_free,
                'price' => $video->price
            ]);
    
            return redirect()->route('admin.videos')
                ->with('success', "Video \"{$video->title}\" updated successfully!");
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            \Log::warning('Video update validation failed', [
                'video_id' => $id,
                'errors' => $e->validator->errors()->toArray(),
                'input' => $request->except(['video_file'])
            ]);
            
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput($request->except(['video_file']));
    
        } catch (\Exception $e) {
            // Unexpected errors
            \Log::error('Video update failed', [
                'video_id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);
    
            return redirect()->back()
                ->with('error', 'Failed to update video. Please try again.')
                ->withInput($request->except(['video_file']));
        }
    }

    public function showReports()
    {
        return view('admin.reports');
    }

    public function downloadReport(Request $request)
    {
        $reportType = $request->input('report_type');

        switch ($reportType) {
            case 'membership_status':
                return $this->generateMembershipStatusReport();
            case 'expiring_memberships':
                return $this->generateExpiringMembershipsReport();
            case 'revenue_summary':
                return $this->generateRevenueSummaryReport();
            case 'payment_transactions':
                return $this->generatePaymentTransactionsReport();
            case 'user_registrations':
                return $this->generateUserRegistrationsReport();
            case 'user_activity':
                return $this->generateUserActivityReport();
            default:
                return back()->with('error', 'Invalid report type');
        }
    }

    private function generateMembershipStatusReport()
    {
        $users = \App\Models\User::select('id', 'name', 'email', 'membership_status', 'membership_expires_at')
            ->get();

        $filename = 'membership_status_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['User ID', 'Name', 'Email', 'Membership Status', 'Expiration Date']);

        foreach ($users as $user) {
            fputcsv($handle, [
                $user->id,
                $user->name,
                $user->email,
                $user->membership_status,
                $user->membership_expires_at ? $user->membership_expires_at->format('Y-m-d H:i:s') : 'N/A'
            ]);
        }

        fclose($handle);
        return response()->stream(
            function() use ($handle) {},
            200,
            $headers
        );
    }

    private function generateExpiringMembershipsReport()
    {
        $expiringUsers = \App\Models\User::where('membership_status', 'active')
            ->where('membership_expires_at', '<=', now()->addDays(30))
            ->select('id', 'name', 'email', 'membership_expires_at')
            ->get();

        $filename = 'expiring_memberships_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['User ID', 'Name', 'Email', 'Membership Expiration Date']);

        foreach ($expiringUsers as $user) {
            fputcsv($handle, [
                $user->id,
                $user->name,
                $user->email,
                $user->membership_expires_at->format('Y-m-d H:i:s')
            ]);
        }

        fclose($handle);
        return response()->stream(
            function() use ($handle) {},
            200,
            $headers
        );
    }

    private function generateRevenueSummaryReport()
    {
        $transactions = \App\Models\Transaction::where('status', 'paid')
            ->selectRaw('DATEPART(year, created_at) as year, DATEPART(month, created_at) as month, SUM(amount) as total_revenue')
            ->groupBy(\DB::raw('DATEPART(year, created_at), DATEPART(month, created_at)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $filename = 'revenue_summary_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Year', 'Month', 'Total Revenue (KSh)']);

        foreach ($transactions as $transaction) {
            fputcsv($handle, [
                $transaction->year,
                $transaction->month,
                number_format($transaction->total_revenue, 2)
            ]);
        }

        fclose($handle);
        return response()->stream(
            function() use ($handle) {},
            200,
            $headers
        );
    }

    private function generatePaymentTransactionsReport()
    {
        $transactions = \App\Models\Transaction::with('user')
            ->where('status', 'paid')
            ->select('id', 'user_id', 'amount', 'content_type', 'content_id', 'status', 'created_at')
            ->get();

        $filename = 'payment_transactions_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Transaction ID', 'User ID', 'User Name', 'Amount (KSh)', 'Content Type', 'Content ID', 'Status', 'Transaction Date']);

        foreach ($transactions as $transaction) {
            fputcsv($handle, [
                $transaction->id,
                $transaction->user_id,
                $transaction->user->name ?? 'N/A',
                number_format($transaction->amount, 2),
                $transaction->content_type,
                $transaction->content_id,
                $transaction->status,
                $transaction->created_at->format('Y-m-d H:i:s')
            ]);
        }

        fclose($handle);
        return response()->stream(
            function() use ($handle) {},
            200,
            $headers
        );
    }

    private function generateUserRegistrationsReport()
    {
        $users = \App\Models\User::selectRaw('DATEPART(year, created_at) as year, DATEPART(month, created_at) as month, COUNT(*) as total_registrations')
            ->groupBy(\DB::raw('DATEPART(year, created_at), DATEPART(month, created_at)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $filename = 'user_registrations_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Year', 'Month', 'Total Registrations']);

        foreach ($users as $user) {
            fputcsv($handle, [
                $user->year,
                $user->month,
                $user->total_registrations
            ]);
        }

        fclose($handle);
        return response()->stream(
            function() use ($handle) {},
            200,
            $headers
        );
    }

    private function generateUserActivityReport()
{
    $users = \App\Models\User::with(['transactions', 'membershipPayments'])
        ->select('id', 'name', 'email', 'created_at')  // Changed to created_at
        ->get()
        ->map(function($user) {
            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'registered_date' => $user->created_at->format('Y-m-d H:i:s'),
                'total_transactions' => $user->transactions->count(),
                'total_spent' => $user->transactions->sum('amount'),
                'total_membership_payments' => $user->membershipPayments->count(),
                'total_membership_spent' => $user->membershipPayments->sum('amount')
            ];
        });

    $filename = 'user_activity_' . now()->format('Y-m-d') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    $callback = function() use ($users) {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['User ID', 'Name', 'Email', 'Registered Date', 'Total Transactions', 'Total Spent (KSh)', 'Total Membership Payments', 'Total Membership Spent (KSh)']);

        foreach ($users as $user) {
            fputcsv($handle, [
                $user['user_id'],
                $user['name'],
                $user['email'],
                $user['registered_date'],
                $user['total_transactions'],
                number_format($user['total_spent'], 2),
                $user['total_membership_payments'],
                number_format($user['total_membership_spent'], 2)
            ]);
        }

        fclose($handle);
    };

    return response()->stream($callback, 200, $headers);
}


// Testimonials Management
public function testimonialsList()
{
    $testimonials = \App\Models\Testimonial::with('user')
        ->latest()
        ->paginate(15);
    return view('admin.testimonials.index', compact('testimonials'));
}

public function testimonialsCreate()
{
    return view('admin.testimonials.create');
}

public function testimonialsStore(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'content' => 'required|string',
        'position' => 'nullable|string|max:255',
        'company' => 'nullable|string|max:255',
        'rating' => 'required|integer|min:1|max:5',
        'is_active' => 'boolean'
    ]);

    \App\Models\Testimonial::create(array_merge($validated, [
        'status' => 'approved',
        'admin_id' => auth('admin')->id()
    ]));

    return redirect()->route('admin.testimonials.index')
        ->with('success', 'Testimonial created successfully!');
}

public function testimonialsEdit($id)
{
    $testimonial = \App\Models\Testimonial::findOrFail($id);
    return view('admin.testimonials.edit', compact('testimonial'));
}

public function testimonialsUpdate(Request $request, $id)
{
    $testimonial = \App\Models\Testimonial::findOrFail($id);
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'content' => 'required|string',
        'position' => 'nullable|string|max:255',
        'company' => 'nullable|string|max:255',
        'rating' => 'required|integer|min:1|max:5',
        'is_active' => 'boolean'
    ]);

    $testimonial->update($validated);

    return redirect()->route('admin.testimonials.index')
        ->with('success', 'Testimonial updated successfully!');
}

public function testimonialsDestroy($id)
{
    $testimonial = \App\Models\Testimonial::findOrFail($id);
    $testimonial->delete();

    return redirect()->route('admin.testimonials.index')
        ->with('success', 'Testimonial deleted successfully!');
}

public function testimonialsApprove($id)
{
    $testimonial = \App\Models\Testimonial::findOrFail($id);
    $testimonial->approve(auth('admin')->user());

    return back()->with('success', 'Testimonial approved!');
}

public function testimonialsReject(Request $request, $id)
{
    $testimonial = \App\Models\Testimonial::findOrFail($id);
    $testimonial->reject(auth('admin')->user(), $request->admin_comment);

    return back()->with('success', 'Testimonial rejected!');
}
}