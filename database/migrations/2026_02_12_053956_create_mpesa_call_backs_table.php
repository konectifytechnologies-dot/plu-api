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
        Schema::create('mpesa_call_backs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('CheckoutRequestID')->unique();
            $table->string('user_id')->nullable();
            $table->json('callback_json');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_call_backs');
    }
};
