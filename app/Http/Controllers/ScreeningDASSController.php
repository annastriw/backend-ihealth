<?php

namespace App\Http\Controllers;

use App\Models\ScreeningDASSHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
