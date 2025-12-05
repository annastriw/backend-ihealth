<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalInformation extends Model
{
    use HasFactory;

    protected $table = 'personal_information';

    protected $fillable = [
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

    protected $casts = [
        'date_of_birth' => 'date',
        'is_married' => 'boolean',
    ];

    /**
     * Relasi ke tabel users
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
