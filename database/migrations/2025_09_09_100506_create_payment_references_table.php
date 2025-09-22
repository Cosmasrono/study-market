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
        Schema::create('payment_references', function (Blueprint $table) {
            $table->id();
            $table->string('tx_ref')->nullable(); // For Flutterwave
            $table->string('order_id')->nullable(); // For Pesapal/M-Pesa
            $table->string('payment_intent_id')->nullable(); // For Stripe
            $table->unsignedBigInteger('user_id');
            $table->string('item_type'); // 'book' or 'video'
            $table->unsignedBigInteger('item_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('original_amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('original_currency', 3)->default('KES');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method'); // 'card', 'mpesa', 'stripe_card'
            $table->string('transaction_id')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['tx_ref', 'order_id']);
            $table->index('payment_intent_id');
            $table->index(['user_id', 'status']);
            $table->index(['item_type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_references');
    }
};