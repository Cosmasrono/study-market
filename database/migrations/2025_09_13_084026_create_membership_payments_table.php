<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('membership_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('phone_number');
            $table->string('reference')->unique()->index();
            $table->string('transaction_reference')->unique()->index();
            $table->string('payhero_reference')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('payment_method')->default('payhero');
            $table->string('subscription_duration')->default('1_year'); // '3_months', '6_months', '9_months', '1_year'
            $table->boolean('is_renewal')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->string('mpesa_receipt_number')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('membership_payments');
    }
};