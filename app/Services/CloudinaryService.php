<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private $cloudinary;
    private $uploadApi;

    public function __construct()
    {
        // Retrieve Cloudinary credentials directly from environment
        $cloudName = env('CLOUDINARY_CLOUD_NAME', '');
        $apiKey = env('CLOUDINARY_API_KEY', '');
        $apiSecret = env('CLOUDINARY_API_SECRET', '');

        // Validate credentials with detailed logging
        if (empty($cloudName)) {
            Log::error('Cloudinary Configuration Error', ['message' => 'Cloud Name is missing']);
            throw new \Exception('Cloudinary Cloud Name is not set.');
        }
        if (empty($apiKey)) {
            Log::error('Cloudinary Configuration Error', ['message' => 'API Key is missing']);
            throw new \Exception('Cloudinary API Key is not set.');
        }
        if (empty($apiSecret)) {
            Log::error('Cloudinary Configuration Error', ['message' => 'API Secret is missing']);
            throw new \Exception('Cloudinary API Secret is not set.');
        }

        // Create Cloudinary configuration
        $config = new Configuration([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        $this->cloudinary = new Cloudinary($config);
        $this->uploadApi = new UploadApi($config);
    }

    public function uploadVideo($file, $options = [])
    {
        try {
            // Default upload options
            $defaultOptions = [
                'resource_type' => 'video',
                'folder' => 'videos',
                'overwrite' => true,
                'unique_filename' => true,
            ];

            // Merge default options with provided options
            $uploadOptions = array_merge($defaultOptions, $options);

            // Extensive file debugging
            $this->debugFileUpload($file, 'video');

            // Prepare file path
            $filePath = $this->prepareFileForUpload($file, 'videos');

            // Validate file path
            if (!$filePath || !file_exists($filePath)) {
                throw new \Exception("Unable to prepare file for upload. File does not exist.");
            }

            // Get file size manually
            $fileSize = filesize($filePath);

            // Try using the official Cloudinary SDK first
            try {
                Log::info('Attempting upload with official Cloudinary SDK');
                $uploadResult = $this->uploadApi->upload($filePath, $uploadOptions);
                
                Log::info('Cloudinary SDK Upload Success', [
                    'public_id' => $uploadResult['public_id'] ?? 'N/A',
                    'url' => $uploadResult['secure_url'] ?? 'N/A',
                ]);

                return [
                    'public_id' => $uploadResult['public_id'] ?? null,
                    'url' => $uploadResult['secure_url'] ?? null,
                    'original_filename' => $file->getClientOriginalName(),
                    'size' => $fileSize,
                ];

            } catch (\Exception $sdkError) {
                Log::warning('Cloudinary SDK upload failed, trying custom cURL method', [
                    'sdk_error' => $sdkError->getMessage()
                ]);

                // Fallback to custom cURL method
                $mimeType = $this->getFileMimeType($filePath);
                $uploadResult = $this->customCloudinaryUpload(
                    $filePath, 
                    $file->getClientOriginalName(),
                    $mimeType,
                    $uploadOptions
                );

                Log::info('Cloudinary Custom Upload Success', [
                    'public_id' => $uploadResult['public_id'] ?? 'N/A',
                    'url' => $uploadResult['secure_url'] ?? 'N/A',
                ]);

                return [
                    'public_id' => $uploadResult['public_id'] ?? null,
                    'url' => $uploadResult['secure_url'] ?? null,
                    'original_filename' => $file->getClientOriginalName(),
                    'size' => $fileSize,
                ];
            }

        } catch (\Exception $e) {
            // Log and rethrow the error
            Log::error('Cloudinary Video Upload Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            // Clean up temporary file if it exists
            if (isset($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * Upload a book file to Cloudinary
     */
    public function uploadBook($file, $options = [])
    {
        try {
            // Determine resource type based on file MIME type
            $mimeType = $file->getMimeType();
            $resourceType = $this->getResourceTypeForMimeType($mimeType);

            // Default upload options
            $defaultOptions = [
                'resource_type' => $resourceType,
                'folder' => 'books',
                'overwrite' => true,
                'unique_filename' => true,
            ];

            // Merge default options with provided options
            $uploadOptions = array_merge($defaultOptions, $options);

            // Extensive file debugging
            $this->debugFileUpload($file, 'book');

            // Prepare file path
            $filePath = $this->prepareFileForUpload($file, 'books');

            // Validate file path
            if (!$filePath || !file_exists($filePath)) {
                throw new \Exception("Unable to prepare book file for upload. File does not exist.");
            }

            // Get file size manually
            $fileSize = filesize($filePath);

            // Try using the official Cloudinary SDK first
            try {
                Log::info('Attempting book upload with official Cloudinary SDK', [
                    'resource_type' => $resourceType,
                    'mime_type' => $mimeType
                ]);
                $uploadResult = $this->uploadApi->upload($filePath, $uploadOptions);
                
                Log::info('Cloudinary Book Upload Success', [
                    'public_id' => $uploadResult['public_id'] ?? 'N/A',
                    'url' => $uploadResult['secure_url'] ?? 'N/A',
                ]);

                return [
                    'public_id' => $uploadResult['public_id'] ?? null,
                    'url' => $uploadResult['secure_url'] ?? null,
                    'original_filename' => $file->getClientOriginalName(),
                    'size' => $fileSize,
                ];

            } catch (\Exception $sdkError) {
                Log::warning('Cloudinary SDK book upload failed, trying custom cURL method', [
                    'sdk_error' => $sdkError->getMessage(),
                    'resource_type' => $resourceType,
                    'mime_type' => $mimeType
                ]);

                // Fallback to custom cURL method
                $uploadResult = $this->customCloudinaryUpload(
                    $filePath, 
                    $file->getClientOriginalName(),
                    $mimeType,
                    $uploadOptions
                );

                Log::info('Cloudinary Custom Book Upload Success', [
                    'public_id' => $uploadResult['public_id'] ?? 'N/A',
                    'url' => $uploadResult['secure_url'] ?? 'N/A',
                ]);

                return [
                    'public_id' => $uploadResult['public_id'] ?? null,
                    'url' => $uploadResult['secure_url'] ?? null,
                    'original_filename' => $file->getClientOriginalName(),
                    'size' => $fileSize,
                ];
            }

        } catch (\Exception $e) {
            // Log and rethrow the error
            Log::error('Cloudinary Book Upload Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            // Clean up temporary file if it exists
            if (isset($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * Debug file upload details
     */
    private function debugFileUpload($file, $fileType = 'file')
    {
        // Extensive logging for file details
        Log::info(ucfirst($fileType) . ' File Upload Details', [
            'original_path' => $file->getRealPath() ?: 'PATH NOT FOUND',
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
            'is_valid' => $file->isValid(),
            'error' => $file->getError(),
            'temp_path' => $file->getPathname(),
            'storage_path' => storage_path('app/public/temp_' . $fileType . 's'),
        ]);
    }

    /**
     * Prepare file for upload by ensuring it's in a valid location
     */
    private function prepareFileForUpload($file, $fileType = 'videos')
    {
        // Ensure storage directory exists
        $tempDir = storage_path("app/public/temp_{$fileType}");
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Generate a unique filename
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $tempPath = $tempDir . '/' . $filename;

        // If file is an UploadedFile, move it
        if ($file instanceof UploadedFile) {
            $file->move($tempDir, $filename);
        } else {
            // If it's not an UploadedFile, try to copy
            if (!copy($file->getRealPath(), $tempPath)) {
                throw new \Exception("Failed to copy file to temporary location");
            }
        }

        Log::info(ucfirst($fileType) . ' File Prepared for Upload', [
            'temp_path' => $tempPath,
            'original_name' => $file->getClientOriginalName(),
            'file_exists' => file_exists($tempPath),
            'file_size' => filesize($tempPath)
        ]);

        return $tempPath;
    }

    /**
     * Custom Cloudinary upload method with SSL bypass for development
     * FIXED: Now uses the correct resource_type from options
     */
    private function customCloudinaryUpload($filePath, $originalFilename, $mimeType, $options = [])
    {
        // Validate file path
        if (empty($filePath) || !file_exists($filePath)) {
            throw new \ValueError("File path is invalid or does not exist: " . $filePath);
        }

        // Get the resource type from options (THIS WAS THE BUG!)
        $resourceType = $options['resource_type'] ?? 'raw';
        
        // Cloudinary API endpoint - FIXED to use correct resource type
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');
        
        $url = "https://api.cloudinary.com/v1_1/{$cloudName}/{$resourceType}/upload";

        // Fallback MIME type if not provided
        if (empty($mimeType)) {
            $mimeType = $this->getFileMimeType($filePath);
        }

        // Prepare the file for upload
        $file = new \CURLFile(
            $filePath, 
            $mimeType, 
            $originalFilename
        );

        // Prepare signature data
        $timestamp = time();
        $signatureData = [
            'timestamp' => $timestamp,
        ];

        // Allowed options for signature
        $allowedOptions = ['folder', 'overwrite', 'unique_filename'];
        
        // Normalize and add allowed options
        foreach ($options as $key => $value) {
            if (in_array($key, $allowedOptions)) {
                // Normalize boolean values to lowercase strings
                $signatureData[$key] = is_bool($value) 
                    ? ($value ? 'true' : 'false') 
                    : (string)$value;
            }
        }

        // Sort signature data alphabetically
        ksort($signatureData);

        // Build signature string
        $signatureParams = [];
        foreach ($signatureData as $key => $value) {
            $signatureParams[] = $key . '=' . $value;
        }
        $signatureString = implode('&', $signatureParams);

        // Generate signature
        $signature = hash('sha1', $signatureString . $apiSecret);

        // Detailed debug logging
        Log::info('Cloudinary Signature Debug', [
            'resource_type' => $resourceType,
            'signature_data' => $signatureData,
            'signature_string_without_secret' => implode('&', $signatureParams),
            'signature_string_with_secret' => $signatureString . $apiSecret,
            'signature' => $signature,
            'timestamp' => $timestamp,
            'api_url' => $url
        ]);

        // Prepare POST data
        $postData = [
            'file' => $file,
            'api_key' => $apiKey,
            'signature' => $signature,
            'timestamp' => $timestamp,
            'resource_type' => $resourceType, // FIXED: Use dynamic resource type
        ];

        // Add signature parameters to POST data
        foreach ($signatureData as $key => $value) {
            $postData[$key] = $value;
        }

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout for large files
        
        // Disable SSL verification (ONLY for development!)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Execute the request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL Error: {$error}");
        }

        // Get HTTP status code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Close cURL
        curl_close($ch);

        // Log the response for debugging
        Log::info('Cloudinary API Response', [
            'http_code' => $httpCode,
            'response' => $response,
            'resource_type' => $resourceType
        ]);

        // Parse response
        $result = json_decode($response, true);

        // Check for JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON response from Cloudinary: " . json_last_error_msg());
        }

        // Check for Cloudinary API errors
        if (isset($result['error'])) {
            throw new \Exception("Cloudinary Upload Error: " . $result['error']['message']);
        }

        // Check HTTP status code
        if ($httpCode >= 400) {
            throw new \Exception("HTTP Error {$httpCode}: " . ($result['error']['message'] ?? 'Unknown error'));
        }

        return $result;
    }

    /**
     * Fallback method to get MIME type
     */
    private function getFileMimeType($filePath)
    {
        // Try different methods to get MIME type
        if (function_exists('mime_content_type')) {
            return mime_content_type($filePath);
        }

        // Fallback based on file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            // Video types
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
            'wmv' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv',
            'webm' => 'video/webm',
            
            // Document types
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'epub' => 'application/epub+zip',
            'mobi' => 'application/x-mobipocket-ebook',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Determine Cloudinary resource type based on MIME type
     */
    private function getResourceTypeForMimeType($mimeType)
    {
        // Define MIME type to resource type mapping
        $resourceTypeMap = [
            // Video types
            'video/mp4' => 'video',
            'video/x-msvideo' => 'video',
            'video/quicktime' => 'video',
            'video/x-ms-wmv' => 'video',
            'video/x-flv' => 'video',
            'video/webm' => 'video',
            
            // Document types (use 'raw' for all documents)
            'application/pdf' => 'raw',
            'application/msword' => 'raw',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'raw',
            'application/epub+zip' => 'raw',
            'application/x-mobipocket-ebook' => 'raw',
            
            // Image types
            'image/jpeg' => 'image',
            'image/png' => 'image',
            'image/gif' => 'image',
            
            // Default to raw for unknown types
            'default' => 'raw'
        ];

        // Return mapped resource type or default
        return $resourceTypeMap[$mimeType] ?? $resourceTypeMap['default'];
    }

    public function deleteVideo($publicId)
    {
        try {
            $result = $this->uploadApi->destroy($publicId, [
                'resource_type' => 'video'
            ]);

            Log::info('Cloudinary Video Deletion', [
                'public_id' => $publicId,
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Cloudinary Video Deletion Failed', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Delete a book file from Cloudinary
     */
    public function deleteBook($publicId)
    {
        try {
            $result = $this->uploadApi->destroy($publicId, [
                'resource_type' => 'raw'
            ]);

            Log::info('Cloudinary Book Deletion', [
                'public_id' => $publicId,
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Cloudinary Book Deletion Failed', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}