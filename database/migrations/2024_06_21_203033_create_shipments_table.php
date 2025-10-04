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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->uuid('agent_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('current_location_id');
            $table->unsignedBigInteger('stop_location_id')->nullable();

            $table->timestamp('estimated_delivery_time')->nullable();
            $table->string('status')->default('in_transit');
            $table->string('vehicle_type')->nullable();
            $table->timestamps();

            $table->foreign('current_location_id')->references('id')->on('locations')->onDelete('set null');
            $table->foreign('stop_location_id')->references('id')->on('locations')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
