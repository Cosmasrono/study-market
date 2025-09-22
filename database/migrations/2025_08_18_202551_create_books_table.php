<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->string('book_file', 1000)->nullable(); // Local file path
            $table->string('book_url')->nullable(); // Cloudinary URL
            $table->string('book_path')->nullable(); // Cloudinary public ID
            $table->boolean('is_local')->default(true); // Flag to indicate storage location
            $table->unsignedBigInteger('file_size')->nullable(); // File size in bytes
            $table->string('original_filename')->nullable(); // Original filename
            $table->string('mime_type')->nullable(); // File MIME type
            $table->boolean('is_available')->default(true); // Book availability
            $table->decimal('price', 10, 2)->default(0.00); // Book price in decimal
            $table->boolean('is_free')->default(true); // Explicit free/paid flag
            $table->timestamps();

            // Add indexes for common queries
            $table->index(['is_available', 'is_free']);
            $table->index('price');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};