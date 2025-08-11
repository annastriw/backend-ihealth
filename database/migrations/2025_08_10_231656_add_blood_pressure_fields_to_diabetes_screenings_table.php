<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diabetes_screenings', function (Blueprint $table) {
    $table->integer('sistolic_pressure')->nullable();
    $table->integer('diastolic_pressure')->nullable();
    $table->string('hypertension_classification')->nullable();
            // Hapus kolom lama jika tidak diperlukan
            // $table->dropColumn('high_blood_pressure'); // Uncomment jika ingin hapus
        });
    }

    public function down(): void
    {
        Schema::table('diabetes_screenings', function (Blueprint $table) {
            $table->dropColumn(['sistolic_pressure', 'diastolic_pressure', 'hypertension_classification']);
            // $table->string('high_blood_pressure')->nullable(); // Uncomment jika ingin restore
        });
    }
};