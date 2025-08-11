<?php

// ========================================
// app/Models/DiabetesScreening.php - UPDATED
// Dengan field baru untuk sistol/diastol dan klasifikasi hipertensi
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiabetesScreening extends Model
{
    use HasFactory;

    protected $table = 'diabetes_screenings';

    // ========================================
    // FILLABLE - UPDATED dengan field baru
    // ========================================
    protected $fillable = [
        'user_id',
        'age',
        'name',
        'gender',
        'bmi',
        'smoking_history',
        'high_blood_pressure',
        'sistolic_pressure',           // TAMBAH INI
        'diastolic_pressure',          // TAMBAH INI  
        'hypertension_classification', // TAMBAH INI
        'blood_glucose_level',
        'prediction_result',
        'prediction_score',
        'recommendation',
        'screening_date',
        'ml_response',
    ];

    // ========================================
    // CASTS - UPDATED dengan field baru
    // ========================================
    protected $casts = [
        'age' => 'integer',
        'bmi' => 'decimal:2',
        'sistolic_pressure' => 'integer',    // TAMBAH INI
        'diastolic_pressure' => 'integer',   // TAMBAH INI
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
    // ACCESSORS - UPDATED dengan accessor baru
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
     * TAMBAH: Format blood pressure sistol/diastol
     */
    public function getFormattedBloodPressureAttribute()
    {
        if ($this->sistolic_pressure && $this->diastolic_pressure) {
            return $this->sistolic_pressure . '/' . $this->diastolic_pressure . ' mmHg';
        }
        return null;
    }

    /**
     * TAMBAH: Blood pressure color berdasarkan klasifikasi
     */
    public function getBloodPressureColorAttribute()
    {
        $classification = strtolower($this->hypertension_classification ?? '');
        
        if (str_contains($classification, 'optimal') || str_contains($classification, 'normal')) {
            return 'success';
        } elseif (str_contains($classification, 'pra hipertensi') || str_contains($classification, 'tinggi')) {
            return 'warning';
        } elseif (str_contains($classification, 'hipertensi')) {
            return 'danger';
        }
        
        return 'secondary';
    }

    /**
     * TAMBAH: Blood pressure icon berdasarkan klasifikasi
     */
    public function getBloodPressureIconAttribute()
    {
        $classification = strtolower($this->hypertension_classification ?? '');
        
        if (str_contains($classification, 'optimal')) {
            return 'fas fa-check-circle';
        } elseif (str_contains($classification, 'normal')) {
            return 'fas fa-check';
        } elseif (str_contains($classification, 'pra hipertensi') || str_contains($classification, 'tinggi')) {
            return 'fas fa-exclamation-triangle';
        } elseif (str_contains($classification, 'hipertensi')) {
            return 'fas fa-exclamation-circle';
        }
        
        return 'fas fa-heartbeat';
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
     * UPDATED: Hypertension status dengan data baru
     */
    public function getHypertensionStatusAttribute()
    {
        // Prioritaskan klasifikasi baru jika ada
        if ($this->hypertension_classification) {
            $classification = strtolower($this->hypertension_classification);
            
            if (str_contains($classification, 'optimal')) {
                return [
                    'text' => 'Optimal',
                    'icon' => 'fas fa-check-circle',
                    'color' => 'success'
                ];
            } elseif (str_contains($classification, 'normal') && !str_contains($classification, 'tinggi')) {
                return [
                    'text' => 'Normal',
                    'icon' => 'fas fa-check',
                    'color' => 'success'
                ];
            } elseif (str_contains($classification, 'pra hipertensi') || str_contains($classification, 'tinggi')) {
                return [
                    'text' => 'Pra Hipertensi',
                    'icon' => 'fas fa-exclamation-triangle',
                    'color' => 'warning'
                ];
            } elseif (str_contains($classification, 'derajat 1')) {
                return [
                    'text' => 'Hipertensi Derajat 1',
                    'icon' => 'fas fa-exclamation-circle',
                    'color' => 'danger'
                ];
            } elseif (str_contains($classification, 'derajat 2')) {
                return [
                    'text' => 'Hipertensi Derajat 2',
                    'icon' => 'fas fa-exclamation-circle',
                    'color' => 'danger'
                ];
            } elseif (str_contains($classification, 'derajat 3')) {
                return [
                    'text' => 'Hipertensi Derajat 3',
                    'icon' => 'fas fa-times-circle',
                    'color' => 'danger'
                ];
            } elseif (str_contains($classification, 'sistolik terisolasi')) {
                return [
                    'text' => 'Hipertensi Sistolik',
                    'icon' => 'fas fa-arrow-up',
                    'color' => 'danger'
                ];
            }
        }
        
        // Fallback ke data lama jika tidak ada klasifikasi baru
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
     * TAMBAH: Scope untuk filter berdasarkan klasifikasi hipertensi
     */
    public function scopeWithHypertensionClass($query, $classification)
    {
        return $query->where('hypertension_classification', $classification);
    }

    /**
     * TAMBAH: Scope untuk filter high blood pressure (sistolik >= 140 atau diastolik >= 90)
     */
    public function scopeHighBloodPressure($query)
    {
        return $query->where(function($q) {
            $q->where('sistolic_pressure', '>=', 140)
              ->orWhere('diastolic_pressure', '>=', 90);
        });
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
    // STATIC METHODS - UPDATED dengan method baru
    // ========================================
    
    /**
     * TAMBAH: Klasifikasi hipertensi berdasarkan sistol dan diastol
     */
    public static function classifyHypertension($sistolic, $diastolic)
    {
        if ($sistolic < 120 && $diastolic < 80) {
            return "Optimal";
        } elseif ($sistolic >= 120 && $sistolic <= 129 && $diastolic >= 80 && $diastolic <= 84) {
            return "Normal";
        } elseif ($sistolic >= 130 && $sistolic <= 139 && $diastolic >= 85 && $diastolic <= 89) {
            return "Normal Tinggi (Pra Hipertensi)";
        } elseif ($sistolic >= 140 && $sistolic <= 159 && $diastolic >= 90 && $diastolic <= 99) {
            return "Hipertensi Derajat 1";
        } elseif ($sistolic >= 160 && $sistolic <= 179 && $diastolic >= 100 && $diastolic <= 109) {
            return "Hipertensi Derajat 2";
        } elseif ($sistolic >= 180 && $diastolic >= 110) {
            return "Hipertensi Derajat 3";
        } elseif ($sistolic >= 140 && $diastolic < 90) {
            return "Hipertensi Sistolik Terisolasi";
        }
        return "Normal";
    }

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
     * TAMBAH: Get available hypertension classifications
     */
    public static function getHypertensionClassifications()
    {
        return [
            'Optimal' => 'success',
            'Normal' => 'success',
            'Normal Tinggi (Pra Hipertensi)' => 'warning',
            'Hipertensi Derajat 1' => 'danger',
            'Hipertensi Derajat 2' => 'danger',
            'Hipertensi Derajat 3' => 'danger',
            'Hipertensi Sistolik Terisolasi' => 'danger'
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
     * UPDATED: Get statistics for user dengan data hipertensi
     */
    public static function getStatsForUser($userId)
    {
        $screenings = self::forUser($userId)->get();
        
        return [
            'total' => $screenings->count(),
            'high_risk' => $screenings->where('prediction_result', 'Tinggi')->count(),
            'medium_risk' => $screenings->where('prediction_result', 'Sedang')->count(),
            'low_risk' => $screenings->where('prediction_result', 'Rendah')->count(),
            'high_bp' => $screenings->filter(function($s) {
                return $s->sistolic_pressure >= 140 || $s->diastolic_pressure >= 90;
            })->count(),
            'optimal_bp' => $screenings->where('hypertension_classification', 'Optimal')->count(),
            'latest' => $screenings->sortByDesc('screening_date')->first(),
            'average_score' => $screenings->avg('prediction_score'),
            'average_sistolic' => $screenings->avg('sistolic_pressure'),
            'average_diastolic' => $screenings->avg('diastolic_pressure'),
        ];
    }

    /**
     * TAMBAH: Get blood pressure statistics
     */
    public static function getBloodPressureStats()
    {
        $total = self::whereNotNull('sistolic_pressure')->whereNotNull('diastolic_pressure')->count();
        
        return [
            'total' => $total,
            'optimal' => self::where('hypertension_classification', 'Optimal')->count(),
            'normal' => self::where('hypertension_classification', 'Normal')->count(),
            'prehypertension' => self::where('hypertension_classification', 'Normal Tinggi (Pra Hipertensi)')->count(),
            'stage1' => self::where('hypertension_classification', 'Hipertensi Derajat 1')->count(),
            'stage2' => self::where('hypertension_classification', 'Hipertensi Derajat 2')->count(),
            'stage3' => self::where('hypertension_classification', 'Hipertensi Derajat 3')->count(),
            'isolated' => self::where('hypertension_classification', 'Hipertensi Sistolik Terisolasi')->count(),
        ];
    }
}