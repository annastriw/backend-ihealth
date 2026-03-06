<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CholesterolMeasurement extends Model
{
    protected $fillable = [
        'cholesterol_level',
        'source',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];
}