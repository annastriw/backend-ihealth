<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatientHealthCheck;
use App\Models\PersonalInformation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PatientHealthCheckController extends Controller
{
// 1️⃣ Search pasien (autocomplete)
public function searchPatient(Request $request)
{
    $query = $request->query('q', '');

    $patients = PersonalInformation::where('name', 'LIKE', "%{$query}%")
                    ->select('id', 'name', 'age', 'gender')
                    ->limit(10)
                    ->get()
                    ->map(function ($patient) {
                        // Mapping gender: 0 => Male, 1 => Female
                        $patient->gender = $patient->gender == 1 ? 'Female' : 'Male';
                        return $patient;
                    });

    return response()->json([
        'status' => 'success',
        'data' => $patients,
    ]);
}


    // 2️⃣ Simpan cek kesehatan pasien
    public function store(Request $request)
    {
        $validated = $request->validate([
            'personal_information_id' => 'required|exists:personal_information,id',
            'name' => 'required|string',
            'age' => 'required|string',
            'gender' => 'required|string',
            'check_date' => 'required|date',
            'blood_pressure_systolic' => 'required|integer',
            'blood_pressure_diastolic' => 'required|integer',
            'hypertension' => 'required|boolean',
            'random_blood_sugar' => 'required|integer',
            'diabetes' => 'required|boolean',
            'cholesterol_level' => 'required|integer',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'bmi' => 'required|numeric',
            'obesity' => 'required|boolean',
            'waist_circumference' => 'required|integer',
            'family_history' => 'required|boolean',
            'smoking_status' => 'required|in:NEVER,CURRENT,PAST',
            'physical_activity' => 'required|in:LOW,MODERATE,HIGH',
            'dietary_habits' => 'required|in:UNHEALTHY,HEALTHY',
            'stress_level' => 'required|in:LOW,MODERATE,HIGH',
            'sleep_hours' => 'required|integer',
            'previous_heart_disease' => 'required|boolean',
            'medication_usage' => 'required|boolean',
        ]);

        $validated['id'] = Str::uuid()->toString();

        $healthCheck = PatientHealthCheck::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Patient health check saved successfully',
            'data' => $healthCheck,
        ]);
    }

    // 3️⃣ Preview data pasien terakhir / semua history
    public function preview(Request $request, $personal_information_id)
    {
        // Ambil semua record terakhir dari pasien
        $records = PatientHealthCheck::where('personal_information_id', $personal_information_id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $records,
        ]);
    }

    public function listPatientSummary(Request $request)
    {
        $search = $request->get('q', '');

        $query = PatientHealthCheck::select(
                'personal_information_id',
                'name',
                DB::raw('COUNT(*) as total_checks'),
                DB::raw('MAX(check_date) as last_check_date')
            )
            ->groupBy('personal_information_id', 'name');

        if (!empty($search)) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $patients = $query->get();

        // Ambil hipertensi & diabetes terakhir per pasien
        $patients = $patients->map(function ($p) {
            $lastRecord = PatientHealthCheck::where('personal_information_id', $p->personal_information_id)
                ->orderBy('check_date', 'desc')
                ->first();

            return [
                'personal_information_id' => $p->personal_information_id,
                'name' => $p->name,
                'hypertension_last' => $lastRecord ? $lastRecord->hypertension : false,
                'diabetes_last' => $lastRecord ? $lastRecord->diabetes : false,
                'total_checks' => $p->total_checks,
                'last_check_date' => $p->last_check_date,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $patients
        ]);
    }
}