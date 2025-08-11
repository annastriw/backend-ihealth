<?php

// ========================================
// app/Models/DiabetesScreening.php - UPDATED
// Dengan field baru untuk sistol/diastol, klasifikasi hipertensi, dan zero glucose support
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiabetesScreening extends Model
{
    use HasFactory;

    protected $table = 'diabetes_screenings';

    // ========================================
    // FILLABLE - UPDATED dengan field baru termasuk is_zero_glucose
    // ========================================
    protected $fillable = [
        'user_id',
        'age',
        'name',
        'gender',
        'bmi',
        'smoking_history',
        'high_blood_pressure',
        'sistolic_pressure',           // SUDAH ADA
        'diastolic_pressure',          // SUDAH ADA
        'hypertension_classification', // SUDAH ADA
        'blood_glucose_level',
        'prediction_result',
        'prediction_score',
        'recommendation',
        'screening_date',
        'ml_response',
        'is_zero_glucose',            // ✅ TAMBAH BARU - Support zero glucose
    ];

    // ========================================
    // CASTS - UPDATED dengan field baru termasuk is_zero_glucose
    // ========================================
    protected $casts = [
        'age' => 'integer',
        'bmi' => 'decimal:2',
        'sistolic_pressure' => 'integer',
        'diastolic_pressure' => 'integer',
        'blood_glucose_level' => 'decimal:2',
        'prediction_score' => 'decimal:2',
        'ml_response' => 'array',
        'screening_date' => 'datetime',
        'is_zero_glucose' => 'boolean',   // ✅ TAMBAH BARU - Cast sebagai boolean
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
    // ACCESSORS - UPDATED dengan accessor baru dan zero glucose support
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
     * Badge warna berdasarkan level risiko - UPDATED dengan zero glucose support
     */
    public function getRiskColorAttribute()
    {
        // Handle zero glucose case
        if ($this->is_zero_glucose) {
            return 'warning';  // Kuning untuk data tidak lengkap
        }
        
        $level = strtolower($this->prediction_result ?? '');
        
        switch ($level) {
            case 'tinggi':
                return 'danger';
            case 'sedang':
                return 'warning';
            case 'rendah':
                return 'success';
            case 'tidak dapat ditentukan':
                return 'secondary';
            default:
                return 'secondary';
        }
    }

    /**
     * Icon berdasarkan level risiko - UPDATED dengan zero glucose support
     */
    public function getRiskIconAttribute()
    {
        // Handle zero glucose case
        if ($this->is_zero_glucose) {
            return 'fas fa-exclamation-triangle';
        }
        
        $level = strtolower($this->prediction_result ?? '');
        
        switch ($level) {
            case 'tinggi':
                return 'fas fa-exclamation-triangle';
            case 'sedang':
                return 'fas fa-exclamation-circle';
            case 'rendah':
                return 'fas fa-check-circle';
            case 'tidak dapat ditentukan':
                return 'fas fa-question-circle';
            default:
                return 'fas fa-info-circle';
        }
    }

    /**
     * Risk level untuk compatibility dengan kode lama
     */
    public function getRiskLevelAttribute()
    {
        return $this->prediction_result;
    }

    /**
     * Diabetes prediction untuk compatibility dengan kode lama - UPDATED dengan zero glucose
     */
    public function getDiabetesPredictionAttribute()
    {
        // Return 0 untuk zero glucose case
        if ($this->is_zero_glucose) {
            return 0;
        }
        
        $level = strtolower($this->prediction_result ?? '');
        
        switch ($level) {
            case 'tinggi':
            case 'sedang':
                return 1;
            case 'rendah':
            default:
                return 0;
        }
    }

    /**
     * Prediction probability untuk compatibility dengan kode lama - UPDATED dengan zero glucose
     */
    public function getPredictionProbabilityAttribute()
    {
        // Return 0 untuk zero glucose case
        if ($this->is_zero_glucose) {
            return 0;
        }
        
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
     * Format prediction score sebagai percentage - UPDATED dengan zero glucose support
     */
    public function getFormattedScoreAttribute()
    {
        // Return dash untuk zero glucose case
        if ($this->is_zero_glucose) {
            return '-';
        }
        
        return $this->prediction_score ? number_format($this->prediction_score, 1) . '%' : '0%';
    }

    /**
     * Format blood glucose level - UPDATED dengan zero glucose support
     */
    public function getFormattedGlucoseAttribute()
    {
        // Return dash untuk zero glucose case
        if ($this->is_zero_glucose || $this->blood_glucose_level === null) {
            return '-';
        }
        
        return $this->blood_glucose_level ? number_format($this->blood_glucose_level, 0) . ' mg/dL' : null;
    }

    /**
     * Format blood pressure sistol/diastol
     */
    public function getFormattedBloodPressureAttribute()
    {
        if ($this->sistolic_pressure && $this->diastolic_pressure) {
            return $this->sistolic_pressure . '/' . $this->diastolic_pressure . ' mmHg';
        }
        return null;
    }

    /**
     * Blood pressure color berdasarkan klasifikasi
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
     * Blood pressure icon berdasarkan klasifikasi
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
        
        switch ($gender) {
            case 'laki-laki':
                return 'fas fa-mars';
            case 'perempuan':
                return 'fas fa-venus';
            default:
                return 'fas fa-user';
        }
    }

    /**
     * Gender color
     */
    public function getGenderColorAttribute()
    {
        $gender = strtolower($this->gender ?? '');
        
        switch ($gender) {
            case 'laki-laki':
                return 'primary';
            case 'perempuan':
                return 'pink';
            default:
                return 'secondary';
        }
    }

    /**
     * Hypertension status dengan data baru
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
        
        // ✅ DIPERBAIKI: Fallback ke data lama dengan switch
        $status = strtolower($this->high_blood_pressure ?? '');
        
        switch ($status) {
            case 'tinggi':
                return [
                    'text' => 'Tinggi',
                    'icon' => 'fas fa-arrow-up',
                    'color' => 'danger'
                ];
            case 'rendah':
                return [
                    'text' => 'Normal',
                    'icon' => 'fas fa-check',
                    'color' => 'success'
                ];
            default:
                return [
                    'text' => 'Unknown',
                    'icon' => 'fas fa-question',
                    'color' => 'secondary'
                ];
        }
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

    /**
     * ✅ TAMBAH BARU: Zero glucose status
     */
    public function getZeroGlucoseStatusAttribute()
    {
        if ($this->is_zero_glucose) {
            return [
                'text' => 'Data Gula Darah Tidak Tersedia',
                'icon' => 'fas fa-exclamation-triangle',
                'color' => 'warning',
                'badge' => 'Data Parsial'
            ];
        }
        
        return [
            'text' => 'Data Lengkap',
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
            'badge' => 'Data Lengkap'
        ];
    }

    /**
     * ✅ TAMBAH BARU: Screening completeness percentage
     */
    public function getCompletenessPercentageAttribute()
    {
        $totalFields = 6; // Total field yang bisa diisi
        $filledFields = 0;
        
        // Field yang selalu ada
        if ($this->age) $filledFields++;
        if ($this->bmi) $filledFields++;
        if ($this->sistolic_pressure && $this->diastolic_pressure) $filledFields++;
        if ($this->smoking_history) $filledFields++;
        if ($this->prediction_result) $filledFields++;
        
        // Field gula darah (hanya count jika bukan zero glucose)
        if (!$this->is_zero_glucose && $this->blood_glucose_level) {
            $filledFields++;
        } elseif ($this->is_zero_glucose) {
            // Jika zero glucose, kurangi total field
            $totalFields = 5;
        }
        
        return round(($filledFields / $totalFields) * 100);
    }

    // ========================================
    // SCOPES - Query helpers termasuk zero glucose
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
     * Scope untuk filter berdasarkan klasifikasi hipertensi
     */
    public function scopeWithHypertensionClass($query, $classification)
    {
        return $query->where('hypertension_classification', $classification);
    }

    /**
     * Scope untuk filter high blood pressure (sistolik >= 140 atau diastolik >= 90)
     */
    public function scopeHighBloodPressure($query)
    {
        return $query->where(function($q) {
            $q->where('sistolic_pressure', '>=', 140)
              ->orWhere('diastolic_pressure', '>=', 90);
        });
    }

    /**
     * ✅ TAMBAH BARU: Scope untuk zero glucose cases
     */
    public function scopeZeroGlucose($query)
    {
        return $query->where('is_zero_glucose', true);
    }

    /**
     * ✅ TAMBAH BARU: Scope untuk normal glucose cases
     */
    public function scopeNormalGlucose($query)
    {
        return $query->where('is_zero_glucose', false);
    }

    /**
     * ✅ TAMBAH BARU: Scope untuk data lengkap
     */
    public function scopeCompleteData($query)
    {
        return $query->where('is_zero_glucose', false)
                    ->whereNotNull('blood_glucose_level')
                    ->whereNotNull('sistolic_pressure')
                    ->whereNotNull('diastolic_pressure');
    }

    /**
     * ✅ TAMBAH BARU: Scope untuk data parsial
     */
    public function scopePartialData($query)
    {
        return $query->where('is_zero_glucose', true);
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
    // STATIC METHODS - UPDATED dengan method baru dan zero glucose support
    // ========================================
    
    /**
     * Klasifikasi hipertensi berdasarkan sistol dan diastol - SESUAI FLASK API
     */
    public static function classifyHypertension($sistolic, $diastolic)
    {
        // Optimal
        if ($sistolic < 120 && $diastolic < 80) {
            return "Optimal";
        }
        // Normal
        elseif ($sistolic <= 129 && $diastolic <= 84) {
            return "Normal";
        }
        // Normal Tinggi (Pra Hipertensi)
        elseif ($sistolic <= 139 && $diastolic <= 89) {
            return "Normal Tinggi (Pra Hipertensi)";
        }
        // Hipertensi Derajat 1
        elseif ($sistolic <= 159 && $diastolic <= 99) {
            return "Hipertensi Derajat 1";
        }
        // Hipertensi Derajat 2
        elseif ($sistolic <= 179 && $diastolic <= 109) {
            return "Hipertensi Derajat 2";
        }
        // Hipertensi Derajat 3
        elseif ($sistolic >= 180 || $diastolic >= 110) {
            return "Hipertensi Derajat 3";
        }
        // Hipertensi Sistolik Terisolasi
        elseif ($sistolic >= 140 && $diastolic < 90) {
            return "Hipertensi Sistolik Terisolasi";
        }
        else {
            return "Tidak dapat diklasifikasikan";
        }
    }

    /**
     * Get available risk levels - UPDATED dengan zero glucose
     */
    public static function getRiskLevels()
    {
        return [
            'Rendah' => 'success',
            'Sedang' => 'warning', 
            'Tinggi' => 'danger',
            'Tidak Dapat Ditentukan' => 'secondary' // ✅ TAMBAH untuk zero glucose
        ];
    }

    /**
     * Get available hypertension classifications
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
     * UPDATED: Get statistics for user dengan data hipertensi dan zero glucose
     */
    public static function getStatsForUser($userId)
    {
        $screenings = self::forUser($userId)->get();
        
        return [
            'total' => $screenings->count(),
            'high_risk' => $screenings->where('prediction_result', 'Tinggi')->count(),
            'medium_risk' => $screenings->where('prediction_result', 'Sedang')->count(),
            'low_risk' => $screenings->where('prediction_result', 'Rendah')->count(),
            'undetermined' => $screenings->where('prediction_result', 'Tidak Dapat Ditentukan')->count(), // ✅ TAMBAH
            'zero_glucose' => $screenings->where('is_zero_glucose', true)->count(), // ✅ TAMBAH
            'complete_data' => $screenings->where('is_zero_glucose', false)->count(), // ✅ TAMBAH
            'high_bp' => $screenings->filter(function($s) {
                return $s->sistolic_pressure >= 140 || $s->diastolic_pressure >= 90;
            })->count(),
            'optimal_bp' => $screenings->where('hypertension_classification', 'Optimal')->count(),
            'latest' => $screenings->sortByDesc('screening_date')->first(),
            'average_score' => $screenings->where('is_zero_glucose', false)->avg('prediction_score'), // ✅ EXCLUDE zero glucose
            'average_sistolic' => $screenings->avg('sistolic_pressure'),
            'average_diastolic' => $screenings->avg('diastolic_pressure'),
            'data_completeness' => $screenings->avg('completeness_percentage'), // ✅ TAMBAH
        ];
    }

    /**
     * UPDATED: Get blood pressure statistics dengan zero glucose awareness
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

    /**
     * ✅ TAMBAH BARU: Get zero glucose statistics
     */
    public static function getZeroGlucoseStats()
    {
        $total = self::count();
        $zeroGlucose = self::where('is_zero_glucose', true)->count();
        $completeData = self::where('is_zero_glucose', false)->count();
        
        return [
            'total' => $total,
            'zero_glucose_count' => $zeroGlucose,
            'complete_data_count' => $completeData,
            'zero_glucose_percentage' => $total > 0 ? round(($zeroGlucose / $total) * 100, 1) : 0,
            'complete_data_percentage' => $total > 0 ? round(($completeData / $total) * 100, 1) : 0,
        ];
    }

    /**
     * ✅ TAMBAH BARU: Check if screening has complete diabetes prediction data
     */
    public function hasCompleteDiabetesData()
    {
        return !$this->is_zero_glucose && 
               $this->blood_glucose_level !== null && 
               $this->prediction_result !== 'Tidak Dapat Ditentukan';
    }

    /**
     * ✅ TAMBAH BARU: Check if screening has hypertension
     */
    public function hasHypertension()
    {
        $hypertensionConditions = [
            "Hipertensi Derajat 1", 
            "Hipertensi Derajat 2", 
            "Hipertensi Derajat 3", 
            "Hipertensi Sistolik Terisolasi"
        ];
        
        return in_array($this->hypertension_classification, $hypertensionConditions);
    }

    /**
     * ✅ TAMBAH BARU: Get formatted blood pressure classification
     */
    public function getFormattedBloodPressureClassification()
    {
        return $this->hypertension_classification ?? 'Tidak Terklasifikasi';
    }
}