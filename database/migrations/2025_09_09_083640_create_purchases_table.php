<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('item_type'); // 'book' or 'video'
            $table->unsignedBigInteger('item_id');
            $table->string('item_title');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // 'mpesa' or 'card'
            $table->string('transaction_id')->unique();
            $table->string('reference')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->json('payment_data')->nullable(); // Store additional payment info
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'item_type', 'item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};