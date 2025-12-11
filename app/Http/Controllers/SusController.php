<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSusRequest;
use App\Models\SusResponse;
use App\Models\SusResponseDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SusController extends Controller
{
    public function store(StoreSusRequest $request)
    {
        $userId = Auth::id();

        // RULE: hanya 1x per hari
        $already = SusResponse::where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($already) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only submit once per day.'
            ], 403);
        }

        // Create SUS response
        $sus = SusResponse::create([
            'id' => Str::uuid(),
            'user_id' => $userId
        ]);

        $convertedTotal = 0;
        $answers = $request->answers;

        foreach ($answers as $index => $value) {
            $questionId = $index + 1;

            // Convert SUS scoring
            if ($questionId % 2 === 1) { 
                $converted = $value - 1;
            } else {
                $converted = 5 - $value;
            }

            $convertedTotal += $converted;

            SusResponseDetail::create([
                'id' => Str::uuid(),
                'sus_response_id' => $sus->id,
                'question_id' => $questionId,
                'answer_raw' => $value,
                'answer_converted' => $converted
            ]);
        }

        // Convert total to SUS 0-100
        $susScore = $convertedTotal * 2.5;

        // Interpretasi
        $interpretation = $this->interpretation($susScore);

        $sus->update([
            'total_score' => $susScore,
            'interpretation' => $interpretation
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_score' => $susScore,
                'interpretation' => $interpretation,
            ]
        ]);
    }

    private function interpretation($score)
    {
        if ($score >= 85) return 'Excellent';
        if ($score >= 70) return 'Good';
        if ($score >= 50) return 'Acceptable';
        return 'Poor';
    }
}
