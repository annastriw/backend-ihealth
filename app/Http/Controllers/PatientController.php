<?php

namespace App\Http\Controllers;

use App\Models\PersonalInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    public function searchPatients(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 3) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Query pencarian minimal 3 karakter',
                    'statusCode' => 400
                ],
                'data' => []
            ]);
        }

        try {
            $patients = PersonalInformation::where('name', 'LIKE', "%{$query}%")
                ->orWhere('user_id', 'LIKE', "%{$query}%")
                ->select([
                    'id',
                    'user_id', 
                    'name',
                    'age',
                    'gender'
                ])
                ->limit(10)
                ->get();

            // Format response
            $formattedPatients = $patients->map(function ($patient) {
                return [
                    'id' => $patient->user_id ?? $patient->id,
                    'name' => $patient->name,
                    'age' => $patient->age ?? 0,
                    'gender' => $patient->gender == 1 ? 'Perempuan' : 'Laki-laki'
                ];
            });

            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'message' => 'Pencarian berhasil',
                    'statusCode' => 200
                ],
                'data' => $formattedPatients
            ]);

        } catch (\Exception $e) {
            Log::error('Error in searchPatients: ' . $e->getMessage());
            
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Error: ' . $e->getMessage(),
                    'statusCode' => 500
                ],
                'data' => []
            ], 500);
        }
    }

    public function getPatientDetail($patientId)
    {
        try {
            Log::info("Searching for patient ID: " . $patientId);
            // Coba berbagai cara untuk mencari pasien
            $patient = PersonalInformation::where('user_id', $patientId)
                ->orWhere('id', $patientId)
                ->first();

            // Jika tidak ditemukan, coba cari berdasarkan ID integer
            if (!$patient) {
                $patient = PersonalInformation::find($patientId);
            }

            // Jika masih tidak ditemukan, coba cari berdasarkan nama (untuk debugging)
            if (!$patient) {
                Log::info("Patient not found with ID: " . $patientId);
                
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'message' => 'Pasien tidak ditemukan - ID: ' . $patientId,
                        'statusCode' => 404
                    ],
                    'data' => null
                ], 404);
            }

            // Log untuk debugging
            Log::info("Found patient: " . $patient->name);

            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'message' => 'Data pasien berhasil diambil',
                    'statusCode' => 200
                ],
                'data' => [
                    'id' => $patient->user_id ?? $patient->id,
                    'name' => $patient->name,
                    'age' => $patient->age ?? 0,
                    'gender' => (int) ($patient->gender ?? 0), 
                    'phone' => $patient->phone,
                    'email' => $patient->email,
                    'place_of_birth' => $patient->place_of_birth,
                    'date_of_birth' => $patient->date_of_birth,
                    // Data untuk auto-fill form screening
                    'hypertension' => $patient->hypertension ?? 0,
                    'heart_disease' => $patient->heart_disease ?? $patient->heart_disease_history ?? 0,
                    'smoking_history' => $patient->smoking_history ?? 'tidak pernah merokok',
                    'bmi' => $patient->bmi ?? 0,
                    'blood_glucose_level' => $patient->blood_glucose_level ?? 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error in getPatientDetail: " . $e->getMessage());
            
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Error: ' . $e->getMessage(),
                    'statusCode' => 500
                ],
                'data' => null
            ], 500);
        }
    }

    public function checkColumns()
    {
        try {
            $sample = PersonalInformation::first();
            
            if ($sample) {
                return response()->json([
                    'success' => true,
                    'columns' => array_keys($sample->toArray()),
                    'sample_data' => $sample
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No data found'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error in checkColumns: " . $e->getMessage());
            
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Error: ' . $e->getMessage(),
                    'statusCode' => 500
                ],
                'data' => null
            ], 500);
        }
    }
}