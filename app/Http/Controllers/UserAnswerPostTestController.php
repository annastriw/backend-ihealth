<?php

namespace App\Http\Controllers;

use App\Models\UserAnswerPostTest;
use App\Models\UserHistoryPostTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserAnswerPostTestController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'post_test_id' => 'required|uuid',
            'answers' => 'required|array|min:1',
            'answers.*.selected_option_id' => 'nullable|uuid|exists:options,id',
            'answers.*.question_id' => 'nullable|uuid|exists:questions,id',
            'answers.*.answer_text' => 'nullable|string',
        ]);

        $user = auth()->user();

        try {
            // Ambil semua selected_option_id yang tidak null
            $selectedOptionIds = collect($request->answers)
                ->pluck('selected_option_id')
                ->filter()
                ->unique();

            // Ambil semua options sekaligus, di-index berdasarkan ID untuk lookup cepat
            $options = \App\Models\Option::whereIn('id', $selectedOptionIds)
                ->get()
                ->keyBy('id');

            // Buat history awal dengan skor 0, akan di-update nanti
            $history = UserHistoryPostTest::create([
                'user_id' => $user->id,
                'post_test_id' => $request->post_test_id,
                'sum_score' => 0, // placeholder
            ]);

            $totalScore = 0;
            $answeredCount = 0;

            foreach ($request->answers as $answer) {
                // Simpan jawaban user
                UserAnswerPostTest::create([
                    'user_id' => $user->id,
                    'user_history_post_test_id' => $history->id,
                    'selected_option_id' => $answer['selected_option_id'] ?? null,
                    'question_id' => $answer['question_id'],
                    'answer_text' => $answer['answer_text'] ?? null,
                    'answered_at' => now(),
                ]);

                // Hitung skor jika option ditemukan
                $selectedOptionId = $answer['selected_option_id'] ?? null;
                if ($selectedOptionId && $options->has($selectedOptionId)) {
                    $totalScore += $options[$selectedOptionId]->score ?? 0;
                    $answeredCount++;
                }
            }

            // Hitung rata-rata
            $sumScore = $totalScore;

            // Update skor rata-rata ke history
            $history->update([
                'sum_score' => $sumScore
            ]);

            return response()->json([
                'message' => 'Post Test submitted successfully',
                'history_id' => $history->id,
                'sum_score' => $sumScore,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Submit Post Test error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat submit post-test',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
