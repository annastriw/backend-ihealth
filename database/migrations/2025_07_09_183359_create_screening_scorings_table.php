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
        Schema::create('screening_scorings', function (Blueprint $table) {
            $table->uuid('id')->primary(); // ID tipe UUID
            $table->foreignUuid('question_set_id') // relasi ke bank soal
                ->constrained()
                ->onDelete('cascade');
            $table->string('name'); // nama screening scoring
            $table->enum('type', ['HT', 'DM']); // tipe hanya HT atau DM
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_scorings');
    }
};
