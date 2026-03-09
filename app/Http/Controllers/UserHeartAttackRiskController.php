<?php

namespace App\Http\Controllers;

use App\Models\HeartAttackRiskPred;
use App\Models\PersonalInformation;
use Illuminate\Http\Request;

class UserHeartAttackRiskController extends Controller
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
     * GET /api/user/heart-attack-risk/predictions
     * Response format disamakan dengan:
     * HeartAttackRiskPredController::listPredictionsByPatient()
     */
    public function myPredictions(Request $request)
    {
        $personalInformation = $this->getMyPersonalInformation($request);

        if (!$personalInformation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil pasien tidak ditemukan',
            ], 404);
        }

        $preds = HeartAttackRiskPred::query()
            ->where('personal_information_id', $personalInformation->id)
            ->orderBy('created_at', 'desc')
            ->get([
                'id',
                'patient_health_check_id',
                'personal_information_id',
                'pred_value',
                'not_risk',
                'risk',
                'created_at',
            ])
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $preds,
        ], 200);
    }

    /**
     * GET /api/user/heart-attack-risk/predictions/{id}
     * Response format disamakan dengan:
     * HeartAttackRiskPredController::show()
     */
    public function show(string $id, Request $request)
    {
        $personalInformation = $this->getMyPersonalInformation($request);

        if (!$personalInformation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil pasien tidak ditemukan',
            ], 404);
        }

        $pred = HeartAttackRiskPred::query()
            ->with(['healthCheck'])
            ->where('id', $id)
            ->where('personal_information_id', $personalInformation->id)
            ->first();

        if (!$pred) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data prediksi tidak ditemukan',
            ], 404);
        }

        $analysis = $this->safeJsonDecode($pred->analysis_text);
        $factor = $this->safeJsonDecode($pred->factor_text);

        return response()->json([
            'status' => 'success',
            'data' => [
                'health_check' => $pred->healthCheck,
                'prediction' => [
                    'id' => $pred->id,
                    'patient_health_check_id' => $pred->patient_health_check_id,
                    'personal_information_id' => $pred->personal_information_id,
                    'pred_value' => (int) $pred->pred_value,
                    'not_risk' => (float) $pred->not_risk,
                    'risk' => (float) $pred->risk,
                    'analysis_text' => $analysis,
                    'factor_text' => $factor,
                    'created_at' => $pred->created_at,
                ],
            ],
        ], 200);
    }

    /**
     * helper decode JSON string -> array
     */
    private function safeJsonDecode($value): array
    {
        if ($value === null) return [];
        if (is_array($value)) return $value;

        $str = (string) $value;
        $decoded = json_decode($str, true);

        return is_array($decoded) ? $decoded : [$str];
    }
}