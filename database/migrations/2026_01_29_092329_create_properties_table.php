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
        Schema::create('properties', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('picture')->nullable();
            $table->string('name')->nullable();
            $table->integer('number_of_units')->nullable()->default(0);
            $table->string('location')->nullable();
            $table->integer('water_unit_cost')->nullable()->default(0);
            $table->enum('property_type', ['residential', 'commercial', 'industrial'])->default('residential');
            $table->boolean('deposit_required')->nullable()->default(false);
            $table->integer('rent_due_date')->nullable()->default(5);
            $table->boolean('is_deleted')->default(0);
            $table->json('additional_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
