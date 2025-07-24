<?php

// ========================================
// 1. PersonalInformationController.php (CLEANED UP)
// ========================================

namespace App\Http\Controllers;

use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PersonalInformationController extends Controller
{
    public function index()
    {
        $personalInformations = PersonalInformation::all();
        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information list fetched successfully',
                'statusCode' => 200
            ],
            'data' => $personalInformations,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'place_of_birth' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'age' => 'required|integer|min:1|max:120',
            'work' => 'required|string',
            'gender' => 'required|in:0,1',
            'is_married' => 'required|boolean',
            'last_education' => 'required|string|max:255',
            'origin_disease' => 'required|string|max:255',
            'disease_duration' => 'required|string|max:255',
            'history_therapy' => 'required|string|max:255',
            'smoking_history' => 'required|in:perokok aktif,mantan perokok,tidak pernah merokok,tidak ada informasi',
            'bmi' => 'required|numeric|min:10|max:50',
            'heart_disease_history' => 'required|in:0,1',
            'height' => 'required|string|max:10',
            'weight' => 'required|string|max:10',
        ]);

        try {
            $personalInformation = PersonalInformation::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'place_of_birth' => $validated['place_of_birth'],
                'date_of_birth' => $validated['date_of_birth'],
                'age' => $validated['age'],
                'gender' => $validated['gender'],
                'work' => $validated['work'],
                'is_married' => $validated['is_married'],
                'last_education' => $validated['last_education'],
                'origin_disease' => $validated['origin_disease'],
                'disease_duration' => $validated['disease_duration'],
                'history_therapy' => $validated['history_therapy'],
                'smoking_history' => $validated['smoking_history'],
                'bmi' => $validated['bmi'],
                'heart_disease_history' => $validated['heart_disease_history'],
                'height' => $validated['height'],
                'weight' => $validated['weight'],
            ]);

            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'message' => 'Personal Information created successfully',
                    'statusCode' => 201
                ],
                'data' => $personalInformation,
            ], 201);
        } catch (\Throwable $th) {
            Log::error('Error saving personal information: ' . $th->getMessage());
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => $th->getMessage(),
                    'statusCode' => 500
                ]
            ], 500);
        }
    }

    public function show($id)
    {
        $personalInformation = PersonalInformation::with('user')->findOrFail($id);
        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $personalInformation,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $personalInformation = PersonalInformation::where('user_id', $user->id)->first();

        if (!$personalInformation) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Personal Information not found for this user',
                    'statusCode' => 404
                ]
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'place_of_birth' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date',
            'age' => 'sometimes|integer|min:1|max:120',
            'gender' => 'sometimes|in:0,1',
            'is_married' => 'sometimes|boolean',
            'last_education' => 'sometimes|string|max:255',
            'origin_disease' => 'sometimes|string|max:255',
            'disease_duration' => 'sometimes|string|max:255',
            'history_therapy' => 'sometimes|string|max:255',
            'smoking_history' => 'sometimes|in:perokok aktif,mantan perokok,tidak pernah merokok,tidak ada informasi',
            'bmi' => 'sometimes|numeric|min:10|max:50',
            'heart_disease_history' => 'sometimes|in:0,1',
            'height' => 'sometimes|string|max:10',
            'weight' => 'sometimes|string|max:10',
        ]);

        $personalInformation->update($validated);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information updated successfully',
                'statusCode' => 200,
            ],
            'data' => $personalInformation,
        ]);
    }

    public function destroy($id)
    {
        $personalInformation = PersonalInformation::findOrFail($id);
        $personalInformation->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information deleted successfully',
                'statusCode' => 200,
            ],
        ]);
    }

    public function getPersonalInformationByUserId($user_id)
    {
        $personalInformation = PersonalInformation::where('user_id', $user_id)->first();

        if (!$personalInformation) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Personal Information not found for this user',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $personalInformation,
        ]);
    }

    public function checkUserPersonalInformation(Request $request)
    {
        $user = $request->user();
        $isCompleted = PersonalInformation::where('user_id', $user->id)->exists();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information check completed',
                'statusCode' => 200,
            ],
            'data' => [
                'is_completed' => $isCompleted,
            ],
        ]);
    }

    public function showAuthenticatedUserPersonalInformation(Request $request)
    {
        $user = $request->user();
        $personalInformation = PersonalInformation::where('user_id', $user->id)->first();

        if (!$personalInformation) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Personal Information not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $personalInformation,
        ]);
    }
}

