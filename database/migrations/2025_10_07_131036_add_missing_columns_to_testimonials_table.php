<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (!Schema::hasColumn('testimonials', 'company')) {
                $table->string('company')->nullable()->after('position');
            }
            if (!Schema::hasColumn('testimonials', 'admin_id')) {
                $table->foreignId('admin_id')->nullable()->after('is_active')->constrained('admins')->onDelete('set null');
            }
            if (!Schema::hasColumn('testimonials', 'admin_comment')) {
                $table->text('admin_comment')->nullable()->after('admin_id');
            }
            if (!Schema::hasColumn('testimonials', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('admin_comment');
            }
            if (!Schema::hasColumn('testimonials', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down()
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $columns = ['company', 'admin_id', 'admin_comment', 'approved_at', 'rejected_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('testimonials', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};