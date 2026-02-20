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
        Schema::create('mpesa_responses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('MerchantRequestID')->unique();
            $table->string('CheckoutRequestID')->unique();
            $table->string('ResponseCode');
            $table->string('ResponseDescription');
            $table->string('CustomerMessage')->nullable();
            $table->string('amount')->nullable();
            $table->string('MpesaReceiptNumber')->nullable()->unique();
            $table->string('TransactionDate')->nullable();
            $table->string('PhoneNumber')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_responses');
    }
};