// ========================================
// 2. DiabetesScreeningController.php (NEW SEPARATE CONTROLLER)
// ========================================

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\DB;
// use Carbon\Carbon;

// class DiabetesScreeningController extends Controller
// {
//     private $mlApiUrl = 'https://tcnisaa-prediksi-dm-adaboost.hf.space/predict';

//     public function predictDiabetesOnly(Request $request)
//     {
//         $validated = $request->validate([
//             'gender' => 'required|in:0,1',
//             'age' => 'required|integer|min:1|max:120',
//             'hypertension' => 'required|in:0,1',
//             'heart_disease' => 'required|in:0,1',
//             'smoking_history' => 'required|in:tidak pernah merokok,perokok aktif,mantan perokok,tidak ada informasi',
//             'bmi' => 'required|numeric|min:10|max:50',
//             'blood_glucose_level' => 'required|numeric|min:50|max:400'
//         ]);

//         $prediction = $this->predictDiabetes($validated);

//         return response()->json([
//             'meta' => [
//                 'status' => 'success',
//                 'message' => 'Diabetes prediction completed',
//                 'statusCode' => 200
//             ],
//             'data' => $prediction
//         ]);
//     }

//     public function screeningDiabetes(Request $request)
//     {
//         Log::info('=== SCREENING DIABETES START ===');
//         Log::info('Received screening data', ['data' => $request->all()]);

//         try {
//             $validated = $request->validate([
//                 'patient_id' => 'required|string',
//                 'gender' => 'required|in:0,1',
//                 'age' => 'required|integer|min:1|max:120',
//                 'heart_disease' => 'required|in:0,1',
//                 'smoking_history' => 'required|string|in:perokok aktif,mantan perokok,tidak pernah merokok,tidak ada informasi',
//                 'bmi' => 'required|numeric|min:10|max:50',
//                 'hypertension' => 'required|in:0,1',
//                 'blood_glucose_level' => 'required|numeric|min:50|max:400'
//             ]);

//             Log::info('Validation passed', ['validated' => $validated]);

//             $mlData = [
//                 'gender' => (int) $validated['gender'], 
//                 'age' => (int) $validated['age'],
//                 'hypertension' => (int) $validated['hypertension'],
//                 'heart_disease' => (int) $validated['heart_disease'],
//                 'smoking_history' => $validated['smoking_history'],
//                 'bmi' => (float) $validated['bmi'],
//                 'blood_glucose_level' => (float) $validated['blood_glucose_level']
//             ];

//             Log::info('Prepared ML data', ['ml_data' => $mlData]);

//             // Kirim ke ML API
//             $prediction = $this->predictDiabetes($mlData);

//             if (isset($prediction['prediction'])) {
//                 try {
//                     Log::info('Attempting to save screening results to diabetes_screenings table');
                    
//                     $screeningId = DB::table('diabetes_screenings')->insertGetId([
//                         'user_id' => auth()->id(),
//                         'age' => $validated['age'],
//                         'gender' => $validated['gender'] == 1 ? 'Perempuan' : 'Laki-laki',
//                         'bmi' => $validated['bmi'],
//                         'smoking_history' => $validated['smoking_history'],
//                         'high_blood_pressure' => $validated['hypertension'] == 1 ? 'Tinggi' : 'Normal',
//                         'blood_glucose_level' => $validated['blood_glucose_level'],
//                         'prediction_result' => $this->getRiskLevel($prediction['prediction'] ?? 0),
//                         'prediction_score' => ($prediction['probability'] ?? 0.5) * 100,
//                         'recommendation' => $this->getRecommendation($prediction['prediction'] ?? 0),
//                         'screening_date' => now(),
//                         'ml_response' => json_encode($prediction),
//                         'created_at' => now(),
//                         'updated_at' => now()
//                     ]);

