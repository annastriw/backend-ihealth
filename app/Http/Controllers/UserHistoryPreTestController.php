<?php

namespace App\Http\Controllers;

use App\Models\UserHistoryPreTest;

class UserHistoryPreTestController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $histories = UserHistoryPreTest::with('preTest')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil semua history pre test',
            'data' => $histories,
        ]);
    }

    public function getByPreTestId($preTestId)
    {
        $histories = UserHistoryPreTest::with(['user', 'preTest'])
            ->where('pre_test_id', $preTestId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($histories->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada history pre test untuk pre_test_id tersebut',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil history pre test berdasarkan pre_test_id',
            'data' => $histories,
        ]);
    }

    public function getAllHistory()
    {
        $histories = UserHistoryPreTest::with(['preTest', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil semua history pre-test (semua user)',
            'data' => $histories,
        ]);
    }

    public function show($id)
    {

        $history = UserHistoryPreTest::with(['user', 'answer.question.options', 'answer.selectedOption',])
            ->where('id', $id)
            ->first();

        if (!$history) {
            return response()->json([
                'message' => 'History pre test tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail history pre test',
            'data' => [
                'id' => $history->id,
                'sum_score' => $history->sum_score,
                'created_at' => $history->created_at,
                'answer' => $history->answer->map(function ($answer) {
                    return [
                        'id' => $answer->question->id,
                        'question' => $answer->question->question_text,
                        'options' => $answer->question->options->map(fn($opt) => [
                            'id' => $opt->id,
                            'text' => $opt->option_text,
                            'score' => $opt->score,
                        ]),
                        'selected_option' => [
                            'id' => $answer->selectedOption?->id,
                            'text' => $answer->selectedOption?->option_text,
                            'score' => $answer->selectionOption?->score,
                        ],
                    ];
                }),
                'user' => [
                    'id' => $history->user->id,
                    'name' => $history->user->name,
                ],
            ],
        ]);
    }

    public function destroy($id)
    {
        $history = UserHistoryPreTest::find($id);

        if (!$history) {
            return response()->json([
                'message' => 'History pre test tidak ditemukan',
            ], 404);
        }

        $history->delete();

        return response()->json([
            'message' => 'History pre test berhasil dihapus',
        ]);
    }
}
