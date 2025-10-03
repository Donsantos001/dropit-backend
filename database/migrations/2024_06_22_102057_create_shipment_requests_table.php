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
        Schema::create('shipment_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('agent_id')->nullable();
            $table->unsignedBigInteger('shipment_id');
            $table->boolean('active')->default(true);
            $table->decimal('price');
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_requests');
    }
};
