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
        Schema::create('personal_information', function (Blueprint $table) {

            // Primary Key
            $table->uuid('id')->primary(); // CHAR(36)

            // Foreign Key
            $table->uuid('user_id'); // CHAR(36)

            // Data Pribadi
            $table->string('name', 255);
            $table->string('place_of_birth', 255);
            $table->date('date_of_birth');
            $table->string('age', 255);
            $table->string('gender', 255);
            $table->string('work', 255);
            $table->boolean('is_married'); // ✅ PERUBAHAN DI SINI
            $table->string('last_education', 255);

            // Data Penyakit
            $table->string('origin_disease', 255);
            $table->string('disease_duration', 255);
            $table->text('history_therapy');

            // TIMESTAMP NULL sesuai gambar
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Foreign Key Constraint
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_information');
    }
};
