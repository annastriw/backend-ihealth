<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientHealthCheck extends Model
{
    use HasFactory;

    protected $table = 'patient_health_check';
    public $incrementing = false; // UUID sebagai primary key
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'personal_information_id',
        'name',
        'age',
        'gender',
        'check_date',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'hypertension',
        'random_blood_sugar',
        'diabetes',
        'cholesterol_level',
        'height',
        'weight',
        'bmi',
        'obesity',
        'waist_circumference',
        'family_history',
        'smoking_status',
        'physical_activity',
        'dietary_habits',
        'stress_level',
        'sleep_hours',
        'previous_heart_disease',
        'medication_usage',
    ];

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class, 'personal_information_id', 'id');
    }
}
