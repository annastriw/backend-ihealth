<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HeartAttackRiskPred extends Model
{
    use HasFactory;

    protected $table = 'heart_attack_risk_pred';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'patient_health_check_id',
        'personal_information_id',
        'pred_value',
        'not_risk',
        'risk',
        'analysis_text',
        'factor_text',
    ];

    protected $casts = [
        'pred_value' => 'integer',
        'not_risk' => 'float',
        'risk' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function healthCheck()
    {
        return $this->belongsTo(PatientHealthCheck::class, 'patient_health_check_id', 'id');
    }

    public function personalInformation()
    {
        return $this->belongsTo(PersonalInformation::class, 'personal_information_id', 'id');
    }
}