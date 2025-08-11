<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discussion_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('discussion_id');
            $table->foreignUuid('user_id');
            $table->foreignUuid('medical_id')->nullable();
            $table->text('comment');
            $table->string('image_path')->nullable();
            $table->string('is_private')->default('false');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_comments');
    }
};
