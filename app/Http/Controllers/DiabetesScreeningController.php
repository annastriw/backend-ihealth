<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiabetesScreening;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiabetesScreeningController extends Controller
{
    private $mlApiUrl = 'https://tcnisaa-prediksi-dm-adaboost.hf.space/predict';

    public function screeningDiabetes(Request $request)
    {
        Log::info('=== SCREENING DIABETES START ===');
        Log::info('Received screening data', ['data' => $request->all()]);

        try {
            // ✅ DEBUG: Log received data first
            Log::info('Raw request data', [
                'all_data' => $request->all(),
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type')
            ]);

            // ✅ FIX: Simplified validation dengan better error messages
            $validated = $request->validate([
                'patient_id' => 'required|string',
                'gender' => 'required|integer|in:0,1',
                'age' => 'required|integer|min:1|max:120',
                'heart_disease' => 'required|integer|in:0,1',
                'smoking_history' => 'required|string',
                'bmi' => 'required|numeric|min:10|max:50',
                'hypertension' => 'required|integer|in:0,1',
                'sistolic_pressure' => 'required|integer|min:60|max:250',
                'diastolic_pressure' => 'required|integer|min:40|max:150',
                'blood_glucose_level' => 'required|numeric|min:0|max:400'
            ], [
                // Custom error messages untuk debugging
                'patient_id.required' => 'Patient ID is required',
                'gender.required' => 'Gender is required',
                'gender.in' => 'Gender must be 0 or 1',
                'age.required' => 'Age is required',
                'age.min' => 'Age must be at least 1',
                'age.max' => 'Age must not exceed 120',
                'heart_disease.required' => 'Heart disease field is required',
                'heart_disease.in' => 'Heart disease must be 0 or 1',
                'smoking_history.required' => 'Smoking history is required',
                'bmi.required' => 'BMI is required',
                'bmi.min' => 'BMI must be at least 10',
                'bmi.max' => 'BMI must not exceed 50',
                'hypertension.required' => 'Hypertension field is required',
                'hypertension.in' => 'Hypertension must be 0 or 1',
                'sistolic_pressure.required' => 'Sistolic pressure is required',
                'sistolic_pressure.min' => 'Sistolic pressure must be at least 60',
                'sistolic_pressure.max' => 'Sistolic pressure must not exceed 250',
                'diastolic_pressure.required' => 'Diastolic pressure is required',
                'diastolic_pressure.min' => 'Diastolic pressure must be at least 40',
                'diastolic_pressure.max' => 'Diastolic pressure must not exceed 150',
                'blood_glucose_level.required' => 'Blood glucose level is required',
                'blood_glucose_level.min' => 'Blood glucose level must be at least 0',
                'blood_glucose_level.max' => 'Blood glucose level must not exceed 400',
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            // ✅ FIX: Detect zero glucose
            $isZeroGlucose = $validated['blood_glucose_level'] == 0;
            Log::info('Zero glucose check', ['is_zero_glucose' => $isZeroGlucose]);

            // ✅ FIX: Ambil nama pasien yang benar
            $patient = DB::table('personal_information')
                ->where('user_id', $validated['patient_id'])
                ->first();
            
            if (!$patient) {
                $patient = DB::table('users')
                    ->where('id', $validated['patient_id'])
                    ->first();
            }

            $patientName = $patient ? $patient->name : 'Pasien Tidak Diketahui';

            Log::info('Patient data retrieved', [
                'patient_id' => $validated['patient_id'],
                'patient_name' => $patientName
            ]);

            // ✅ FIX: Klasifikasi hipertensi berdasarkan sistolic/diastolic yang sebenarnya
            $hypertensionClass = $this->classifyHypertension(
                $validated['sistolic_pressure'], 
                $validated['diastolic_pressure']
            );
            Log::info('Hypertension classification', ['classification' => $hypertensionClass]);

            // ============================================
            // KONDISIONAL PREDICTION BERDASARKAN GULA DARAH
            // ============================================
            
            if ($isZeroGlucose) {
                // ZERO GLUCOSE CASE - Gunakan prediksi terbatas
                Log::info('🟡 Zero glucose detected - using limited prediction');
                
                $prediction = $this->getZeroGlucosePrediction($validated, $hypertensionClass);
                
            } else {
                // NORMAL CASE - Gunakan ML API atau fallback
                Log::info('✅ Normal glucose detected - proceeding with ML prediction');
                
                $mlData = $this->prepareMLData($validated);
                Log::info('ML data prepared', ['ml_data' => $mlData]);
                
                $prediction = $this->predictDiabetes($mlData);
                Log::info('ML API response received', ['prediction' => $prediction]);
            }

            // ============================================
            // SAVE TO DATABASE
            // ============================================
            $screeningId = null;
            try {
                Log::info('Saving to database...');

                $screeningData = [
                    'user_id' => $validated['patient_id'],
                    'age' => $validated['age'],
                    'gender' => $validated['gender'] == 1 ? 'Perempuan' : 'Laki-laki',
                    'bmi' => $validated['bmi'],
                    'smoking_history' => $this->transformSmokingHistory($validated['smoking_history']),
                    'high_blood_pressure' => $validated['hypertension'] == 1 ? 'Tinggi' : 'Normal',
                    'sistolic_pressure' => $validated['sistolic_pressure'], // ✅ TAMBAH KEMBALI
                    'diastolic_pressure' => $validated['diastolic_pressure'], // ✅ TAMBAH KEMBALI
                    'blood_glucose_level' => $isZeroGlucose ? null : $validated['blood_glucose_level'], // ✅ FIX: Null jika zero
                    'is_zero_glucose' => $isZeroGlucose, // ✅ FIX: Field baru
                    'hypertension_classification' => $hypertensionClass, // ✅ FIX: Field baru
                    'prediction_result' => $this->getPredictionResultFromResponse($prediction, $isZeroGlucose),
                    'prediction_score' => $this->getPredictionScoreFromResponse($prediction, $isZeroGlucose),
                    'recommendation' => $this->getRecommendationFromResponse($prediction, $isZeroGlucose),
                    'screening_date' => now(),
                    'ml_response' => json_encode($prediction),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                Log::info('Data to save', ['screening_data' => $screeningData]);

                $screening = DiabetesScreening::create($screeningData);
                $screeningId = $screening->id;

                Log::info('✅ Database save successful!', ['screening_id' => $screeningId]);

            } catch (\Exception $dbError) {
                Log::error('❌ Database save error!', [
                    'error' => $dbError->getMessage(),
                    'trace' => $dbError->getTraceAsString()
                ]);
                // Continue execution even if DB save fails
            }

            // ============================================
            // RESPONSE FORMAT YANG KONSISTEN
            // ============================================
            $responseData = [
                'id' => $screeningId,
                'screening_id' => $screeningId,
                'patient_id' => $validated['patient_id'],
                'patient_name' => $patientName,
                'age' => $validated['age'],
                'bmi' => $validated['bmi'],
                'sistolic_pressure' => $validated['sistolic_pressure'], // ✅ TAMBAH KEMBALI
                'diastolic_pressure' => $validated['diastolic_pressure'], // ✅ TAMBAH KEMBALI
                'blood_pressure' => $validated['sistolic_pressure'] . '/' . $validated['diastolic_pressure'], // ✅ TAMBAH KEMBALI
                'blood_glucose_level' => $isZeroGlucose ? null : $validated['blood_glucose_level'], // ✅ FIX: Konsisten null
                'hypertension_classification' => $hypertensionClass,
                'prediction' => $this->getPredictionBinaryFromResponse($prediction, $isZeroGlucose),
                'probability' => $this->getPredictionProbabilityFromResponse($prediction, $isZeroGlucose),
                'risk_level' => $this->getPredictionResultFromResponse($prediction, $isZeroGlucose),
                'risk_score' => $this->getPredictionScoreFromResponse($prediction, $isZeroGlucose),
                'recommendation' => $this->getRecommendationFromResponse($prediction, $isZeroGlucose),
                'is_zero_glucose' => $isZeroGlucose, // ✅ FIX: Flag yang konsisten
                'ml_response' => $prediction
            ];

            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'message' => $isZeroGlucose ? 
                        'Screening selesai dengan data terbatas (tanpa gula darah)' : 
                        'Screening berhasil dilakukan',
                    'statusCode' => 200
                ],
                'data' => $responseData
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'statusCode' => 422
                ]
            ], 422);

        } catch (\Exception $e) {
            Log::error('General error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Error during screening: ' . $e->getMessage(),
                    'statusCode' => 500
                ]
            ], 500);
        }
    }

    // ============================================
    // HELPER METHODS YANG DIPERBAIKI
    // ============================================

    /**
     * ✅ FIX: Prepare ML data dengan sistolic/diastolic
     */
    private function prepareMLData($validated)
    {
        return [
            'gender' => (int) $validated['gender'], 
            'age' => (int) $validated['age'],
            'hypertension' => (int) $validated['hypertension'],
            'heart_disease' => (int) $validated['heart_disease'],
            'smoking_history' => $validated['smoking_history'],
            'bmi' => (float) $validated['bmi'],
            'systolic_bp' => (float) $validated['sistolic_pressure'], // ✅ TAMBAH KEMBALI
            'diastolic_bp' => (float) $validated['diastolic_pressure'], // ✅ TAMBAH KEMBALI
            'blood_glucose_level' => (float) $validated['blood_glucose_level']
        ];
    }

    /**
     * ✅ FIX: Klasifikasi hipertensi berdasarkan sistolic/diastolic yang sebenarnya
     */
    private function classifyHypertension($systolic, $diastolic)
    {
        // Optimal
        if ($systolic < 120 && $diastolic < 80) {
            return "Optimal";
        }
        // Normal
        elseif ($systolic <= 129 && $diastolic <= 84) {
            return "Normal";
        }
        // Normal Tinggi (Pra Hipertensi)
        elseif ($systolic <= 139 && $diastolic <= 89) {
            return "Normal Tinggi (Pra Hipertensi)";
        }
        // Hipertensi Derajat 1
        elseif ($systolic <= 159 && $diastolic <= 99) {
            return "Hipertensi Derajat 1";
        }
        // Hipertensi Derajat 2
        elseif ($systolic <= 179 && $diastolic <= 109) {
            return "Hipertensi Derajat 2";
        }
        // Hipertensi Derajat 3
        elseif ($systolic >= 180 || $diastolic >= 110) {
            return "Hipertensi Derajat 3";
        }
        // Hipertensi Sistolik Terisolasi
        elseif ($systolic >= 140 && $diastolic < 90) {
            return "Hipertensi Sistolik Terisolasi";
        }
        else {
            return "Tidak dapat diklasifikasikan";
        }
    }

    /**
     * ✅ FIX: Prediksi untuk zero glucose case
     */
    private function getZeroGlucosePrediction($validated, $hypertensionClass)
    {
        // Hitung risk score berdasarkan faktor yang tersedia (tanpa gula darah)
        $risk_score = 0;
        $factors = [];

        // Age factor
        if ($validated['age'] >= 60) {
            $risk_score += 25;
            $factors[] = 'usia ≥60 tahun';
        } elseif ($validated['age'] >= 45) {
            $risk_score += 15;
            $factors[] = 'usia 45-59 tahun';
        }
        
        // BMI factor
        if ($validated['bmi'] >= 30) {
            $risk_score += 20;
            $factors[] = 'obesitas (BMI ≥30)';
        } elseif ($validated['bmi'] >= 25) {
            $risk_score += 10;
            $factors[] = 'overweight (BMI 25-29.9)';
        }
        
        // Hypertension factor
        if ($validated['hypertension'] == 1) {
            $risk_score += 15;
            $factors[] = 'hipertensi';
        }
        
        // Heart disease factor
        if ($validated['heart_disease'] == 1) {
            $risk_score += 20;
            $factors[] = 'penyakit jantung';
        }
        
        // Smoking factor
        if (in_array($validated['smoking_history'], ['perokok aktif'])) {
            $risk_score += 15;
            $factors[] = 'perokok aktif';
        } elseif (in_array($validated['smoking_history'], ['mantan perokok'])) {
            $risk_score += 10;
            $factors[] = 'mantan perokok';
        }
        
        // Gender factor
        if ($validated['gender'] == 1 && $validated['age'] >= 45) {
            $risk_score += 5;
        }
        
        // Batasi score maksimal
        $risk_score = min($risk_score, 100);
        
        return [
            'type' => 'zero_glucose',
            'risk_score' => $risk_score,
            'factors' => $factors,
            'classification' => 'Tidak Dapat Ditentukan',
            'message' => 'Data gula darah tidak tersedia'
        ];
    }

    /**
     * ✅ FIX: Get prediction result yang konsisten
     */
    private function getPredictionResultFromResponse($prediction, $isZeroGlucose)
    {
        if ($isZeroGlucose) {
            return 'Tidak Dapat Ditentukan';
        }

        // Handle different prediction formats
        if (isset($prediction['prediction'])) {
            return $prediction['prediction'] == 1 ? 'Tinggi' : 'Rendah';
        }

        if (isset($prediction['risk_score'])) {
            $score = $prediction['risk_score'];
            if ($score >= 60) return 'Tinggi';
            if ($score >= 35) return 'Sedang';
            return 'Rendah';
        }

        return 'Rendah';
    }

    /**
     * ✅ FIX: Get prediction score yang konsisten
     */
    private function getPredictionScoreFromResponse($prediction, $isZeroGlucose)
    {
        if ($isZeroGlucose) {
            return isset($prediction['risk_score']) ? $prediction['risk_score'] : 0;
        }

        if (isset($prediction['probability'])) {
            return $prediction['probability'] * 100;
        }

        if (isset($prediction['risk_score'])) {
            return $prediction['risk_score'];
        }

        return 0;
    }

    /**
     * ✅ FIX: Get binary prediction
     */
    private function getPredictionBinaryFromResponse($prediction, $isZeroGlucose)
    {
        if ($isZeroGlucose) {
            return 0; // Default untuk zero glucose
        }

        if (isset($prediction['prediction'])) {
            return $prediction['prediction'];
        }

        $score = $this->getPredictionScoreFromResponse($prediction, $isZeroGlucose);
        return $score >= 50 ? 1 : 0;
    }

    /**
     * ✅ FIX: Get probability
     */
    private function getPredictionProbabilityFromResponse($prediction, $isZeroGlucose)
    {
        if ($isZeroGlucose) {
            return 0;
        }

        if (isset($prediction['probability'])) {
            return $prediction['probability'];
        }

        $score = $this->getPredictionScoreFromResponse($prediction, $isZeroGlucose);
        return $score / 100;
    }

    /**
     * ✅ FIX: Get recommendation yang konsisten
     */
    private function getRecommendationFromResponse($prediction, $isZeroGlucose)
    {
        if ($isZeroGlucose) {
            return 'Hasil screening menunjukkan risiko Hipertensi. Disarankan untuk segera konsultasi dengan dokter untuk pemeriksaan lebih lanjut dan mulai menerapkan pola hidup sehat.';
        } else {
            return 'Hasil screening menunjukkan risiko Hipertensi rendah. Tetap pertahankan pola hidup sehat dengan diet seimbang dan olahraga teratur.';
        }

        $binary = $this->getPredictionBinaryFromResponse($prediction, $isZeroGlucose);
        
        if ($binary == 1) {
            return 'Hasil screening menunjukkan risiko diabetes. Disarankan untuk segera konsultasi dengan dokter untuk pemeriksaan lebih lanjut dan mulai menerapkan pola hidup sehat.';
        } else {
            return 'Hasil screening menunjukkan risiko diabetes rendah. Tetap pertahankan pola hidup sehat dengan diet seimbang dan olahraga teratur.';
        }
    }

    // ============================================
    // ML API CALL - DENGAN FALLBACK YANG ROBUST
    // ============================================
    private function predictDiabetes($data)
    {
        try {
            Log::info('🚀 Calling ML API...', [
                'url' => $this->mlApiUrl,
                'data' => $data
            ]);

            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => false  // Fix SSL issue untuk development
                ])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->mlApiUrl, $data);

            Log::info('📡 ML API Status', ['status' => $response->status()]);
            Log::info('📡 ML API Raw Response', ['response' => $response->body()]);

            if (!$response->successful()) {
                throw new \Exception('ML API error: ' . $response->status() . ' - ' . $response->body());
            }

            $result = $response->json();
            Log::info('✅ ML API Success', ['parsed_result' => $result]);

            return $result;

        } catch (\Exception $e) {
            Log::error('❌ ML API Failed', [
                'error' => $e->getMessage(),
                'url' => $this->mlApiUrl,
                'data_sent' => $data
            ]);

            // ✅ FIX: Fallback prediction yang lebih simple dan reliable
            return $this->getFallbackPrediction($data);
        }
    }

    /**
     * ✅ FIX: Fallback prediction yang simple dan reliable
     */
    private function getFallbackPrediction($data)
    {
        Log::info('Using fallback prediction logic');
        
        $risk_score = 0;
        $factors = [];
        
        // Age factor
        if ($data['age'] >= 60) {
            $risk_score += 25;
            $factors[] = 'usia ≥60 tahun';
        } elseif ($data['age'] >= 45) {
            $risk_score += 15;
            $factors[] = 'usia 45-59 tahun';
        }
        
        // BMI factor
        if ($data['bmi'] >= 30) {
            $risk_score += 20;
            $factors[] = 'obesitas (BMI ≥30)';
        } elseif ($data['bmi'] >= 25) {
            $risk_score += 10;
            $factors[] = 'overweight (BMI 25-29.9)';
        }
        
        // Blood glucose level - KRITERIA YANG PALING PENTING
        if ($data['blood_glucose_level'] >= 200) {
            $risk_score += 50; // Sangat tinggi
            $factors[] = 'gula darah sangat tinggi (≥200 mg/dL)';
        } elseif ($data['blood_glucose_level'] >= 140) {
            $risk_score += 30; // Tinggi
            $factors[] = 'gula darah tinggi (140-199 mg/dL)';
        } elseif ($data['blood_glucose_level'] >= 100) {
            $risk_score += 10; // Pre-diabetes
            $factors[] = 'gula darah borderline (100-139 mg/dL)';
        }
        
        // Hypertension factor
        if ($data['hypertension'] == 1) {
            $risk_score += 15;
            $factors[] = 'hipertensi';
        }
        
        // Heart disease factor
        if ($data['heart_disease'] == 1) {
            $risk_score += 20;
            $factors[] = 'penyakit jantung';
        }
        
        // Smoking factor
        if (in_array($data['smoking_history'], ['perokok aktif'])) {
            $risk_score += 15;
            $factors[] = 'perokok aktif';
        } elseif (in_array($data['smoking_history'], ['mantan perokok'])) {
            $risk_score += 10;
            $factors[] = 'mantan perokok';
        }
        
        // Gender factor
        if ($data['gender'] == 1 && $data['age'] >= 45) {
            $risk_score += 5;
        }
        
        // Batasi score maksimal
        $risk_score = min($risk_score, 100);
        
        // Tentukan hasil berdasarkan score
        $prediction = $risk_score >= 50 ? 1 : 0;
        $probability = $risk_score / 100;
        
        Log::info('Fallback prediction calculated', [
            'risk_score' => $risk_score,
            'prediction' => $prediction,
            'probability' => $probability,
            'factors' => $factors
        ]);
        
        return [
            'prediction' => $prediction,
            'probability' => $probability,
            'risk_score' => $risk_score,
            'factors' => $factors,
            'fallback_used' => true
        ];
    }

    // ============================================
    // UTILITY METHODS
    // ============================================

    private function transformSmokingHistory($smokingHistory)
    {
        switch($smokingHistory) {
            case 'tidak pernah merokok':
                return 'Tidak Pernah Merokok';
            case 'mantan perokok':
                return 'Mantan Perokok';
            case 'perokok aktif':
                return 'Perokok Aktif';
            case 'tidak ada informasi':
                return 'Tidak Ada Informasi';
            default:
                return 'Tidak Pernah Merokok';
        }
    }

    // ============================================
    // EXISTING METHODS (Keep unchanged)
    // ============================================

    public function getDiabetesHistory(Request $request)
    {
        $user = auth()->user();

        $query = DiabetesScreening::with('user')->where('user_id', $user->id);

        if ($request->has('risk') && $request->risk != '') {
            $query->where('prediction_result', 'like', '%' . $request->risk . '%');
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('screening_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('screening_date', '<=', $request->date_to);
        }

        $screenings = $query->orderBy('screening_date', 'desc')->paginate(15);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Diabetes history fetched successfully',
                'statusCode' => 200
            ],
            'data' => $screenings->items(),
            'pagination' => [
                'current_page' => $screenings->currentPage(),
                'total_pages' => $screenings->lastPage(),
                'total_items' => $screenings->total()
            ]
        ]);
    }

    public function getDiabetesDetail($id)
    {
        $screening = DiabetesScreening::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$screening) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Screening data not found',
                    'statusCode' => 404
                ]
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Diabetes screening detail fetched successfully',
                'statusCode' => 200
            ],
            'data' => $screening
        ]);
    }

    public function deleteDiabetesScreening($id)
    {
        $deleted = DiabetesScreening::where('user_id', auth()->id())
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Screening data not found',
                    'statusCode' => 404
                ]
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Diabetes screening deleted successfully',
                'statusCode' => 200
            ]
        ]);
    }

    // Other methods remain unchanged...
}