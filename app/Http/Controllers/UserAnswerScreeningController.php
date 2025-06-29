<?php

namespace App\Http\Controllers;

use App\Models\UserAnswerScreening;
use App\Models\UserHistoryScreening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserAnswerScreeningController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'screening_id' => 'required|uuid',
            'answers' => 'required|array|min:1',
            'answers.*.selected_option_id' => 'nullable|uuid|exists:options,id',
            'answers.*.question_id' => 'nullable|uuid|exists:questions,id',
            'answers.*.answer_text' => 'nullable|string',
        ]);

        $user = auth()->user();

        try {
            // 1. Save user history screening
            $history = UserHistoryScreening::create([
                'user_id' => $user->id,
                'screening_id' => $request->screening_id,
            ]);

            // 2. Save a all answer to user answer screening
            foreach ($request->answers as $answer) {
                UserAnswerScreening::create([
                    'user_id' => $user->id,
                    'user_history_screening_id' => $history->id,
                    'selected_option_id' => $answer['selected_option_id'] ?? null,
                    'question_id' => $answer['question_id'],
                    'answer_text' => $answer['answer_text'] ?? null,
                    'answered_at' => now(),
                ]);
            }

            return response()->json([
                'message' => 'Screening berhasil disubmit',
                'history_id' => $history->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Submit screening error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
