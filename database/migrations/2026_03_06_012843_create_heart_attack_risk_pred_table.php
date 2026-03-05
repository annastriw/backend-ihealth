<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heart_attack_risk_pred', function (Blueprint $table) {
            $table->char('id', 36)->primary(); // UUID PK

            // FK wajib: jika patient_health_check dihapus -> prediksi ikut terhapus
            $table->char('patient_health_check_id', 36);
            $table->foreign('patient_health_check_id')
                ->references('id')->on('patient_health_check')
                ->onDelete('cascade');

            // personal_information_id juga disimpan (sesuai kebutuhan)
            $table->char('personal_information_id', 36);
            $table->foreign('personal_information_id')
                ->references('id')->on('personal_information')
                ->onDelete('cascade');

            // hasil prediksi
            $table->integer('pred_value');              // 0/1 (atau sesuai dari Flask)
            $table->decimal('not_risk', 6, 4);          // contoh 0.8900
            $table->decimal('risk', 6, 4);              // contoh 0.1100

            // text dari Flask (boleh disimpan JSON string atau plain text)
            $table->longText('analysis_text');
            $table->longText('factor_text');

            $table->timestamps(); // created_at & updated_at (created_at wajib ada)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heart_attack_risk_pred');
    }
};