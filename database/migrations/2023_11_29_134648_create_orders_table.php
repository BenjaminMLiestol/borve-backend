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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('customer_id');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->float('price');
            $table->string('address_from');
            $table->string('address_to');
            $table->dateTime('start_time');
            $table->integer('status')->default(0);
            $table->integer('time_spent')->nullable();
            $table->integer('km_driven')->nullable();
            $table->string('comment', 200)->nullable();
            $table->integer('passenger_count');
            $table->string('created_by');
            $table->foreign('created_by')->references('user_id')->on('users');
            $table->timestamps();
            $table->softDeletes();
            $table->dateTime('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
