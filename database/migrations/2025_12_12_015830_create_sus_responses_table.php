<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sus_responses', function (Blueprint $table) {
            $table->char('id', 36)->primary(); // UUID
            $table->char('user_id', 36);
            $table->float('total_score')->nullable();
            $table->string('interpretation')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sus_responses');
    }
};
