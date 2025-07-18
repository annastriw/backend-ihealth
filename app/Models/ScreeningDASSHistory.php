<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ScreeningDASSHistory extends Model
{
    use HasFactory;

    protected $table = 'screening_dass_histories';

    protected $fillable = ['id', 'user_id', 'answers'];

    protected $casts = [
        'answers' => 'array',
    ];

    // ➕ Konfigurasi UUID
    public $incrementing = false;
    protected $keyType = 'string';

    // ➕ Generate UUID saat create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
