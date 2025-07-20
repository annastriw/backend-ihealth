<?php

namespace App\Http\Controllers;

use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PersonalInformationController extends Controller
{
    private $mlApiUrl = 'https://tcnisaa-prediksi-dm-adaboost.hf.space/predict';

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
            'blood_glucose_level' => 'required|numeric|min:50|max:400',
            'hypertension' => 'required|in:0,1',
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
                'hypertension' => $validated['hypertension'],
                'blood_glucose_level' => $validated['blood_glucose_level'],
                'height' => $validated['height'],
                'weight' => $validated['weight'],
            ]);

            // Prepare data for ML prediction
            $mlData = [
                'gender' => (int) $validated['gender'],
                'age' => (int) $validated['age'],
                'hypertension' => (int) $validated['hypertension'],
                'heart_disease' => (int) $validated['heart_disease_history'],
                'smoking_history' => $validated['smoking_history'],
                'bmi' => (float) $validated['bmi'],
                'blood_glucose_level' => (float) $validated['blood_glucose_level']
            ];

            // Prediksi
            $prediction = $this->predictDiabetes($mlData);

            // Update data dengan hasil prediksi
            if (isset($prediction['prediction'])) {
                $personalInformation->update([
                    'diabetes_prediction' => $prediction['prediction'] ?? 0,
                    'prediction_probability' => $prediction['probability'] ?? null,
                    'risk_level' => $this->determineRiskLevel($prediction['prediction'] ?? 0),
                    'ml_response' => $prediction,
                    'predicted_at' => now()
                ]);
            }

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

    private function predictDiabetes($data)
    {
        try {
            Log::info('Sending to ML API', ['ml_data' => $data]);
        
        $client = new \GuzzleHttp\Client([
            'verify' => false, // Disable SSL untuk development
            'timeout' => 30,
        ]);
        
        $response = $client->post('https://tcnisaa-prediksi-dm-adaboost.hf.space/predict', [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);
        
        $result = json_decode($response->getBody()->getContents(), true);
        Log::info('ML API Response', ['result' => $result]);
        
        return $result;
        
    } catch (\Exception $e) {
        Log::error('ML API Error: ' . $e->getMessage());
        
        // Return default jika ML API gagal
        return [
            'prediction' => 0,
            'probability' => 0.5,
            'message' => 'ML API unavailable'
        ];
    }
}

    private function getRiskLevel($prediction)
    {
        return $prediction == 1 ? 'Tinggi' : 'Rendah';
    }

    private function determineRiskLevel($prediction)
    {
        return $prediction == 1 ? 'high' : 'low';
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
            'hypertension' => 'sometimes|in:0,1',
            'blood_glucose_level' => 'sometimes|numeric|min:50|max:400',
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

    public function predictDiabetesOnly(Request $request)
    {
        $validated = $request->validate([
            'gender' => 'required|in:0,1',
            'age' => 'required|integer|min:1|max:120',
            'hypertension' => 'required|in:0,1',
            'heart_disease' => 'required|in:0,1',
            'smoking_history' => 'required|in:tidak pernah merokok,perokok aktif,mantan perokok,tidak ada informasi',
            'bmi' => 'required|numeric|min:10|max:50',
            'blood_glucose_level' => 'required|numeric|min:50|max:400'
        ]);

        $prediction = $this->predictDiabetes($validated);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Diabetes prediction completed',
                'statusCode' => 200
            ],
            'data' => $prediction
        ]);
    }

    // Method untuk screening diabetes dari frontend - YANG SUDAH DIPERBAIKI
    public function screeningDiabetes(Request $request)
    {
        Log::info('=== SCREENING DIABETES START ===');
        Log::info('Received screening data', ['data' => $request->all()]);

        try {
            $validated = $request->validate([
                'patient_id' => 'required|string',
                'gender' => 'required|in:0,1',
                'age' => 'required|integer|min:1|max:120',
                'heart_disease' => 'required|in:0,1',
                'smoking_history' => 'required|string|in:perokok aktif,mantan perokok,tidak pernah merokok,tidak ada informasi',
                'bmi' => 'required|numeric|min:10|max:50',
                'hypertension' => 'required|in:0,1',
                'blood_glucose_level' => 'required|numeric|min:50|max:400'
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            
            $mlData = [
                'gender' => (int) $validated['gender'], 
                'age' => (int) $validated['age'],
                'hypertension' => (int) $validated['hypertension'],
                'heart_disease' => (int) $validated['heart_disease'],
                'smoking_history' => $validated['smoking_history'],
                'bmi' => (float) $validated['bmi'],
                'blood_glucose_level' => (float) $validated['blood_glucose_level']
            ];

            Log::info('Prepared ML data', ['ml_data' => $mlData]);

            // Kirim ke ML API
            $prediction = $this->predictDiabetes($mlData);

            if (isset($prediction['prediction'])) {
                try {
                    Log::info('Attempting to save screening results to diabetes_screenings table');
                    
                    // SIMPAN KE TABEL DIABETES_SCREENINGS 
                    $screeningId = DB::table('diabetes_screenings')->insertGetId([
                        'user_id' => $validated['patient_id'],
                        'hypertension' => $validated['hypertension'],
                        'blood_glucose_level' => $validated['blood_glucose_level'],
                        'diabetes_prediction' => $prediction['prediction'] ?? 0,
                        'prediction_probability' => $prediction['probability'] ?? null,
                        'risk_level' => $this->determineRiskLevel($prediction['prediction'] ?? 0),
                        'ml_response' => json_encode($prediction),
                        'predicted_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    Log::info('Screening results saved successfully to diabetes_screenings', [
                        'screening_id' => $screeningId,
                        'patient_id' => $validated['patient_id'],
                        'prediction' => $prediction['prediction'] ?? 0
                    ]);

                } catch (\Exception $dbError) {
                    Log::error('Database save error (but screening still successful)', [
                        'error' => $dbError->getMessage(),
                        'patient_id' => $validated['patient_id']
                    ]);
                }
            }
        
            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'message' => 'Screening berhasil dan data tersimpan di database',
                    'statusCode' => 200
                ],
                'data' => [
                    'patient_id' => $validated['patient_id'],
                    'prediction' => $prediction['prediction'] ?? 0,
                    'probability' => $prediction['probability'] ?? 0,
                    'risk_level' => $this->getRiskLevel($prediction['prediction'] ?? 0),
                    'ml_response' => $prediction
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
            Log::error('Screening error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'url' => $this->mlApiUrl
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
}