<?php

namespace App\Http\Controllers;

use App\Models\ScreeningScoring;
use Illuminate\Http\Request;

class ScreeningScoringController extends Controller
{
    public function index()
    {
        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => ScreeningScoring::with('questionSet')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:HT,DM',
            'question_set_id' => 'required|exists:question_sets,id',
        ]);

        $screening = ScreeningScoring::create($request->only([
            'name',
            'type',
            'question_set_id'
        ]));

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Screening scoring created'
            ],
            'data' => $screening
        ], 201);
    }

    public function show($id)
    {
        $screening = ScreeningScoring::with([
            'questionSet.questions.options'
        ])->find($id);

        if (!$screening) {
            return response()->json([
                'meta' => ['status' => 'error', 'message' => 'Screening scoring not found'],
            ], 404);
        }

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => $screening
        ]);
    }

    public function update(Request $request, $id)
    {
        $screening = ScreeningScoring::find($id);

        if (!$screening) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:HT,DM',
            'question_set_id' => 'sometimes|exists:question_sets,id',
        ]);

        $screening->update($request->only(['name', 'type', 'question_set_id']));

        return response()->json([
            'meta' => ['status' => 'success', 'message' => 'Screening scoring updated'],
            'data' => $screening
        ]);
    }

    public function destroy($id)
    {
        $screening = ScreeningScoring::find($id);

        if (!$screening) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $screening->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
