<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Option;
use App\Models\UserHistoryScreeningScoring;
use App\Models\UserAnswerScreeningScoring;

class UserAnswerScreeningScoringController extends Controller
{
    public function submit(Request $request)
    {
        \Log::info('[submit] Incoming Request', $request->all());

        try {
            $user = \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $e) {
            \Log::error('[submit] Auth failed', [
                'token' => $request->bearerToken(),
                'header_auth' => $request->header('Authorization'),
                'exception' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Auth user not found',
            ], 401);
        }

        \Log::info('[submit] Auth user', [
            'token' => $request->bearerToken(),
            'user' => $user,
        ]);

        $request->validate([
            'screening_scoring_id' => 'required|uuid|exists:screening_scorings,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|uuid|exists:questions,id',
            'answers.*.selected_option_id' => 'nullable|uuid|exists:options,id',
            'answers.*.answer_text' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $optionIds = collect($request->answers)
                ->pluck('selected_option_id')
                ->filter()
                ->unique();

            $optionScores = Option::whereIn('id', $optionIds)->get()->keyBy('id');

            $history = UserHistoryScreeningScoring::create([
                'user_id' => $user->id,
                'screening_scoring_id' => $request->screening_scoring_id,
                'sum_score' => 0,
            ]);

            $total = 0;

            foreach ($request->answers as $ans) {
                UserAnswerScreeningScoring::create([
                    'user_id' => $user->id,
                    'user_history_screening_scoring_id' => $history->id,
                    'question_id' => $ans['question_id'],
                    'selected_option_id' => $ans['selected_option_id'] ?? null,
                    'answer_text' => $ans['answer_text'] ?? null,
                    'answered_at' => now(),
                ]);

                if (!empty($ans['selected_option_id'])) {
                    $option = $optionScores->get($ans['selected_option_id']);
                    if ($option) {
                        $total += $option->score;
                    }
                }
            }

            $history->update(['sum_score' => $total]);

            DB::commit();

            return response()->json([
                'message' => 'Screening scoring submitted',
                'sum_score' => $total,
                'history_id' => $history->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal submit',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
