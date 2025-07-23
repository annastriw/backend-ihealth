<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiabetesScreening extends Model
{
    protected $table = 'diabetes_screenings';

    protected $fillable = [
        'user_id',
        'age',
        'gender',
        'bmi',
        'smoking_history',
        'high_blood_pressure',
        'blood_glucose_level',
        'prediction_result',
        'prediction_score',
        'recommendation',
        'screening_date',
        'ml_response',
    ];

    protected $casts = [
        'screening_date' => 'datetime',
        'ml_response' => 'array',
    ];
}
