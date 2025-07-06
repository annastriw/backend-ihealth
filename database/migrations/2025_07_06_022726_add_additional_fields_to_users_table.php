<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_information', function (Blueprint $table) {
            $table->string('smoking_history')->nullable();
            $table->string('body_mass_index')->nullable(); 
            $table->string('heart_disease_history')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('personal_information', function (Blueprint $table) {
            $table->dropColumn(['smoking_history', 'body_mass_index', 'heart_disease_history']);
        });
    }
};