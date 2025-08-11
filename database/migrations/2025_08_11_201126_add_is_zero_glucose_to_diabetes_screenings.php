<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah kolom sudah ada untuk menghindari error
        if (!Schema::hasColumn('diabetes_screenings', 'is_zero_glucose')) {
            Schema::table('diabetes_screenings', function (Blueprint $table) {
                $table->boolean('is_zero_glucose')->default(false)->after('blood_glucose_level');
            });
        }

        if (!Schema::hasColumn('diabetes_screenings', 'hypertension_classification')) {
            Schema::table('diabetes_screenings', function (Blueprint $table) {
                $table->string('hypertension_classification', 100)->nullable()->after('high_blood_pressure');
            });
        }

        // Update blood_glucose_level ke nullable menggunakan raw SQL
        try {
            DB::statement('ALTER TABLE diabetes_screenings MODIFY COLUMN blood_glucose_level DECIMAL(8,2) NULL');
        } catch (\Exception $e) {
            // Jika gagal, coba dengan sintaks MySQL yang berbeda
            DB::statement('ALTER TABLE diabetes_screenings CHANGE blood_glucose_level blood_glucose_level DECIMAL(8,2) NULL');
        }
    }

    public function down(): void
    {
        Schema::table('diabetes_screenings', function (Blueprint $table) {
            if (Schema::hasColumn('diabetes_screenings', 'is_zero_glucose')) {
                $table->dropColumn('is_zero_glucose');
            }
            
            if (Schema::hasColumn('diabetes_screenings', 'hypertension_classification')) {
                $table->dropColumn('hypertension_classification');
            }
        });
        
        // Revert blood_glucose_level menggunakan raw SQL
        try {
            DB::statement('ALTER TABLE diabetes_screenings MODIFY COLUMN blood_glucose_level DECIMAL(8,2) NOT NULL');
        } catch (\Exception $e) {
            DB::statement('ALTER TABLE diabetes_screenings CHANGE blood_glucose_level blood_glucose_level DECIMAL(8,2) NOT NULL');
        }
    }
};