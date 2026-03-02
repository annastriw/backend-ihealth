<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonalInformation;
use App\Models\PatientHealthCheck;

class HeartAttackRiskController extends Controller
{
    public function searchPatients(Request $request)
    {
        $q = trim($request->query('q', ''));

        if ($q === '') {
            return response()->json([
                'status' => 'success',
                'data' => [],
            ], 200);
        }

        $patients = PersonalInformation::query()
            ->where('name', 'LIKE', "%{$q}%")
            ->select('id', 'name', 'age', 'gender')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                $g = strtolower(trim((string) $p->gender));
                if ($g === '1' || $g === 'female') {
                    $p->gender = 'Female';
                } elseif ($g === '0' || $g === 'male') {
                    $p->gender = 'Male';
                }
                return $p;
            });

        return response()->json([
            'status' => 'success',
            'data' => $patients,
        ], 200);
    }

    public function latest5Checks($personal_information_id)
    {
        $exists = PersonalInformation::where('id', $personal_information_id)->exists();

        if (!$exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pasien tidak ditemukan',
            ], 404);
        }

        $checks = PatientHealthCheck::query()
            ->where('personal_information_id', $personal_information_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get([
                'id',
                'personal_information_id',
                'name',
                'age',
                'gender',
                'check_date',
                'blood_pressure_systolic',
                'blood_pressure_diastolic',
                'hypertension',
                'random_blood_sugar',
                'diabetes',
                'cholesterol_level',
                'height',
                'weight',
                'bmi',
                'obesity',
                'waist_circumference',
                'family_history',
                'smoking_status',
                'physical_activity',
                'dietary_habits',
                'stress_level',
                'sleep_hours',
                'previous_heart_disease',
                'medication_usage',
                'created_at',
                'updated_at',
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $checks,
        ], 200);
    }
}