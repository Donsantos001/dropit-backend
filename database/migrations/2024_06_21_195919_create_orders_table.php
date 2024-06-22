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
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('item_name');
            $table->string('receiver_firstname');
            $table->string('receiver_lastname');
            $table->string('receiver_phone_no');
            $table->string('receiver_email');
            $table->string('delivery_address');
            $table->string('pickup_address');
            $table->string('status')->default('pending');
            $table->string('payment_method');
            $table->string('preferred_vehicle');
            $table->string('schedule_type')->default('now');
            $table->timestamp('schedule_time')->nullable()->default(now());
            $table->timestamps();
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
