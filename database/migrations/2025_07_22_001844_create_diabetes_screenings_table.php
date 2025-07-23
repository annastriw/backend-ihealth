<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diabetes_screenings', function (Blueprint $table) {
            $table->id();
            $table->char('user_id', 36);

            // Data input
            $table->tinyInteger('age')->nullable();
            $table->string('gender')->nullable(); // Laki-laki / Perempuan
            $table->decimal('bmi', 5, 2)->nullable();
            $table->string('smoking_history')->nullable(); // Tidak pernah merokok, dll
            $table->string('high_blood_pressure')->nullable(); // Tinggi / Rendah
            $table->decimal('blood_glucose_level', 8, 2)->nullable(); // Gula darah sewaktu

            // Prediction output
            $table->string('prediction_result')->nullable(); // Rendah / Sedang / Tinggi
            $table->decimal('prediction_score', 5, 2)->nullable(); // 83.21 (persen)
            $table->text('recommendation')->nullable(); // Rekomendasi gaya hidup
            $table->timestamp('screening_date')->nullable(); // Waktu prediksi

            // Optional: response mentah
            $table->json('ml_response')->nullable();

            $table->timestamps();

            // Relasi dan index
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'screening_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diabetes_screenings');
    }
};
