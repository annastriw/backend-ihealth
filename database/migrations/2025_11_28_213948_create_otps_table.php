<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('email')->index(); // Email user
            $table->string('otp_hash');       // Hash OTP
            $table->enum('type', ['register', 'forgot'])->default('register');

            $table->unsignedTinyInteger('attempts')->default(0);      // Percobaan salah
            $table->unsignedTinyInteger('resend_count')->default(0);  // Jumlah resend
            $table->boolean('is_used')->default(false);               // Sudah digunakan atau belum
            $table->boolean('is_expired')->default(false);            // Flag kadaluarsa manual
            $table->timestamp('expires_at')->nullable();              // Waktu kadaluarsa sebenarnya

            $table->timestamps();
            $table->index(['email', 'type', 'is_used', 'is_expired']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};

