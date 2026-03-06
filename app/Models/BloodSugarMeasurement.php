<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloodSugarMeasurement extends Model
{
    protected $fillable = [
        'random_blood_sugar',
        'source',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];
}