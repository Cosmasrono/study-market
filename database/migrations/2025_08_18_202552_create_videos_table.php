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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url')->nullable(); // External URL
            $table->string('video_path')->nullable(); // Local file path
            $table->string('video_type')->nullable(); // Type of video (external/local)
            $table->string('file_name')->nullable(); // Original file name
            $table->string('mime_type')->nullable(); // MIME type of the video
            $table->unsignedBigInteger('file_size')->nullable(); // File size in bytes.
            $table->boolean('is_local')->default(true); // Flag to indicate storage location
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0);
            $table->unsignedBigInteger('uploaded_by')->nullable(); // Remove foreign key constraint
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
