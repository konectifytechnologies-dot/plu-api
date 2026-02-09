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
        Schema::create('water_readings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('property_id');
            $table->string('unit_id');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->unsignedInteger('year');
            $table->unsignedInteger('month');
            $table->decimal('previous_reading', 10,2);
            $table->decimal('current_reading', 10,2);
            $table->decimal('units_consumed', 10,2);
            $table->decimal('amount', 12,2);

            $table->timestamps();
            $table->unique(['unit_id', 'year', 'month']);
            $table->index(['unit_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_readings');
    }
};
