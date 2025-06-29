<?php

namespace App\Http\Controllers;

use App\Models\QuestionSet;
use Illuminate\Http\Request;

class QuestionSetController extends Controller
{
    public function index()
    {
        $sets = QuestionSet::all();

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => $sets,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $set = QuestionSet::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'meta' => ['status' => 'success', 'message' => 'QuestionSet created'],
            'data' => $set,
        ]);
    }

    public function show($id)
    {
        $set = QuestionSet::with(['questions.options'])->findOrFail($id);

        $questions = $set->questions
            ->sortBy('created_at')
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'options' => $question->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'option_text' => $option->option_text,
                            'option_index' => $option->option_index,
                        ];
                    }),
                ];
            })->values();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Question Set fetched successfully',
                'statusCode' => 200,
            ],
            'data' => [
                'id' => $set->id,
                'name' => $set->name,
                'questions' => $questions,
            ],
        ]);
    }


    public function destroy($id)
    {
        $set = QuestionSet::findOrFail($id);
        $set->delete();

        return response()->json([
            'meta' => ['status' => 'success', 'message' => 'QuestionSet deleted'],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $set = QuestionSet::findOrFail($id);

        $set->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'QuestionSet updated',
            ],
            'data' => $set,
        ]);
    }
}
