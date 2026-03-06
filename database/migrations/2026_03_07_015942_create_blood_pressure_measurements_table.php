<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_pressure_measurements', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('blood_pressure_systolic');
            $table->unsignedInteger('blood_pressure_diastolic');
            $table->string('source')->default('esp32');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_pressure_measurements');
    }
};