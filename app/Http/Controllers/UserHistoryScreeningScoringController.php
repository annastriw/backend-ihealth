<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserHistoryScreeningScoring;

class UserHistoryScreeningScoringController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $histories = UserHistoryScreeningScoring::with('screening')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => $histories
        ]);
    }

    public function show($id)
    {
        $history = UserHistoryScreeningScoring::with([
            'screening',
            'answers.question.options',
            'answers.selectedOption',
            'user'
        ])->findOrFail($id);

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => [
                'id' => $history->id,
                'sum_score' => $history->sum_score,
                'created_at' => $history->created_at->toDateTimeString(),
                'user' => $history->user->only(['id', 'name']),
                'screening' => $history->screening->only(['id', 'name', 'type']),
                'answers' => $history->answers->map(function ($ans) {
                    return [
                        'question' => $ans->question->question_text,
                        'options' => $ans->question->options->map(fn($o) => [
                            'id' => $o->id,
                            'text' => $o->option_text,
                            'score' => $o->score
                        ]),
                        'selected_option' => $ans->selectedOption ? [
                            'id' => $ans->selectedOption->id,
                            'text' => $ans->selectedOption->option_text,
                            'score' => $ans->selectedOption->score,
                        ] : null,
                        'answer_text' => $ans->answer_text,
                    ];
                })
            ]
        ]);
    }

    public function getAllHistory()
    {
        $histories = UserHistoryScreeningScoring::with(['screening', 'user'])
            ->latest()->get();

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => $histories
        ]);
    }

    public function getByScreeningId($screeningScoringId)
    {
        $histories = UserHistoryScreeningScoring::with(['user', 'screening'])
            ->where('screening_scoring_id', $screeningScoringId)
            ->latest()
            ->get();

        if ($histories->isEmpty()) {
            return response()->json([
                'meta' => ['status' => 'error', 'message' => 'No history found'],
                'data' => [],
            ], 404);
        }

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => $histories
        ]);
    }

    public function destroy($id)
    {
        $history = UserHistoryScreeningScoring::find($id);

        if (!$history) {
            return response()->json(['message' => 'History not found'], 404);
        }

        $history->delete();

        return response()->json(['message' => 'History deleted']);
    }
}
