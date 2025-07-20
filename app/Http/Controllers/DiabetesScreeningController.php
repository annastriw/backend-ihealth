<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DiabetesScreeningController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'hypertension' => 'required|boolean',
                'blood_glucose_level' => 'required|numeric|min:0|max:999',
            ]);

            // Simulasi ML prediction (ganti dengan API ML yang sebenarnya)
            $prediction = $this->predictDiabetes($request->all());

            // Insert ke database menggunakan raw query
            $id = DB::table('diabetes_screenings')->insertGetId([
                'user_id' => Auth::id() ?? 1, // default user 1 jika belum login
                'hypertension' => $request->hypertension,
                'blood_glucose_level' => $request->blood_glucose_level,
                'diabetes_prediction' => $prediction['prediction'],
                'prediction_probability' => $prediction['probability'],
                'risk_level' => $prediction['risk_level'],
                'ml_response' => json_encode($prediction),
                'predicted_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Screening berhasil disimpan!',
                'data' => [
                    'id' => $id,
                    'prediction' => $prediction['prediction'] ? 'Berisiko Diabetes' : 'Tidak Berisiko',
                    'probability' => $prediction['probability'],
                    'risk_level' => $prediction['risk_level'],
                    'recommendation' => $this->getRecommendation($prediction['risk_level'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function predictDiabetes($data)
    {
        // Simulasi simple logic untuk prediksi
        $score = 0;
        
        // Hypertension factor
        if ($data['hypertension']) {
            $score += 30;
        }
        
        // Blood glucose factor
        if ($data['blood_glucose_level'] >= 200) {
            $score += 50;
        } elseif ($data['blood_glucose_level'] >= 140) {
            $score += 30;
        } elseif ($data['blood_glucose_level'] >= 100) {
            $score += 10;
        }
        
        // Random factor untuk variasi
        $score += rand(0, 20);
        
        $probability = min($score / 100, 0.99);
        
        // Determine risk level
        if ($probability >= 0.7) {
            $riskLevel = 'high';
        } elseif ($probability >= 0.4) {
            $riskLevel = 'medium';
        } else {
            $riskLevel = 'low';
        }
        
        return [
            'prediction' => $probability > 0.5 ? 1 : 0,
            'probability' => round($probability, 4),
            'risk_level' => $riskLevel,
            'factors' => $data,
            'timestamp' => now()->toISOString()
        ];
    }

    private function getRecommendation($riskLevel)
    {
        return match($riskLevel) {
            'high' => 'Segera konsultasi ke dokter untuk pemeriksaan lebih lanjut.',
            'medium' => 'Perhatikan pola makan dan rutin olahraga. Konsultasi ke dokter jika perlu.',
            'low' => 'Pertahankan gaya hidup sehat dan rutin check-up.',
            default => 'Konsultasi ke tenaga medis untuk saran yang tepat.'
        };
    }

    public function index()
    {
        $screenings = DB::table('diabetes_screenings')
            ->where('user_id', Auth::id() ?? 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $screenings
        ]);
    }
}