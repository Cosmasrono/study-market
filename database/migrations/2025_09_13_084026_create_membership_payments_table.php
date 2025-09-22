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
        Schema::create('membership_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('mpesa'); // mpesa, card, bank_transfer
            $table->string('transaction_id')->unique();
            $table->string('reference_id')->nullable(); // External payment gateway reference
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_gateway')->nullable(); // safaricom, stripe, paypal etc
            $table->json('payment_data')->nullable(); // Store additional payment info
            $table->string('phone_number')->nullable(); // For M-Pesa payments
            $table->datetime('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_payments');
    }
};