//                     Log::info('Screening results saved successfully', ['screening_id' => $screeningId]);

//                 } catch (\Exception $dbError) {
//                     Log::error('Database save error', ['error' => $dbError->getMessage()]);
//                 }
//             }
        
//             return response()->json([
//                 'meta' => [
//                     'status' => 'success',
//                     'message' => 'Screening berhasil dan data tersimpan di database',
//                     'statusCode' => 200
//                 ],
//                 'data' => [
//                     'id' => $screeningId ?? null,
//                     'screening_id' => $screeningId ?? null,
//                     'patient_id' => $validated['patient_id'],
//                     'prediction' => $prediction['prediction'] ?? 0,
//                     'probability' => $prediction['probability'] ?? 0,
//                     'risk_level' => $this->getRiskLevel($prediction['prediction'] ?? 0),
//                     'risk_score' => ($prediction['probability'] ?? 0.5) * 100,
//                     'recommendation' => $this->getRecommendation($prediction['prediction'] ?? 0),
//                     'ml_response' => $prediction
//                 ]
//             ]);
            
//         } catch (\Illuminate\Validation\ValidationException $e) {
//             Log::error('Validation Error', ['errors' => $e->errors()]);
//             return response()->json([
//                 'meta' => [
//                     'status' => 'error',
//                     'message' => 'Validation failed',
//                     'errors' => $e->errors(),
//                     'statusCode' => 422
//                 ]
//             ], 422);

//         } catch (\Exception $e) {
//             Log::error('Screening error', [
//                 'message' => $e->getMessage(),
//                 'trace' => $e->getTraceAsString()
//             ]);

//             return response()->json([
//                 'meta' => [
//                     'status' => 'error',
//                     'message' => 'Error during screening: ' . $e->getMessage(),
//                     'statusCode' => 500
//                 ]
//             ], 500);
//         }
//     }

//     public function getDiabetesHistory(Request $request)
//     {
//         $user = auth()->user();
        
//         $query = DB::table('diabetes_screenings')->where('user_id', $user->id);
        
//         if ($request->has('risk') && $request->risk != '') {
//             $query->where('prediction_result', $request->risk);
//         }
        
//         if ($request->has('date_from') && $request->date_from != '') {
//             $query->whereDate('screening_date', '>=', $request->date_from);
//         }
        
//         if ($request->has('date_to') && $request->date_to != '') {
//             $query->whereDate('screening_date', '<=', $request->date_to);
//         }
        
//         $screenings = $query->orderBy('screening_date', 'desc')->paginate(15);
        
//         return response()->json([
//             'meta' => [
//                 'status' => 'success',
//                 'message' => 'Diabetes history fetched successfully',
//                 'statusCode' => 200
//             ],
//             'data' => $screenings->items(),
//             'pagination' => [
//                 'current_page' => $screenings->currentPage(),
//                 'total_pages' => $screenings->lastPage(),
//                 'total_items' => $screenings->total()
//             ]
//         ]);
//     }

//     public function getDiabetesDetail($id)
//     {
//         $screening = DB::table('diabetes_screenings')
//             ->where('user_id', auth()->id())
//             ->where('id', $id)
//             ->first();
        
//         if (!$screening) {
//             return response()->json([
//                 'meta' => [
//                     'status' => 'error',
//                     'message' => 'Screening data not found',
//                     'statusCode' => 404
//                 ]
//             ], 404);
//         }
        
//         return response()->json([
//             'meta' => [
//                 'status' => 'success',
//                 'message' => 'Diabetes screening detail fetched successfully',
//                 'statusCode' => 200
//             ],
//             'data' => $screening
//         ]);
//     }

//     public function deleteDiabetesScreening($id)
//     {
//         $deleted = DB::table('diabetes_screenings')
//             ->where('user_id', auth()->id())
//             ->where('id', $id)
//             ->delete();
        
//         if (!$deleted) {
//             return response()->json([
//                 'meta' => [
//                     'status' => 'error',
//                     'message' => 'Screening data not found',
//                     'statusCode' => 404
//                 ]
//             ], 404);
//         }
        
