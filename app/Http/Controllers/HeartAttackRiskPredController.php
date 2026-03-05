<?php

namespace App\Http\Controllers;

use App\Models\HeartAttackRiskPred;
use App\Models\PersonalInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HeartAttackRiskPredController extends Controller
{
    /**
     * 1) LIST PASIEN YANG PUNYA PREDIKSI
     * GET /api/heart-attack-risk/predictions/patients?q=tes
     * Return: [{personal_information_id, name, age, gender, total_predictions, last_predicted_at}]
     */
    public function listPatientsWithPredictions(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = PersonalInformation::query()
            ->select([
                'personal_information.id',
                'personal_information.name',
                'personal_information.age',
                'personal_information.gender',
            ])
            // hanya pasien yang punya prediksi
            ->join('heart_attack_risk_pred as pred', 'pred.personal_information_id', '=', 'personal_information.id')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('personal_information.name', 'LIKE', "%{$q}%");
            })
            ->groupBy('personal_information.id', 'personal_information.name', 'personal_information.age', 'personal_information.gender')
            ->selectRaw('COUNT(pred.id) as total_predictions')
            ->selectRaw('MAX(pred.created_at) as last_predicted_at')
            ->orderByDesc('last_predicted_at')
            ->limit(50);

        $rows = $query->get()->map(function ($p) {
            // normalisasi gender output: Male/Female (optional)
            $g = strtolower(trim((string) $p->gender));
            if ($g === '1' || $g === 'female') {
                $p->gender = 'Female';
            } elseif ($g === '0' || $g === 'male') {
                $p->gender = 'Male';
            }
            return [
                'personal_information_id' => $p->id,
                'name' => $p->name,
                'age' => $p->age,
                'gender' => $p->gender,
                'total_predictions' => (int) $p->total_predictions,
                'last_predicted_at' => $p->last_predicted_at,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $rows,
        ], 200);
    }

    /**
     * 2) LIST RIWAYAT PREDIKSI PER PASIEN
     * GET /api/heart-attack-risk/predictions/patients/{personal_information_id}
     * Return: list pred
     */
    public function listPredictionsByPatient(string $personal_information_id)
    {
        $exists = PersonalInformation::query()->where('id', $personal_information_id)->exists();
        if (!$exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pasien tidak ditemukan',
            ], 404);
        }

        $preds = HeartAttackRiskPred::query()
            ->where('personal_information_id', $personal_information_id)
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
     * 3) DETAIL 1 PREDIKSI (untuk card detail cek kesehatan + card hasil prediksi)
     * GET /api/heart-attack-risk/predictions/{id}
     * Return: {health_check: {...}, prediction: {...}}
     */
    public function show(string $id)
    {
        $pred = HeartAttackRiskPred::query()
            ->with(['healthCheck'])
            ->where('id', $id)
            ->first();

        if (!$pred) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data prediksi tidak ditemukan',
            ], 404);
        }

        // decode text JSON jika memang JSON string
        $analysis = $this->safeJsonDecode($pred->analysis_text);
        $factor  = $this->safeJsonDecode($pred->factor_text);

        return response()->json([
            'status' => 'success',
            'data' => [
                'health_check' => $pred->healthCheck, // card detail cek kesehatan
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
     * 4) DELETE 1 PREDIKSI
     * DELETE /api/heart-attack-risk/predictions/{id}
     */
    public function destroy(string $id)
    {
        $pred = HeartAttackRiskPred::query()->where('id', $id)->first();

        if (!$pred) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data prediksi tidak ditemukan',
            ], 404);
        }

        $pred->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Prediksi berhasil dihapus',
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