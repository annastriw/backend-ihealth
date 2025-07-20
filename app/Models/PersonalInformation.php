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
        'age',
        'gender',
        'phone',
        'email',
        'place_of_birth',
        'date_of_birth',
        'hypertension',
        'heart_disease',
        'smoking_history',
        'heart_disease_history',
        'bmi',
        'hba1c_level',
        'blood_glucose_level',
        'diabetes_prediction',
        'prediction_probability',
        'risk_level',
        'ml_response',
        'predicted_at'
    ];

    protected $casts = [
        'age' => 'integer',
        'gender' => 'string',
        'is_married' => 'boolean',
        'hypertension' => 'integer',
        'heart_disease' => 'integer',
        'heart_disease_history' => 'integer',
        'smoking_history' => 'string',
        'bmi' => 'float',
        'hba1c_level' => 'float',
        'blood_glucose_level' => 'float',
        'diabetes_prediction' => 'integer',
        'prediction_probability' => 'float',
        'ml_response' => 'array',
        'date_of_birth' => 'date',
        'predicted_at' => 'datetime'
    ];

    // Scope untuk pencarian
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('user_id', 'LIKE', "%{$term}%");
        });
    }

    // Accessor untuk status diabetes yang lebih readable
    public function getDiabetesStatusAttribute()
    {
        if ($this->diabetes_prediction === null) {
            return 'Belum diprediksi';
        }
        
        return $this->diabetes_prediction == 1 ? 'Berisiko Diabetes' : 'Tidak Berisiko';
    }

    // Accessor untuk risk level dengan warna
    public function getRiskLevelColorAttribute()
    {
        switch ($this->risk_level) {
            case 'Tinggi':
                return 'danger';
            case 'Sedang':
                return 'warning';
            case 'Rendah':
                return 'success';
            default:
                return 'secondary';
        }
    }

    
}