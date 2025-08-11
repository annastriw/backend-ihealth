<?php

namespace App\Http\Controllers;

use App\Models\UserHistoryScreening;

class UserHistoryScreeningController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $histories = UserHistoryScreening::with('screening')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil semua history screening',
            'data' => $histories,
        ]);
    }

    public function getByScreeningId($screeningId)
    {
        $histories = UserHistoryScreening::with([
            'user',
            'screening.questionSet.questions' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'screening.questionSet.questions.options' => function ($query) {
                $query->orderBy('option_index', 'asc');
            },
        ])
            ->where('screening_id', $screeningId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($histories->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada history screening untuk screening_id tersebut',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil history screening berdasarkan screening_id',
            'data' => $histories,
        ]);
    }

    public function getAllHistory()
    {
        $histories = UserHistoryScreening::with(['screening', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil semua history screening (semua user)',
            'data' => $histories,
        ]);
    }

    public function show($id)
    {

        $history = UserHistoryScreening::with(['user', 'answer.question.options', 'answer.selectedOption',])
            ->where('id', $id)
            ->first();

        if (!$history) {
            return response()->json([
                'message' => 'History screening tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail history screening',
            'data' => [
                'id' => $history->id,
                'answer' => $history->answer->map(function ($answer) {
                    return [
                        'id' => $answer->question->id,
                        'question' => $answer->question->question_text,
                        'options' => $answer->question->options->map(fn($opt) => [
                            'id' => $opt->id,
                            'text' => $opt->option_text,
                        ]),
                        'selected_option' => [
                            'id' => $answer->selectedOption?->id,
                            'text' => $answer->selectedOption?->option_text,
                        ],
                    ];
                }),
                'user' => [
                    'id' => $history->user->id,
                    'name' => $history->user->name,
                ],
                'created_at' => $history->created_at,
            ],
        ]);
    }

    public function destroy($id)
    {
        $history = UserHistoryScreening::find($id);

        if (!$history) {
            return response()->json([
                'message' => 'History screening tidak ditemukan',
            ], 404);
        }

        $history->delete();

        return response()->json([
            'message' => 'History screening berhasil dihapus',
        ]);
    }
}
