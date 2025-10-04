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
        Schema::create('agent_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('agent_id');
            $table->unsignedBigInteger('order_id');
            // requested, accepted, rejected
            $table->string('status')->default("requested");
            $table->longText('message')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_requests');
    }
};
