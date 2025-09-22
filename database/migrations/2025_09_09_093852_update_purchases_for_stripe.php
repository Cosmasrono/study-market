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
        Schema::table('purchases', function (Blueprint $table) {
            // Add Stripe-specific columns
            $table->string('stripe_payment_intent_id')->nullable()->after('transaction_id');
            $table->string('stripe_customer_id')->nullable()->after('stripe_payment_intent_id');
            $table->string('currency', 3)->default('USD')->after('amount');
            $table->decimal('original_amount', 10, 2)->nullable()->after('currency');
            $table->string('original_currency', 3)->default('KES')->after('original_amount');
            $table->string('card_brand')->nullable()->after('payment_method'); // visa, mastercard, etc.
            $table->string('card_last4')->nullable()->after('card_brand'); // Last 4 digits
            $table->string('receipt_url')->nullable()->after('card_last4'); // Stripe receipt URL
            
            // Update payment_method enum to include stripe methods
            $table->string('payment_method')->change();
            
            // Add indexes
            $table->index('stripe_payment_intent_id');
            $table->index(['user_id', 'status']);
            $table->index(['item_type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['item_type', 'item_id']);
            
            // Drop columns
            $table->dropColumn([
                'stripe_payment_intent_id',
                'stripe_customer_id',
                'currency',
                'original_amount',
                'original_currency',
                'card_brand',
                'card_last4',
                'receipt_url'
            ]);
        });
    }
};