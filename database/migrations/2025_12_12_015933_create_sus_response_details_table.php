<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sus_response_details', function (Blueprint $table) {
            $table->char('id', 36)->primary(); // UUID
            $table->char('sus_response_id', 36);
            $table->integer('question_id'); // 1-10
            $table->integer('answer_raw'); // 1-5
            $table->integer('answer_converted')->nullable(); // 0-4
            $table->timestamps();

            $table->foreign('sus_response_id')
                ->references('id')->on('sus_responses')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sus_response_details');
    }
};
