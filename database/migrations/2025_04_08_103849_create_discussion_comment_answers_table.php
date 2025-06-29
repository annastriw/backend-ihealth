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
        Schema::create('discussion_comment_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('discussion_comment_id');
            $table->foreignUuid('user_id');
            $table->text('comment');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_comment_answers');
    }
};
