<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_answer_screening_scorings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi ke user
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');

            // Relasi ke riwayat screening user (nama constraint manual agar tidak terlalu panjang)
            $table->uuid('user_history_screening_scoring_id');
            $table->foreign('user_history_screening_scoring_id', 'fk_uasc_uhss')
                ->references('id')
                ->on('user_history_screening_scorings')
                ->onDelete('cascade');

            // Relasi ke soal
            $table->foreignUuid('question_id')->constrained()->onDelete('cascade');

            // Relasi ke opsi jawaban (bisa nullable jika isian bebas)
            $table->foreignUuid('selected_option_id')->nullable();
            $table->foreign('selected_option_id', 'fk_uasc_opt')
                ->references('id')
                ->on('options')
                ->onDelete('cascade');

            // Jawaban isian bebas (jika bukan pilihan ganda)
            $table->text('answer_text')->nullable();

            // Waktu dijawab
            $table->timestamp('answered_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_answer_screening_scorings');
    }
};
