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
                'success' => false,
                'message' => 'Query pencarian minimal 3 karakter',
                'data' => []
            ]);
        }

        try {
            // Sekarang pakai kolom yang benar
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
                    'age' => $patient->age,
                    'gender' => $patient->gender
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Pencarian berhasil',
                'data' => $formattedPatients
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function getPatientDetail($patientId)
    {
        try {
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
                $patient = PersonalInformation::where('name', 'LIKE', "%{$patientId}%")
                    ->first();
            }

            // Log untuk debugging
            Log::info("Searching for patient ID: " . $patientId);
            if ($patient) {
                Log::info("Found patient: " . $patient->name);
            } else {
                Log::info("Patient not found");
            }

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien tidak ditemukan - ID: ' . $patientId
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pasien berhasil diambil',
                'data' => [
                    'id' => $patient->user_id ?? $patient->id,
                    'name' => $patient->name,
                    'age' => $patient->age,
                    'gender' => $patient->gender,
                    'place_of_birth' => $patient->place_of_birth,
                    'date_of_birth' => $patient->date_of_birth,
                    'hypertension' => $patient->hypertension ?? 0,
                    'heart_disease' => $patient->heart_disease_history ?? 0,
                    'smoking_history' => $patient->smoking_history ?? 0,
                    'bmi' => $patient->bmi ?? 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error in getPatientDetail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
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
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
}