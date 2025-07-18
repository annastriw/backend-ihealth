<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('screening_dass_histories', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID sebagai primary key
            $table->uuid('user_id');
            $table->json('answers');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_dass_histories');
    }
};
