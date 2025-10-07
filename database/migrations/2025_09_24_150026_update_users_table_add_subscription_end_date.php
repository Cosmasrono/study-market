<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableAddSubscriptionEndDate extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add subscription end date
            $table->timestamp('subscription_end_date')->nullable()->after('membership_expired_at');
            
            // Add columns to track subscription details
            $table->string('current_subscription_type')->nullable()->after('membership_type');
            $table->decimal('current_subscription_price', 10, 2)->nullable()->after('current_subscription_type');
            
            // Add a flag to indicate if subscription is active
            $table->boolean('is_subscription_active')->default(false)->after('current_subscription_price');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_end_date', 
                'current_subscription_type', 
                'current_subscription_price',
                'is_subscription_active'
            ]);
        });
    }
}
