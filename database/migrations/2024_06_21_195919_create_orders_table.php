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
            $table->uuid('user_id')->nullable();
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('pickup_location_id');
            $table->unsignedBigInteger('delivery_location_id');

            $table->string('item_name');
            $table->string('preferred_vehicle');
            $table->string('status')->default('created');
            $table->string('schedule_type')->default('now');
            $table->timestamp('schedule_time')->nullable()->default(now());

            $table->string('payment_method')->default('card');
            $table->decimal('price');
            $table->boolean('paid')->default(false);
            $table->timestamps();

            $table->foreign('recipient_id')->references('id')->on('recipients')->onDelete('set null');
            $table->foreign('pickup_location_id')->references('id')->on('locations')->onDelete('set null');
            $table->foreign('delivery_location_id')->references('id')->on('locations')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
