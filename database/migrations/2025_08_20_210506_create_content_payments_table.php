<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('content_type')->nullable(); // book, video, etc.
            $table->unsignedBigInteger('content_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('phone_number')->nullable();
            $table->string('reference')->unique();
            $table->string('checkout_request_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('metadata')->nullable();
            
            $table->timestamps();

            // Optional foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_payments');
    }
};
