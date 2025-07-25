<?php

// ========================================
// app/Models/DiabetesScreening.php - FIXED
// Sesuai dengan migration yang sudah ada
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiabetesScreening extends Model
{
    use HasFactory;

    protected $table = 'diabetes_screenings';

    // ========================================
    // FILLABLE - Sesuai dengan migration fields
    // ========================================
    protected $fillable = [
        'user_id',
        'age',
        'name',
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

    // ========================================
    // CASTS - Type casting untuk fields
    // ========================================
    protected $casts = [
        'age' => 'integer',
        'bmi' => 'decimal:2',
        'blood_glucose_level' => 'decimal:2',
        'prediction_score' => 'decimal:2',
        'ml_response' => 'array',
        'screening_date' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    /**
     * Relasi ke tabel users
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ========================================
    // ACCESSORS - Format data untuk display
    // ========================================
    
    /**
     * Format tanggal screening
     */
    public function getFormattedDateAttribute()
    {
        return $this->screening_date ? $this->screening_date->format('d M Y, H:i') : null;
    }

    /**
     * Format tanggal screening (short)
     */
    public function getShortDateAttribute()
    {
        return $this->screening_date ? $this->screening_date->format('d/m/Y') : null;
    }

    /**
     * Badge warna berdasarkan level risiko
     */
    public function getRiskColorAttribute()
    {
        $level = strtolower($this->prediction_result ?? '');
        return match ($level) {
            'tinggi' => 'danger',
            'sedang' => 'warning',
            'rendah' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Icon berdasarkan level risiko
     */
    public function getRiskIconAttribute()
    {
        $level = strtolower($this->prediction_result ?? '');
        return match ($level) {
            'tinggi' => 'fas fa-exclamation-triangle',
            'sedang' => 'fas fa-exclamation-circle',
            'rendah' => 'fas fa-check-circle',
            default => 'fas fa-info-circle',
        };
    }

    /**
     * Risk level untuk compatibility dengan kode lama
     */
    public function getRiskLevelAttribute()
    {
        return $this->prediction_result;
    }

    /**
     * Diabetes prediction untuk compatibility dengan kode lama
     */
    public function getDiabetesPredictionAttribute()
    {
        $level = strtolower($this->prediction_result ?? '');
        return match ($level) {
            'tinggi' => 1,
            'sedang' => 1,
            'rendah' => 0,
            default => 0,
        };
    }

    /**
     * Prediction probability untuk compatibility dengan kode lama
     */
    public function getPredictionProbabilityAttribute()
    {
        return $this->prediction_score ? ($this->prediction_score / 100) : 0;
    }

    /**
     * Predicted at untuk compatibility dengan kode lama
     */
    public function getPredictedAtAttribute()
    {
        return $this->screening_date;
    }

    /**
     * Format BMI dengan 1 decimal
     */
    public function getFormattedBmiAttribute()
    {
        return $this->bmi ? number_format($this->bmi, 1) : null;
    }

    /**
     * Format prediction score sebagai percentage
     */
    public function getFormattedScoreAttribute()
    {
        return $this->prediction_score ? number_format($this->prediction_score, 1) . '%' : '0%';
    }

    /**
     * Format blood glucose level
     */
    public function getFormattedGlucoseAttribute()
    {
        return $this->blood_glucose_level ? number_format($this->blood_glucose_level, 0) . ' mg/dL' : null;
    }

    /**
     * Gender icon
     */
    public function getGenderIconAttribute()
    {
        $gender = strtolower($this->gender ?? '');
        return match ($gender) {
            'laki-laki' => 'fas fa-mars',
            'perempuan' => 'fas fa-venus',
            default => 'fas fa-user',
        };
    }

    /**
     * Gender color
     */
    public function getGenderColorAttribute()
    {
        $gender = strtolower($this->gender ?? '');
        return match ($gender) {
            'laki-laki' => 'primary',
            'perempuan' => 'pink',
            default => 'secondary',
        };
    }

    /**
     * Hypertension status dengan icon
     */
    public function getHypertensionStatusAttribute()
    {
        $status = strtolower($this->high_blood_pressure ?? '');
        return match ($status) {
            'tinggi' => [
                'text' => 'Tinggi',
                'icon' => 'fas fa-arrow-up',
                'color' => 'danger'
            ],
            'rendah' => [
                'text' => 'Normal',
                'icon' => 'fas fa-check',
                'color' => 'success'
            ],
            default => [
                'text' => 'Unknown',
                'icon' => 'fas fa-question',
                'color' => 'secondary'
            ]
        };
    }

    /**
     * Smoking status dengan icon
     */
    public function getSmokingStatusAttribute()
    {
        $status = strtolower($this->smoking_history ?? '');
        
        if (str_contains($status, 'tidak pernah')) {
            return [
                'text' => 'Tidak Pernah',
                'icon' => 'fas fa-check-circle',
                'color' => 'success'
            ];
        } elseif (str_contains($status, 'mantan')) {
            return [
                'text' => 'Mantan Perokok',
                'icon' => 'fas fa-history',
                'color' => 'warning'
            ];
        } elseif (str_contains($status, 'aktif')) {
            return [
                'text' => 'Perokok Aktif',
                'icon' => 'fas fa-smoking',
                'color' => 'danger'
            ];
        } else {
            return [
                'text' => 'Tidak Ada Info',
                'icon' => 'fas fa-question',
                'color' => 'secondary'
            ];
        }
    }

    // ========================================
    // SCOPES - Query helpers
    // ========================================
    
    /**
     * Scope untuk filter berdasarkan user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter berdasarkan risk level
     */
    public function scopeWithRiskLevel($query, $riskLevel)
    {
        return $query->where('prediction_result', $riskLevel);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('screening_date', [$startDate, $endDate]);
    }

    /**
     * Scope untuk data terbaru
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('screening_date', 'desc');
    }

    /**
     * Scope untuk high risk cases
     */
    public function scopeHighRisk($query)
    {
        return $query->where('prediction_result', 'Tinggi');
    }

    /**
     * Scope untuk recent screenings (30 hari terakhir)
     */
    public function scopeRecent($query)
    {
        return $query->where('screening_date', '>=', now()->subDays(30));
    }

    // ========================================
    // STATIC METHODS - Helper functions
    // ========================================
    
    /**
     * Get available risk levels
     */
    public static function getRiskLevels()
    {
        return [
            'Rendah' => 'success',
            'Sedang' => 'warning', 
            'Tinggi' => 'danger'
        ];
    }

    /**
     * Get available genders
     */
    public static function getGenders()
    {
        return [
            'Laki-laki' => 'primary',
            'Perempuan' => 'pink'
        ];
    }

    /**
     * Get smoking history options
     */
    public static function getSmokingOptions()
    {
        return [
            'Tidak Pernah Merokok' => 'success',
            'Mantan Perokok' => 'warning',
            'Perokok Aktif' => 'danger',
            'Tidak Ada Informasi' => 'secondary'
        ];
    }

    /**
     * Get statistics for user
     */
    public static function getStatsForUser($userId)
    {
        $screenings = self::forUser($userId)->get();
        
        return [
            'total' => $screenings->count(),
            'high_risk' => $screenings->where('prediction_result', 'Tinggi')->count(),
            'medium_risk' => $screenings->where('prediction_result', 'Sedang')->count(),
            'low_risk' => $screenings->where('prediction_result', 'Rendah')->count(),
            'latest' => $screenings->sortByDesc('screening_date')->first(),
            'average_score' => $screenings->avg('prediction_score'),
        ];
    }
}