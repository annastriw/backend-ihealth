<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiabetesScreening;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserScreeningController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get latest screening result
        $latestScreening = DiabetesScreening::where('user_id', $user->id)
            ->latest()
            ->first();
            
        // Get screening history
        $screeningHistory = DiabetesScreening::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('dashboard.diabetes-melitus.user-screening.index', compact('latestScreening', 'screeningHistory'));
    }
    
    public function create()
    {
        return view('dashboard.diabetes-melitus.user-screening.screening-form');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'age' => 'required|integer|min:1',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'bmi' => 'required|numeric|min:10|max:60',
            'heart_disease' => 'required|boolean',
            'family_history' => 'required|boolean', 
            'physically_active' => 'required|boolean',
            'high_blood_pressure' => 'required|boolean',
            'high_blood_sugar' => 'required|boolean',
            'smoking_history' => 'required|in:Tidak Pernah,Mantan Perokok,Perokok Aktif,Rendah',
        ]);
        
        // Calculate prediction score (simplified logic)
        $predictionScore = $this->calculatePredictionScore($request->all());
        
        // Determine prediction result
        $predictionResult = $predictionScore >= 50 ? 'Tinggi' : 'Rendah';
        
        // Generate unique patient ID for user
        $patientId = 'USR-' . Auth::id() . '-' . now()->format('YmdHis');
        
        DiabetesScreening::create([
            'user_id' => Auth::id(),
            'patient_id' => $patientId,
            'age' => $request->age,
            'gender' => $request->gender,
            'bmi' => $request->bmi,
            'heart_disease' => $request->heart_disease,
            'family_history' => $request->family_history,
            'physically_active' => $request->physically_active,
            'high_blood_pressure' => $request->high_blood_pressure,
            'high_blood_sugar' => $request->high_blood_sugar,
            'smoking_history' => $request->smoking_history,
            'prediction_result' => $predictionResult,
            'prediction_score' => $predictionScore,
        ]);
        
        return redirect()->route('user.screening.index')->with('success', 'Screening berhasil disimpan!');
    }
    
    private function calculatePredictionScore($data)
    {
        $score = 0;
        
        // Age scoring
        if ($data['age'] >= 45) $score += 20;
        elseif ($data['age'] >= 35) $score += 10;
        
        // BMI scoring
        if ($data['bmi'] >= 30) $score += 20;
        elseif ($data['bmi'] >= 25) $score += 10;
        
        // Risk factors
        if ($data['heart_disease']) $score += 15;
        if ($data['family_history']) $score += 15;
        if (!$data['physically_active']) $score += 10;
        if ($data['high_blood_pressure']) $score += 15;
        if ($data['high_blood_sugar']) $score += 20;
        
        // Smoking history
        if ($data['smoking_history'] == 'Perokok Aktif') $score += 15;
        elseif ($data['smoking_history'] == 'Mantan Perokok') $score += 10;
        
        return min($score, 100); // Cap at 100
    }
    
    public function show($id)
    {
        $screening = DiabetesScreening::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
            
        return view('dashboard.diabetes-melitus.user-screening.detail', compact('screening'));
    }
}
