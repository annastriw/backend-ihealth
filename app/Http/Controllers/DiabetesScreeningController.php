<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiabetesScreening;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\User; // Import User model

class DiabetesScreeningController extends Controller
{
    private $mlApiUrl = 'https://tcnisaa-prediksi-dm-adaboost.hf.space/predict';

    public function screeningDiabetes(Request $request)
    {
        Log::info('=== SCREENING DIABETES START ===');
        Log::info('Received screening data', ['data' => $request->all()]);

        try {
            $validated = $request->validate([
                'patient_id' => 'required|string|exists:users,id', // FIX: Added exists validation to ensure patient_id is a valid user ID
                'gender' => 'required|in:0,1',
                'age' => 'required|integer|min:1|max:120',
                'heart_disease' => 'required|in:0,1',
                'smoking_history' => 'required|string|in:perokok aktif,mantan perokok,tidak pernah merokok,tidak ada informasi,pernah merokok (riwayat tidak jelas),tidak merokok saat ini',
                'bmi' => 'required|numeric|min:10|max:50',
                'hypertension' => 'required|in:0,1',
                'blood_glucose_level' => 'required|numeric|min:50|max:400'
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            // ✅ TAMBAHAN BARU: Ambil nama pasien dari database
$patient = \Illuminate\Support\Facades\DB::table('personal_information')
    ->where('user_id', $validated['patient_id'])
    ->orWhere('id', $validated['patient_id'])
    ->first();

$patientName = $patient ? $patient->name : 'Pasien Tidak Diketahui';

Log::info('Patient data retrieved', [
    'patient_id' => $validated['patient_id'],
    'patient_name' => $patientName
]);

            // ============================================
            // FORMAT DATA UNTUK ML API (STRING FORMAT)
            // ============================================
            $mlData = [
                'gender' => $validated['gender'] == 1 ? 'Perempuan' : 'Laki-laki',
                'age' => (int) $validated['age'],
                'hypertension' => $validated['hypertension'] == 1 ? 'Tinggi' : 'Normal',
                'heart_disease' => $validated['heart_disease'] == 1 ? 'Ya' : 'Tidak',
                'smoking_history' => $validated['smoking_history'],
                'bmi' => (float) $validated['bmi'],
                'blood_glucose_level' => (float) $validated['blood_glucose_level']
            ];

            Log::info('ML data prepared (STRING format)', ['ml_data' => $mlData]);

            // Kirim ke ML API
            $prediction = $this->predictDiabetes($mlData);
            Log::info('ML API response received', ['prediction' => $prediction]);

            // ============================================
            // SAVE TO DATABASE - Sesuai struktur migration
            // ============================================
            $screeningId = null;
            try {
                Log::info('Saving to database...');

                $screeningData = [
                    'user_id' => $validated['patient_id'], // FIX: Use patient_id from the request as the user_id
                    'name' => $patientName,
                    'age' => $validated['age'],
                    'gender' => $validated['gender'] == 1 ? 'Perempuan' : 'Laki-laki',
                    'bmi' => $validated['bmi'],
                    'smoking_history' => $this->transformSmokingHistory($validated['smoking_history']),
                    'high_blood_pressure' => $validated['hypertension'] == 1 ? 'Tinggi' : 'Rendah',
                    'blood_glucose_level' => $validated['blood_glucose_level'],
                    'prediction_result' => $this->getPredictionResult($prediction),
                    'prediction_score' => $this->getPredictionScore($prediction),
                    'recommendation' => $this->generateRecommendation($prediction),
                    'screening_date' => now(),
                    'ml_response' => $prediction,
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
            // RESPONSE
            // ============================================
            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'message' => 'Screening completed' . ($screeningId ? ' and saved to database' : ''),
                    'statusCode' => 200
                ],
                'data' => [
                    'id' => $screeningId,
                    'screening_id' => $screeningId,
                    'patient_id' => $validated['patient_id'],
                    'patient_name' => $patientName,
                    'prediction' => $this->getPredictionBinary($prediction),
                    'probability' => $this->getPredictionProbability($prediction),
                    'risk_level' => $this->getRiskLevel($prediction),
                    'risk_score' => $this->getPredictionScore($prediction),
                    'recommendation' => $this->generateRecommendation($prediction),
                    'ml_response' => $prediction,
                    'debug_ml_data_sent' => $mlData,  // untuk debug
                ]
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
    // ML API CALL - DENGAN SSL FIX DAN FALLBACK LOGIC
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

            // FALLBACK LOGIC YANG LEBIH BAIK
            return $this->getFallbackPrediction($data);
        }
    }

    // ============================================
    // FALLBACK PREDICTION - LOGIC YANG LEBIH REALISTIS
    // ============================================
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
        if ($data['hypertension'] == 'Tinggi') {
            $risk_score += 15;
            $factors[] = 'hipertensi';
        }

        // Heart disease factor
        if ($data['heart_disease'] == 'Ya') {
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

        // Gender factor (diabetes lebih umum pada perempuan di usia tertentu)
        if ($data['gender'] == 'Perempuan' && $data['age'] >= 45) {
            $risk_score += 5;
        }

        // Batasi score maksimal
        $risk_score = min($risk_score, 100);

        // Tentukan hasil berdasarkan score
        if ($risk_score >= 60) {
            $result = "Anda memiliki risiko Diabetes. Silakan konsultasi ke dokter.";
            $probability = min(85, $risk_score + 5);
        } elseif ($risk_score >= 35) {
            $result = "Risiko Anda Sedang. Disarankan pemeriksaan lanjutan.";
            $probability = min(65, $risk_score);
        } else {
            $result = "Anda Tidak Berisiko Diabetes.";
            $probability = min(35, $risk_score + 5);
        }

        Log::info('Fallback prediction calculated', [
            'risk_score' => $risk_score,
            'probability' => $probability,
            'factors' => $factors,
            'result' => $result
        ]);

        return [
            'probabilitas' => $probability . '%',
            'hasil' => $result,
            'error' => 'ML API unavailable - using fallback logic',
            'risk_factors' => $factors
        ];
    }

    // ============================================
    // HELPER METHODS - Handle different ML response formats
    // ============================================

    private function getPredictionResult($prediction)
    {
        // Handle ML API response format
        if (isset($prediction['hasil'])) {
            $hasil = $prediction['hasil'];
            if (strpos($hasil, 'risiko Diabetes') !== false) {
                return 'Tinggi';
            } elseif (strpos($hasil, 'Risiko Anda Sedang') !== false) {
                return 'Sedang';
            } else {
                return 'Rendah';
            }
        }

        // Fallback berdasarkan probability
        $probability = $this->getPredictionScore($prediction);
        if ($probability >= 60) return 'Tinggi';
        if ($probability >= 35) return 'Sedang';
        return 'Rendah';
    }

    private function getPredictionScore($prediction)
    {
        // Handle ML API response format
        if (isset($prediction['probabilitas'])) {
            $prob = $prediction['probabilitas'];
            // Remove % sign if present and convert to float
            return floatval(str_replace('%', '', $prob));
        }

        // Handle fallback format
        if (isset($prediction['probability'])) {
            return $prediction['probability'] * 100;
        }

        return 10.0; // default low score
    }

    private function getPredictionBinary($prediction)
    {
        $score = $this->getPredictionScore($prediction);
        return $score >= 50 ? 1 : 0;
    }

    private function getPredictionProbability($prediction)
    {
        $score = $this->getPredictionScore($prediction);
        return $score / 100; // convert percentage to decimal
    }

    private function transformSmokingHistory($smokingHistory)
    {
        return match($smokingHistory) {
            'tidak pernah merokok' => 'Tidak Pernah Merokok',
            'mantan perokok' => 'Mantan Perokok',
            'perokok aktif' => 'Perokok Aktif',
            'tidak ada informasi' => 'Tidak Ada Informasi',
            'pernah merokok (riwayat tidak jelas)' => 'Pernah Merokok (Riwayat Tidak Jelas)',
            'tidak merokok saat ini' => 'Tidak Merokok Saat Ini',
            default => 'Tidak Pernah Merokok'
        };
    }

    private function getRiskLevel($prediction)
    {
        $score = $this->getPredictionScore($prediction);

        if ($score >= 60) return 'Tinggi';
        if ($score >= 35) return 'Sedang';
        return 'Rendah';
    }

    private function generateRecommendation($prediction)
    {
        $score = $this->getPredictionScore($prediction);

        if ($score >= 60) {
            return 'Hasil screening menunjukkan risiko diabetes tinggi. Sangat disarankan untuk segera konsultasi dengan dokter untuk pemeriksaan lebih lanjut dan mulai menerapkan pola hidup sehat secara ketat.';
        } elseif ($score >= 35) {
            return 'Hasil screening menunjukkan risiko diabetes sedang. Disarankan untuk berkonsultasi dengan dokter dan mulai memperhatikan pola hidup sehat seperti diet rendah gula dan olahraga teratur.';
        } else {
            return 'Hasil screening menunjukkan risiko diabetes rendah. Tetap pertahankan pola hidup sehat dengan diet seimbang dan olahraga teratur.';
        }
    }

    // ============================================
    // OTHER METHODS (History, Detail, etc.)
    // ============================================

    public function getDiabetesHistory(Request $request)
    {
        $user = auth()->user();

       $query = DiabetesScreening::with('user')->where('user_id', $user->id); // FIX: Eager load user

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

        // FIX: Format each screening item for the frontend
        $formattedScreenings = $screenings->getCollection()->map(function ($screening) {
            return $this->formatScreeningForFrontend($screening);
        });
        
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

    public function getDiabetesChartData()
    {
        $user = auth()->user();

        $screenings = DiabetesScreening::where('user_id', $user->id)
            ->where('screening_date', '>=', Carbon::now()->subDays(30))
            ->orderBy('screening_date')
            ->get();

        $dates = [];
        $scores = [];

        foreach ($screenings as $screening) {
            $dates[] = Carbon::parse($screening->screening_date)->format('d M');
            $scores[] = $screening->prediction_score ?? 0;
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Chart data fetched successfully',
                'statusCode' => 200
            ],
            'data' => [
                'dates' => $dates,
                'scores' => $scores
            ]
        ]);
    }

    // ADMIN METHODS
    public function getAllDiabetesHistory()
    {
        $screenings = DiabetesScreening::with('user')
            ->orderBy('screening_date', 'desc')
            ->paginate(20);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'All diabetes history fetched successfully',
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

    public function adminDeleteDiabetesScreening($id)
    {
        $deleted = DiabetesScreening::where('id', $id)->delete();

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
    // ========================================
    // 🆕 METHODS BARU UNTUK NEXT.JS FRONTEND
    // ========================================

    /**
     * Display a listing of diabetes screenings untuk Next.js frontend
     * GET /api/diabetes-screenings
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
{
    try {
        $query = DiabetesScreening::with('user') // Tambahkan eager loading relasi 'user'
            ->orderBy('screening_date', 'desc');

        // Jika ada user yang login, prioritaskan data user tersebut
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        }

        $screenings = $query->limit(50)->get()->map(function ($screening) {
            return $this->formatScreeningForFrontend($screening);
        });

        return response()->json($screenings);
    } catch (\Exception $e) {
        Log::error('Diabetes screening index error: ' . $e->getMessage());
        return response()->json([]);
    }
}
    
    /**
     * Display specific screening untuk Next.js frontend
     * GET /api/diabetes-screenings/{id}
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $screening = DiabetesScreening::with('user')->find($id); // FIX: Eager load user

            if (!$screening) {
                return response()->json([
                    'error' => 'Screening not found'
                ], 404);
            }

            return response()->json($this->formatScreeningForFrontend($screening));
        } catch (\Exception $e) {
            Log::error('Diabetes screening show error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve screening',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get screenings by user untuk Next.js frontend
     * GET /api/diabetes-screenings/user/{user_id}
     */
    public function getByUser(int $user_id): \Illuminate\Http\JsonResponse
    {
        try {
            $screenings = DiabetesScreening::with('user') // FIX: Eager load user
                ->where('user_id', $user_id)
                ->orderBy('screening_date', 'desc')
                ->get()
                ->map(function ($screening) {
                    return $this->formatScreeningForFrontend($screening);
                });

            return response()->json($screenings);
        } catch (\Exception $e) {
            Log::error('Diabetes screening getByUser error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Get latest screening for user untuk Next.js frontend
     * GET /api/diabetes-screenings/latest/{user_id}
     */
    public function getLatest(int $user_id): \Illuminate\Http\JsonResponse
    {
        try {
            $screening = DiabetesScreening::with('user') // FIX: Eager load user
                ->where('user_id', $user_id)
                ->orderBy('screening_date', 'desc')
                ->first();

            if (!$screening) {
                return response()->json([
                    'message' => 'No screening found for this user'
                ], 404);
            }

            return response()->json($this->formatScreeningForFrontend($screening));
        } catch (\Exception $e) {
            Log::error('Diabetes screening getLatest error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve latest screening',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // HELPER METHOD UNTUK FORMAT DATA FRONTEND
    // ========================================

    /**
     * Format screening data untuk Next.js frontend
     */
    private function formatScreeningForFrontend($screening): array
    {
        return [
            'id' => $screening->id,
            'user_id' => $screening->user_id,
            'patient_name' => $screening->name ?? 'Pasien Tidak Diketahui', // FIX: Use actual user name
            'age' => $screening->age,
            'gender' => $screening->gender,
            'bmi' => (float) $screening->bmi,
            'high_blood_pressure' => $screening->high_blood_pressure === 'Tinggi' ? 1 : 0,
            'blood_glucose_level' => (float) $screening->blood_glucose_level,
            'smoking_history' => $screening->smoking_history,
            'prediction_result' => $screening->prediction_result,
            'prediction_score' => (float) $screening->prediction_score,
            'recommendation' => $screening->recommendation,
            'created_at' => $screening->screening_date ?? $screening->created_at,
            'updated_at' => $screening->updated_at,
        ];
    }

    
}
