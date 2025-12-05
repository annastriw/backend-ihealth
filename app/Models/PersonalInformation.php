<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PersonalInformation extends Model
{
    use HasFactory;

    protected $table = 'personal_information';

    /**
     * ✅ WAJIB UNTUK UUID PRIMARY KEY
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * ✅ FIELD YANG BOLEH DI-INSERT
     */
    protected $fillable = [
        'id', // ✅ WAJIB ADA UNTUK UUID
        'user_id',
        'name',
        'place_of_birth',
        'date_of_birth',
        'age',
        'gender',
        'work',
        'is_married',
        'last_education',
        'origin_disease',
        'disease_duration',
        'history_therapy',
    ];

    /**
     * ✅ CASTING AGAR SESUAI TIPE DATABASE
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'is_married' => 'boolean',
    ];

    /**
     * ✅ AUTO GENERATE UUID SAAT CREATE (ANTI 500 PALING PENTING)
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * ✅ RELASI KE USERS
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
