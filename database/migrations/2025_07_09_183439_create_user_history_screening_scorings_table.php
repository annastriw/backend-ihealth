<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_history_screening_scorings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi ke user
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');

            // Relasi ke screening scoring
            $table->foreignUuid('screening_scoring_id')->constrained()->onDelete('cascade');

            // Nilai akhir dari hasil pengerjaan
            $table->integer('sum_score')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_history_screening_scorings');
    }
};
