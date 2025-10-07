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
        Schema::table('testimonials', function (Blueprint $table) {
            // Add user_id column for tracking who submitted the testimonial
            if (!Schema::hasColumn('testimonials', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }

            // Add approval status column
            if (!Schema::hasColumn('testimonials', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            }

            // Add admin_id column for tracking which admin approved/rejected
            if (!Schema::hasColumn('testimonials', 'admin_id')) {
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
            }

            // Add column for admin's review comment
            if (!Schema::hasColumn('testimonials', 'admin_comment')) {
                $table->text('admin_comment')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('testimonials', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('testimonials', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('testimonials', 'admin_id')) {
                $table->dropForeign(['admin_id']);
                $table->dropColumn('admin_id');
            }

            if (Schema::hasColumn('testimonials', 'admin_comment')) {
                $table->dropColumn('admin_comment');
            }
        });
    }
};
