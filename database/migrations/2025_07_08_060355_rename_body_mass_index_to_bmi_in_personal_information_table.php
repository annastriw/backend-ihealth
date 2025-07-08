<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom baru "bmi"
        Schema::table('personal_information', function (Blueprint $table) {
            $table->string('bmi')->nullable()->after('smoking_history');
        });

        // 2. Salin data dari kolom lama ke kolom baru
        DB::statement('UPDATE personal_information SET bmi = body_mass_index');

        // 3. Hapus kolom lama
        Schema::table('personal_information', function (Blueprint $table) {
            $table->dropColumn('body_mass_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Tambahkan kembali kolom "body_mass_index"
        Schema::table('personal_information', function (Blueprint $table) {
            $table->string('body_mass_index')->nullable()->after('smoking_history');
        });

        // 2. Salin data dari kolom baru ke kolom lama
        DB::statement('UPDATE personal_information SET body_mass_index = bmi');

        // 3. Hapus kolom "bmi"
        Schema::table('personal_information', function (Blueprint $table) {
            $table->dropColumn('bmi');
        });
    }
};
