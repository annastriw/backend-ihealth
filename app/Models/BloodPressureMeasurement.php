<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloodPressureMeasurement extends Model
{
    protected $fillable = [
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'source',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];
}