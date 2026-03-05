<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonalInformation;
use App\Models\PatientHealthCheck;
use App\Models\HeartAttackRiskPred;
use Illuminate\Support\Facades\Validator;

class HeartAttackRiskController extends Controller
{
    /**
     * GET /api/heart-attack-risk/patients/search?q=testing
     * Return: [{id,name,age,gender}]
     */
    public function searchPatients(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([
                'status' => 'success',
                'data' => [],
            ], 200);
        }

        $patients = PersonalInformation::query()
            ->where('name', 'LIKE', "%{$q}%")
            ->select(['id', 'name', 'age', 'gender'])
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                // normalisasi gender output: Male/Female
                $g = strtolower(trim((string) $p->gender));
                if ($g === '1' || $g === 'female') {
                    $p->gender = 'Female';
                } elseif ($g === '0' || $g === 'male') {
                    $p->gender = 'Male';
                }
                return $p;
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $patients,
        ], 200);
    }

    /**
     * GET /api/heart-attack-risk/patients/{personal_information_id}/latest-checks
     * Return: last 5 checks (full record fields) to pick 1 then POST to Flask.
     */
    public function latest5Checks(string $personal_information_id)
    {
        // pastikan pasien ada
        $exists = PersonalInformation::query()
            ->where('id', $personal_information_id)
            ->exists();

        if (!$exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pasien tidak ditemukan',
            ], 404);
        }

        $checks = PatientHealthCheck::query()
            ->where('personal_information_id', $personal_information_id)
            // lebih logis: urutkan dari tanggal cek terbaru
            ->orderBy('check_date', 'desc')
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
            ])
            ->map(function ($c) {
                // normalisasi gender output: Male/Female
                $g = strtolower(trim((string) $c->gender));
                if ($g === '1' || $g === 'female') {
                    $c->gender = 'Female';
                } elseif ($g === '0' || $g === 'male') {
                    $c->gender = 'Male';
                }

                // normalisasi enum ke uppercase (biar konsisten untuk frontend & Flask)
                $c->smoking_status = strtoupper((string) $c->smoking_status);
                $c->physical_activity = strtoupper((string) $c->physical_activity);
                $c->dietary_habits = strtoupper((string) $c->dietary_habits);
                $c->stress_level = strtoupper((string) $c->stress_level);

                return $c;
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $checks,
        ], 200);
    }

    public function storePrediction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_health_check_id' => ['required', 'string', 'size:36', 'exists:patient_health_check,id'],
            'personal_information_id' => ['required', 'string', 'size:36', 'exists:personal_information,id'],

            'pred_value' => ['required', 'integer'],
            'not_risk'   => ['required', 'numeric', 'min:0', 'max:1'],
            'risk'       => ['required', 'numeric', 'min:0', 'max:1'],

            // boleh kirim array dari frontend -> akan di-json_encode
            'analysis_text' => ['required'],
            'factor_text'   => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $analysis = $request->input('analysis_text');
        $factor = $request->input('factor_text');

        // fleksibel: kalau array -> simpan JSON string, kalau string -> simpan string
        $analysisText = is_array($analysis) ? json_encode($analysis, JSON_UNESCAPED_UNICODE) : (string) $analysis;
        $factorText   = is_array($factor) ? json_encode($factor, JSON_UNESCAPED_UNICODE) : (string) $factor;

        $pred = HeartAttackRiskPred::create([
            'patient_health_check_id' => $request->string('patient_health_check_id'),
            'personal_information_id' => $request->string('personal_information_id'),
            'pred_value' => (int) $request->input('pred_value'),
            'not_risk'   => (float) $request->input('not_risk'),
            'risk'       => (float) $request->input('risk'),
            'analysis_text' => $analysisText,
            'factor_text' => $factorText,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Prediksi berhasil disimpan',
            'data' => [
                'id' => $pred->id,
                'patient_health_check_id' => $pred->patient_health_check_id,
                'personal_information_id' => $pred->personal_information_id,
                'pred_value' => $pred->pred_value,
                'not_risk' => $pred->not_risk,
                'risk' => $pred->risk,
                'created_at' => $pred->created_at,
            ],
        ], 201);
    }
}