<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('checkout_request_id')->unique();
            $table->string('merchant_request_id')->nullable();
            $table->string('content_type', 20)->nullable(); // 'book' or 'video'
            $table->unsignedBigInteger('content_id')->nullable();
            $table->string('phone', 20);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('mpesa_receipt_number')->nullable();
            $table->json('response_data')->nullable();
            $table->string('result_desc')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['checkout_request_id', 'status']);
            $table->index(['user_id', 'content_type', 'content_id']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};