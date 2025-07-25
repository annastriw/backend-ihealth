<?php

namespace App\Http\Controllers;

use App\Models\Screening;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public function index(Request $request)
{
    $query = Screening::query();

    if ($request->has('type')) {
        $type = $request->query('type');
        $query->where('type', $type);
    }

    $screenings = $query->get();

    return response()->json([
        'meta' => ['status' => 'success'],
        'data' => $screenings,
    ]);
}


    public function store(Request $request)
{
    $request->validate([
        'question_set_id' => 'required|exists:question_sets,id',
        'name' => 'required|string|max:255',
        'type' => 'required|string|in:HT,DM,KM', // ✅ tambahkan validasi type
    ]);

    try {
        $screening = Screening::create([
            'question_set_id' => $request->question_set_id,
            'name' => $request->name,
            'type' => $request->type, // ✅ tambahkan field type
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Screening created successfully',
                'statusCode' => 201
            ],
            'data' => $screening,
        ], 201);
    } catch (\Throwable $th) {
        return response()->json([
            'meta' => [
                'status' => 'error',
                'message' => $th->getMessage(),
                'statusCode' => 500
            ]
        ], 500);
    }
}


    public function show($id)
    {
        $screening = Screening::with([
            'questionSet.questions' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'questionSet.questions.options' => function ($query) {
                $query->orderBy('option_index', 'asc');
            }

        ])->findOrFail($id);

        $questions = $screening->questionSet->questions->map(function ($question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'options' => $question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->option_text,
                    ];
                }),
            ];
        });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Screening fetched successfully',
                'statusCode' => 200,
            ],
            'data' => [
                'id' => $screening->id,
                'name' => $screening->name,
                'question_set_id' => $screening->question_set_id,
                'questions' => $questions,
            ],
        ]);
    }


    public function update(Request $request, $id)
    {
        $screening = Screening::findOrFail($id);

        $request->validate([
    'question_set_id' => 'sometimes|exists:question_sets,id',
    'name' => 'sometimes|string',
    'type' => 'sometimes|string|in:HT,DM,KM', // ✅ tambahkan ini
]);


        $screening->update($request->only('name', 'question_set_id', 'type'));

        return response()->json([
            'meta' => ['status' => 'success', 'message' => 'Screening updated'],
            'data' => $screening,
        ]);
    }

    public function destroy($id)
    {
        $screening = Screening::findOrFail($id);
        $screening->delete();

        return response()->json([
            'meta' => ['status' => 'success', 'message' => 'Screening deleted'],
        ]);
    }
}
