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
        Schema::create('diabetes_screenings', function (Blueprint $table) {
            $table->id();
            
            // Ubah sesuai tipe data id di tabel users
            // Jika users.id menggunakan unsignedBigInteger atau bigint unsigned
            $table->unsignedBigInteger('user_id');
            
            // Input screening fields
            $table->tinyInteger('hypertension')->nullable(); // 0=rendah, 1=tinggi
            $table->decimal('blood_glucose_level', 8, 2)->nullable(); // Gula darah sewaktu
            
            // Prediction result fields
            $table->tinyInteger('diabetes_prediction')->nullable(); // 0=tidak berisiko, 1=berisiko
            $table->decimal('prediction_probability', 5, 4)->nullable(); // Probabilitas prediksi
            $table->string('risk_level')->nullable(); // high/medium/low
            $table->json('ml_response')->nullable(); // Raw response dari ML API
            $table->timestamp('predicted_at')->nullable(); // Waktu prediksi
            
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Index untuk performance
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diabetes_screenings');
    }
};