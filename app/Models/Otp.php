<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Otp extends Model
{
    use HasFactory;

    // Primary key non-incrementing UUID
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'id',
        'email',
        'otp_hash',
        'type',
        'attempts',
        'resend_count',
        'expires_at',
        'is_used',
        'is_expired',
    ];

    // Casting tipe data otomatis
    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
        'is_expired' => 'boolean',
        'attempts' => 'integer',
        'resend_count' => 'integer',
    ];

    // Gunakan timestamps
    public $timestamps = true;
}
