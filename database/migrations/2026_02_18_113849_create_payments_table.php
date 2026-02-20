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
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id', 26)->primary();
            $table->string('property_id', 26);
            $table->string('user_id', 26);
            $table->string('tenancy_id', 26);
            $table->string('cost_id', 26)->nullable();
            $table->string('payment_type')->default('RENT');
            $table->string('payment_method')->nullable();
            $table->string('reference_code')->nullable();
            $table->string('date')->nullable();
            $table->integer('year');
            $table->string('description')->nullable();
            $table->integer('amount_due');
            $table->integer('amount_paid');
            $table->integer('balance')->default(0);
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tenancy_id')->references('id')->on('tenancies')->onDelete('cascade');
            $table->foreign('cost_id')->references('id')->on('additional_costs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
