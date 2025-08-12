<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id'); // UUID dari tabel users
            $table->json('answers'); // Jawaban review (Likert scale)
            $table->text('suggestion')->nullable(); // Saran opsional
            $table->timestamps();

            // Foreign key ke tabel users
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_reviews');
    }
};
