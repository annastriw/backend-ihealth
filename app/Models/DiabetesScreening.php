<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiabetesScreening extends Model
{
    use HasFactory;
     protected $fillable = [
        'user_id',
        'hypertension',
        'blood_glucose_level',
        'diabetes_prediction',
        'prediction_probability',
        'risk_level',
        'ml_response',
        'predicted_at'
    ];

    protected $casts = [
        'ml_response' => 'array',
        'predicted_at' => 'datetime'
    ];
}
