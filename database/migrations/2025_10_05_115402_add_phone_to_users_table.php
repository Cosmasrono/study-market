<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add phone column only if it doesn't exist
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 20)
                      ->nullable()
                      ->comment('User\'s phone number for M-Pesa payments');
            }
            
            // Add subscription type column only if it doesn't exist
            if (!Schema::hasColumn('users', 'current_subscription_type')) {
                $table->string('current_subscription_type')
                      ->nullable()
                      ->comment('Type of current subscription (e.g., 6_months, 1_year)');
            }
            
            // Add subscription end date column only if it doesn't exist
            if (!Schema::hasColumn('users', 'subscription_end_date')) {
                $table->datetime('subscription_end_date')
                      ->nullable()
                      ->comment('Date when current subscription expires');
            }
            
            // Add subscription active status column only if it doesn't exist
            if (!Schema::hasColumn('users', 'is_subscription_active')) {
                $table->boolean('is_subscription_active')
                      ->default(false)
                      ->comment('Whether user has an active subscription');
            }
        });

        // Manually handle unique index to avoid duplicate key issues
        if (!Schema::hasColumn('users', 'phone_number_unique')) {
            try {
                // Remove any existing unique index on phone_number
                DB::statement('DROP INDEX IF EXISTS users_phone_number_unique ON users');
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }

            // Create a unique index that allows NULL values
            DB::statement('CREATE UNIQUE NONCLUSTERED INDEX users_phone_number_unique ON users (phone_number) WHERE phone_number IS NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop columns only if they exist
            $columns = [
                'phone_number', 
                'current_subscription_type', 
                'subscription_end_date', 
                'is_subscription_active'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Drop the unique index
        try {
            DB::statement('DROP INDEX IF EXISTS users_phone_number_unique ON users');
        } catch (\Exception $e) {
            // Ignore if index doesn't exist
        }
    }
};
