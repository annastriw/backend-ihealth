<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_information', function (Blueprint $table) {
            if (!Schema::hasColumn('personal_information', 'smoking_history')) {
                $table->string('smoking_history')->nullable();
            }
            if (!Schema::hasColumn('personal_information', 'body_mass_index')) {
                $table->string('body_mass_index')->nullable(); 
            }
            if (!Schema::hasColumn('personal_information', 'heart_disease_history')) {
                $table->string('heart_disease_history')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('personal_information', function (Blueprint $table) {
            // Gunakan pengecekan agar tidak error saat rollback juga
            if (Schema::hasColumn('personal_information', 'smoking_history')) {
                $table->dropColumn('smoking_history');
            }
            if (Schema::hasColumn('personal_information', 'body_mass_index')) {
                $table->dropColumn('body_mass_index');
            }
            if (Schema::hasColumn('personal_information', 'heart_disease_history')) {
                $table->dropColumn('heart_disease_history');
            }
        });
    }
};