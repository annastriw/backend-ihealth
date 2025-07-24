<?php

namespace App\Http\Controllers;

use App\Models\ScreeningHSMBQHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ScreeningHSMBQController extends Controller
{
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,id',
            'answers' => 'required|array|size:40',
            'answers.*.question_id' => 'required|integer|min:1|max:40',
            'answers.*.score' => 'required|integer|min:0|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $answers = $request->answers;
        $totalScore = collect($answers)->sum('score');

        // Interpretasi berdasarkan skor total
        if ($totalScore >= 121) {
            $category = 'Baik';
        } elseif ($totalScore >= 81) {
            $category = 'Cukup';
        } else {
            $category = 'Kurang';
        }

        $history = ScreeningHSMBQHistory::create([
            'user_id' => $request->user_id,
            'answers' => $answers,
            'total_score' => $totalScore,
            'category' => $category,
        ]);

        return response()->json(['message' => 'Berhasil disimpan', 'data' => $history]);
    }

    public function getLatest(Request $request)
    {
        $user = $request->user();

        $latest = ScreeningHSMBQHistory::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        return response()->json([
            'data' => [
                'id' => $latest?->id,
                'latest_submitted_at' => $latest?->created_at,
            ],
        ]);
    }

    public function show(string $id, Request $request)
    {
        $user = $request->user();

        $history = ScreeningHSMBQHistory::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$history) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $history->id,
                'created_at' => $history->created_at,
                'score' => $history->total_score,
                'interpretation' => $history->category,
                'description' => $this->getInterpretationDescription($history->category),
                'answers' => $history->answers,
            ]
        ]);
    }

    private function getInterpretationDescription(string $category): string
    {
        return match ($category) {
            'Kurang' => 'Anda memiliki kualitas hidup hipertensi yang kurang. Perlu perbaikan gaya hidup dan pengelolaan kesehatan lebih lanjut.',
            'Cukup' => 'Anda memiliki kualitas hidup hipertensi yang cukup. Pertahankan dan tingkatkan gaya hidup sehat.',
            'Baik' => 'Anda memiliki kualitas hidup hipertensi yang baik. Teruskan pola hidup sehat Anda.',
            default => 'Interpretasi tidak tersedia.',
        };
    }

    public function getAllByUser(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $histories = ScreeningHSMBQHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get(['id', 'created_at']); // hanya ambil field yg diperlukan

        return response()->json([
            'data' => $histories,
        ]);
    }

    public function getAllForAdmin(): JsonResponse
    {
        $histories = ScreeningHSMBQHistory::with('user')
            ->latest()
            ->get()
            ->map(function ($history) {
                return [
                    'history_id' => $history->id,
                    'user_id' => $history->user_id,
                    'name' => $history->user->name,
                    'score' => $history->total_score,
                    'category' => $history->category,
                    'submitted_at' => $history->created_at->toDateTimeString(), // ✅ diganti
                ];
            });

        return response()->json(['data' => $histories]);
    }

    public function deleteById(string $id): JsonResponse
    {
        $history = ScreeningHSMBQHistory::findOrFail($id);
        $history->delete();

        return response()->json(['message' => 'Berhasil dihapus']);
    }

    public function getDetailForAdmin($id): JsonResponse
    {
        $history = ScreeningHSMBQHistory::with('user')->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $history->id,
                'created_at' => $history->created_at->toDateTimeString(),
                'score' => $history->total_score,
                'interpretation' => $history->category,
                'description' => $this->getDescription($history->category),
                'answers' => collect($history->answers)->map(function ($answer) {
                    return [
                        'question_id' => $answer['question_id'],
                        'score' => $answer['score'],
                    ];
                }),
            ],
        ]);
    }

    /**
     * Optional: konversi interpretasi ke deskripsi.
     */
    private function getDescription(string $category): string
    {
        return match ($category) {
            'Baik' => 'Anda telah memiliki manajemen diri hipertensi yang sangat baik. Pertahankan dan terus tingkatkan gaya hidup sehat Anda.',
            'Cukup' => 'Manajemen diri Anda terhadap hipertensi tergolong cukup. Perlu perhatian lebih untuk meningkatkan kepatuhan.',
            'Kurang' => 'Tingkat kepatuhan Anda terhadap manajemen diri hipertensi masih rendah. Segera konsultasikan dengan tenaga kesehatan.',
            default => 'Deskripsi tidak tersedia.',
        };
    }

}
