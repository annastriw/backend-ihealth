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
        Schema::create('kms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sub_module_id');
            $table->string('name');
            $table->string('video_url');
            $table->string('file_path');
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kms');
    }
};
