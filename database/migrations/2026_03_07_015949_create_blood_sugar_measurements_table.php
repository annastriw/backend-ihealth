<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_sugar_measurements', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('random_blood_sugar');
            $table->string('source')->default('esp32');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_sugar_measurements');
    }
};