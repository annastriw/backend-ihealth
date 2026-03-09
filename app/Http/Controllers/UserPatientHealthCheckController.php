<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatientHealthCheck;
use App\Models\PersonalInformation;

class UserPatientHealthCheckController extends Controller
{
    /**
     * Ambil data personal_information milik user login
     */
    private function getMyPersonalInformation(Request $request): ?PersonalInformation
    {
        $user = $request->user();

        return PersonalInformation::query()
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * GET /api/user/patient-health-check/card-info
     * Response format disamakan dengan:
     * PatientHealthCheckController::showLatestByPersonalInformation()
     */
    public function myCardInfo(Request $request)
    {
        $personalInformation = $this->getMyPersonalInformation($request);

        if (!$personalInformation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil pasien tidak ditemukan'
            ], 404);
        }

        $latestCheck = PatientHealthCheck::where('personal_information_id', $personalInformation->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestCheck) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data cek kesehatan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'name' => $latestCheck->name,
                'age' => $latestCheck->age,
                'gender' => $latestCheck->gender,

                'hypertension' => $latestCheck->hypertension,
                'diabetes' => $latestCheck->diabetes,
                'obesity' => $latestCheck->obesity,
                'family_history' => $latestCheck->family_history,

                'smoking_status' => $latestCheck->smoking_status,
                'physical_activity' => $latestCheck->physical_activity,

                'previous_heart_disease' => $latestCheck->previous_heart_disease,
                'medication_usage' => $latestCheck->medication_usage,

                'checked_at' => $latestCheck->created_at?->toDateTimeString(),
            ]
        ], 200);
    }

    /**
     * GET /api/user/patient-health-check/analytics
     * Response format disamakan dengan:
     * PatientHealthCheckController::analyticsByPatient()
     */
    public function myAnalytics(Request $request)
    {
        $personalInformation = $this->getMyPersonalInformation($request);

        if (!$personalInformation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil pasien tidak ditemukan'
            ], 404);
        }

        $checks = PatientHealthCheck::where('personal_information_id', $personalInformation->id)
            ->orderBy('check_date', 'asc')
            ->get([
                'check_date',
                'blood_pressure_systolic',
                'blood_pressure_diastolic',
                'random_blood_sugar',
                'cholesterol_level',
                'bmi'
            ]);

        if ($checks->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data analytics cek kesehatan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'personal_information_id' => $personalInformation->id,
                'labels' => $checks->pluck('check_date'),
                'datasets' => [
                    'blood_pressure_systolic' => $checks->pluck('blood_pressure_systolic'),
                    'blood_pressure_diastolic' => $checks->pluck('blood_pressure_diastolic'),
                    'random_blood_sugar' => $checks->pluck('random_blood_sugar'),
                    'cholesterol_level' => $checks->pluck('cholesterol_level'),
                    'bmi' => $checks->pluck('bmi'),
                ]
            ]
        ], 200);
    }

    /**
     * GET /api/user/patient-health-check/table
     * Response format disamakan dengan:
     * PatientHealthCheckController::tableByPatient()
     */
    public function myTable(Request $request)
    {
        $personalInformation = $this->getMyPersonalInformation($request);

        if (!$personalInformation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil pasien tidak ditemukan'
            ], 404);
        }

        $checks = PatientHealthCheck::where('personal_information_id', $personalInformation->id)
            ->orderBy('check_date', 'desc')
            ->get([
                'id',
                'check_date',
                'blood_pressure_systolic',
                'blood_pressure_diastolic',
                'random_blood_sugar',
                'cholesterol_level',
                'height',
                'weight',
                'bmi',
                'waist_circumference',
                'dietary_habits',
                'sleep_hours',
                'stress_level'
            ]);

        if ($checks->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data cek kesehatan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $checks
        ], 200);
    }
}