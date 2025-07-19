<?php

namespace App\Http\Controllers;

use App\Models\AdminScreeningDASSHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ScreeningDASSReportController extends Controller
{
    public function getAllScreeningHistories()
    {
        $histories = AdminScreeningDASSHistory::with('user')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'history_id' => $item->id,
                    'submitted_at' => $item->created_at->toDateTimeString(),
                    'user_id' => $item->user->id,
                    'name' => $item->user->name,
                    'email' => $item->user->email,
                ];
            });

        return response()->json([
            'message' => 'Berhasil mengambil seluruh riwayat screening DASS',
            'data' => $histories,
        ]);
    }

    public function deleteHistory($id)
    {
        $history = AdminScreeningDASSHistory::find($id);

        if (!$history) {
            return response()->json([
                'message' => 'Data screening DASS tidak ditemukan.',
            ], 404);
        }

        $history->delete();

        return response()->json([
            'message' => 'Data screening DASS berhasil dihapus.',
        ], 200);
    }

    public function getDetail($id)
    {
        $history = AdminScreeningDASSHistory::with('user')->findOrFail($id);

        $answers = $history->answers;

        // Hitung skor per kategori
        $scores = [
            'Depresi' => 0,
            'Kecemasan' => 0,
            'Stres' => 0,
        ];

        foreach ($answers as $answer) {
            if (isset($scores[$answer['category']])) {
                $scores[$answer['category']] += $answer['score'];
            }
        }

        // Interpretasi skor per kategori
        $interpretation = [];
        foreach ($scores as $category => $score) {
            $interpretation[$category] = \App\Helpers\DASSHelper::interpret($score, $category);
        }

        // Ambil deskripsi dan soal
        $descriptions = \App\Helpers\DASSHelper::descriptions();
        $questions = \App\Constants\DASSQuestions::all();

        $answer_details = array_map(function ($answer) use ($questions) {
            return [
                'question_id' => $answer['question_id'],
                'category' => $answer['category'],
                'score' => $answer['score'],
                'question_text' => $questions[$answer['question_id']]['text'] ?? '-',
            ];
        }, $answers);

        return response()->json([
            'message' => 'Berhasil mengambil detail screening DASS',
            'data' => [
                'id' => $history->id,
                'created_at' => $history->created_at,
                'user' => [
                    'id' => $history->user->id,
                    'name' => $history->user->name,
                    'email' => $history->user->email,
                ],
                'scores' => $scores,
                'interpretation' => $interpretation,
                'descriptions' => [
                    'Depresi' => $descriptions['Depresi'][$interpretation['Depresi']] ?? '',
                    'Kecemasan' => $descriptions['Kecemasan'][$interpretation['Kecemasan']] ?? '',
                    'Stres' => $descriptions['Stres'][$interpretation['Stres']] ?? '',
                ],
                'answers' => $answer_details,
            ]
        ]);
    }

}
