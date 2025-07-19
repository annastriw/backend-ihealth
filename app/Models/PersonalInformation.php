<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalInformation extends Model
{
    protected $table = 'personal_information';
    
    protected $fillable = [
        'user_id',
        'name',
        'age',
        'gender',
        'phone',
        'email',
        'place_of_birth',
        'date_of_birth',
        'hypertension',
        'heart_disease',
        'smoking_history',
        'bmi'
    ];

    protected $casts = [
        'age' => 'integer',
        'gender' => 'integer',
        'hypertension' => 'integer',
        'heart_disease' => 'integer',
        'bmi' => 'float',
        'date_of_birth' => 'date'
    ];

    // Scope untuk pencarian
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('user_id', 'LIKE', "%{$term}%");
        });
    }
}