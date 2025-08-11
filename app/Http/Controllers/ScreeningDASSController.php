<?php

namespace App\Http\Controllers;

use App\Models\ScreeningDASSHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DASSHelper;
use App\Constants\DASSQuestions;

class ScreeningDASSController extends Controller
{
    public function latest(Request $request)
{
    $user = Auth::user(); // ← dari token

    $latest = ScreeningDASSHistory::where('user_id', $user->id)
        ->latest('created_at')
        ->first();

    return response()->json([
        'meta' => ['status' => 'success'],
        'data' => [
            'id' => $latest->id ?? null,
            'latest_submitted_at' => $latest?->created_at,
        ],
    ]);
}

    public function submit(Request $request)
    {
        $validator = validator($request->all(), [
            'user_id' => 'required|uuid',
            'answers' => 'required|array|size:21',
            'answers.*.question_id' => 'required|integer|between:1,21',
            'answers.*.category' => 'required|string|in:Depresi,Kecemasan,Stres',
            'answers.*.score' => 'required|integer|min:0|max:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'meta' => ['status' => 'error', 'message' => 'Validation failed'],
                'errors' => $validator->errors(),
            ], 422);
        }

        $history = ScreeningDASSHistory::create([
            'user_id' => $request->user_id,
            'answers' => $request->answers,
        ]);

        return response()->json([
            'meta' => ['status' => 'success', 'message' => 'Screening DASS submitted'],
            'data' => [
                'id' => $history->id,
                'submitted_at' => $history->created_at,
            ],
        ], 201);
    }

    public function show($id, Request $request)
    {
        $history = ScreeningDASSHistory::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Tidak perlu json_decode jika sudah cast di model
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
            $interpretation[$category] = DASSHelper::interpret($score, $category);
        }

        // Ambil deskripsi dan soal
        $descriptions = DASSHelper::descriptions();
        $questions = DASSQuestions::all();

        // Gabungkan jawaban + teks soal
        $answer_details = array_map(function ($answer) use ($questions) {
            return [
                'question_id' => $answer['question_id'],
                'category' => $answer['category'],
                'score' => $answer['score'],
                'question_text' => $questions[$answer['question_id']]['text'] ?? '-',
            ];
        }, $answers);

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => [
                'id' => $history->id,
                'created_at' => $history->created_at,
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

    public function index(Request $request)
    {
        $user = $request->user();

        $histories = ScreeningDASSHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'created_at' => $item->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => $histories,
        ]);
    }

    public function showAdmin($id)
    {
        $history = ScreeningDASSHistory::with('user')->findOrFail($id);

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
            $interpretation[$category] = DASSHelper::interpret($score, $category);
        }

        $descriptions = DASSHelper::descriptions();
        $questions = DASSQuestions::all();

        $answer_details = array_map(function ($answer) use ($questions) {
            return [
                'question_id' => $answer['question_id'],
                'category' => $answer['category'],
                'score' => $answer['score'],
                'question_text' => $questions[$answer['question_id']]['text'] ?? '-',
            ];
        }, $answers);

        return response()->json([
            'meta' => ['status' => 'success'],
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