//         return response()->json([
//             'meta' => [
//                 'status' => 'success',
//                 'message' => 'Diabetes screening deleted successfully',
//                 'statusCode' => 200
//             ]
//         ]);
//     }

//     public function getDiabetesChartData()
//     {
//         $user = auth()->user();
        
//         $screenings = DB::table('diabetes_screenings')
//             ->where('user_id', $user->id)
//             ->where('screening_date', '>=', Carbon::now()->subDays(30))
//             ->orderBy('screening_date')
//             ->get();
        
//         $dates = [];
//         $scores = [];
        
//         foreach ($screenings as $screening) {
//             $dates[] = Carbon::parse($screening->screening_date)->format('d M');
//             $scores[] = $screening->prediction_score ?? 0;
//         }
        
//         return response()->json([
//             'meta' => [
//                 'status' => 'success',
//                 'message' => 'Chart data fetched successfully',
//                 'statusCode' => 200
//             ],
//             'data' => [
//                 'dates' => $dates,
//                 'scores' => $scores
//             ]
//         ]);
//     }

//     // ADMIN METHODS
//     public function getAllDiabetesHistory()
//     {
//         $screenings = DB::table('diabetes_screenings')
//             ->join('users', 'diabetes_screenings.user_id', '=', 'users.id')
//             ->select('diabetes_screenings.*', 'users.name as user_name', 'users.email as user_email')
//             ->orderBy('diabetes_screenings.screening_date', 'desc')
//             ->paginate(20);
        
//         return response()->json([
//             'meta' => [
//                 'status' => 'success',
//                 'message' => 'All diabetes history fetched successfully',
//                 'statusCode' => 200
//             ],
//             'data' => $screenings->items(),
//             'pagination' => [
//                 'current_page' => $screenings->currentPage(),
//                 'total_pages' => $screenings->lastPage(),
//                 'total_items' => $screenings->total()
//             ]
//         ]);
//     }

//     public function adminDeleteDiabetesScreening($id)
//     {
//         $deleted = DB::table('diabetes_screenings')->where('id', $id)->delete();
        
//         if (!$deleted) {
//             return response()->json([
//                 'meta' => [
//                     'status' => 'error',
//                     'message' => 'Screening data not found',
//                     'statusCode' => 404
//                 ]
//             ], 404);
//         }
        
//         return response()->json([
//             'meta' => [
//                 'status' => 'success',
//                 'message' => 'Diabetes screening deleted successfully',
//                 'statusCode' => 200
//             ]
//         ]);
//     }

//     // PRIVATE HELPER METHODS
//     private function predictDiabetes($data)
//     {
//         try {
//             Log::info('Sending to ML API', ['ml_data' => $data]);
        
//             $client = new \GuzzleHttp\Client([
//                 'verify' => false,
//                 'timeout' => 30,
//             ]);
            
//             $response = $client->post($this->mlApiUrl, [
//                 'json' => $data,
//                 'headers' => [
//                     'Content-Type' => 'application/json',
//                     'Accept' => 'application/json',
//                 ]
//             ]);
            
//             $result = json_decode($response->getBody()->getContents(), true);
//             Log::info('ML API Response', ['result' => $result]);
            
//             return $result;
            
//         } catch (\Exception $e) {
//             Log::error('ML API Error: ' . $e->getMessage());
            
//             return [
//                 'prediction' => 0,
//                 'probability' => 0.5,
//                 'message' => 'ML API unavailable'
//             ];
//         }
//     }

//     private function getRiskLevel($prediction)
//     {
//         return $prediction == 1 ? 'Tinggi' : 'Rendah';
//     }

//     // private function determineRiskLevel($prediction)
//     // {
//     //     return $prediction == 1 ? 'high' : 'low';
//     // }

//     private function getRecommendation($prediction)
//     {
//         if ($prediction == 1) {
//             return 'Hasil screening menunjukkan risiko diabetes. Disarankan untuk segera konsultasi dengan dokter untuk pemeriksaan lebih lanjut dan mulai menerapkan pola hidup sehat.';
//         } else {
//             return 'Hasil screening menunjukkan risiko diabetes rendah. Tetap pertahankan pola hidup sehat dengan diet seimbang dan olahraga teratur.';
//         }
//     }
// }
