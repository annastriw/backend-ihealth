<?php

namespace App\Http\Controllers;

use App\Models\UserHistoryPostTest;
use Illuminate\Http\Request;

class UserHistoryPostTestController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $histories = UserHistoryPostTest::with('postTest')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil semua history post test',
            'data' => $histories,
        ]);
    }

    public function getByPostTestId($postTestId)
    {
        $histories = UserHistoryPostTest::with(['user', 'postTest'])
            ->where('post_test_id', $postTestId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($histories->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada history post test untuk post_test_id tersebut',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil history post test berdasarkan post_test_id',
            'data' => $histories,
        ]);
    }

    public function getAllHistory()
    {
        $histories = UserHistoryPostTest::with(['postTest', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil semua history post test (semua user)',
            'data' => $histories,
        ]);
    }

    public function show($id)
    {
        $history = UserHistoryPostTest::with(['user', 'answer.question.options', 'answer.selectedOption'])
            ->where('id', $id)
            ->first();

        if (!$history) {
            return response()->json([
                'message' => 'History post test tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail history post test',
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
        $history = UserHistoryPostTest::find($id);

        if (!$history) {
            return response()->json([
                'message' => 'History post test tidak ditemukan',
            ], 404);
        }

        $history->delete();

        return response()->json([
            'message' => 'History post test berhasil dihapus',
        ]);
    }
}
