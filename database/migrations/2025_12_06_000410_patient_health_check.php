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
        Schema::create('patient_health_check', function (Blueprint $table) {
            $table->char('id', 36)->primary(); // UUID sebagai primary key

            // Foreign key ke tabel personal_information (one-to-many)
            $table->char('personal_information_id', 36);
            $table->foreign('personal_information_id')
                  ->references('id')->on('personal_information')
                  ->onDelete('cascade');

            // Data pasien (prefill)
            $table->string('name', 255);
            $table->string('age', 255);
            $table->string('gender', 255);

            // Tanggal cek
            $table->date('check_date');

            // Parameter kesehatan
            $table->integer('blood_pressure_systolic');
            $table->integer('blood_pressure_diastolic');
            $table->boolean('hypertension');

            $table->integer('random_blood_sugar');
            $table->boolean('diabetes');

            $table->integer('cholesterol_level');

            $table->decimal('height', 5, 2);
            $table->decimal('weight', 5, 2);
            $table->decimal('bmi', 4, 2);
            $table->boolean('obesity');

            $table->integer('waist_circumference');
            $table->boolean('family_history');

            $table->enum('smoking_status', ['NEVER','CURRENT','PAST']);
            $table->enum('physical_activity', ['LOW','MODERATE','HIGH']);
            $table->enum('dietary_habits', ['UNHEALTHY','HEALTHY']);
            $table->enum('stress_level', ['LOW','MODERATE','HIGH']);

            $table->integer('sleep_hours');
            $table->boolean('previous_heart_disease');
            $table->boolean('medication_usage');

            // Timestamps Laravel
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_health_check');
    }
};
