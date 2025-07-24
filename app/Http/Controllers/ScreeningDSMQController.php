<?php

namespace App\Http\Controllers;

use App\Models\ScreeningDSMQHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ScreeningDSMQController extends Controller
{
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,id',
            'answers' => 'required|array|size:16',
            'answers.*.question_id' => 'required|integer|min:1|max:16',
            'answers.*.score' => 'required|integer|min:0|max:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        $answers = $request->answers;
        $totalScore = collect($answers)->sum('score');

        if ($totalScore >= 32) {
            $category = 'Baik';
        } elseif ($totalScore >= 16) {
            $category = 'Cukup';
        } else {
            $category = 'Buruk';
        }

        $history = ScreeningDSMQHistory::create([
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

        $latest = ScreeningDSMQHistory::where('user_id', $user->id)
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

        $history = ScreeningDSMQHistory::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$history) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $history->id,
                'created_at' => $history->created_at,
                'score' => $history->total_score,
                'interpretation' => $history->category,
                'description' => $this->getDescription($history->category),
                'answers' => $history->answers,
            ],
        ]);
    }

    private function getDescription(string $category): string
    {
        return match ($category) {
            'Buruk' => 'Manajemen diri diabetes Anda tergolong buruk. Segera konsultasikan dengan tenaga medis untuk mendapatkan bantuan lebih lanjut.',
            'Cukup' => 'Manajemen diri Anda cukup baik. Namun masih ada ruang untuk perbaikan.',
            'Baik' => 'Manajemen diri Anda terhadap diabetes sudah sangat baik. Pertahankan kebiasaan sehat Anda.',
            default => 'Deskripsi tidak tersedia.',
        };
    }

    public function getAllByUser(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $histories = ScreeningDSMQHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get(['id', 'created_at']);

        return response()->json([
            'data' => $histories,
        ]);
    }

    public function getAllForAdmin(): JsonResponse
    {
        $histories = ScreeningDSMQHistory::with('user')
            ->latest()
            ->get()
            ->map(function ($history) {
                return [
                    'history_id' => $history->id,
                    'user_id' => $history->user_id,
                    'name' => $history->user->name,
                    'score' => $history->total_score,
                    'category' => $history->category,
                    'submitted_at' => $history->created_at->toDateTimeString(),
                ];
            });

        return response()->json(['data' => $histories]);
    }

    public function deleteById(string $id): JsonResponse
    {
        $history = ScreeningDSMQHistory::findOrFail($id);
        $history->delete();

        return response()->json(['message' => 'Berhasil dihapus']);
    }

    public function getDetailForAdmin(string $id): JsonResponse
    {
        $history = ScreeningDSMQHistory::with('user')->findOrFail($id);

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
}
