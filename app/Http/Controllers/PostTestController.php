<?php

namespace App\Http\Controllers;

use App\Models\PostTest;
use Illuminate\Http\Request;

class PostTestController extends Controller
{
    public function index()
    {
        $postTests = PostTest::with('subModule.module')->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'List of Post Tests fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $postTests,
        ]);
    }

    public function getBySubModule($sub_module_id)
    {
        $postTests = PostTest::where('sub_module_id', $sub_module_id)->get();

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => $postTests,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_module_id' => 'required|exists:sub_modules,id',
            'question_set_id' => 'required|exists:question_sets,id',
            'name' => 'required|string|max:255',
        ]);

        try {
            $postTest = PostTest::create([
                'sub_module_id' => $request->sub_module_id,
                'question_set_id' => $request->question_set_id,
                'name' => $request->name,
            ]);

            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'message' => 'Post test created successfully',
                    'statusCode' => 201
                ],
                'data' => $postTest,
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
        $postTest = PostTest::with([
            'questionSet.questions' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'questionSet.questions.options' => function ($query) {
                $query->orderBy('option_index', 'asc');
            },
            'subModule.module'
        ])->findOrFail($id);

        $questions = $postTest->questionSet->questions->map(function ($question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'options' => $question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'option_text' => $option->option_text,
                        'score' => $option->score,
                    ];
                }),
            ];
        });

        $data = [
            'id' => $postTest->id,
            'name' => $postTest->name,
            'submodule' => [
                'id' => $postTest->subModule->id,
                'name' => $postTest->subModule->name,
            ],
            'question_set_id' => $postTest->question_set_id,
            'questions' => $questions,
        ];

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'PostTest fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $data,
        ]);
    }


    public function update(Request $request, $id)
    {
        $postTest = PostTest::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'sub_module_id' => 'sometimes|exists:sub_modules,id',
            'question_set_id' => 'sometimes|exists:question_sets,id',
        ]);

        $postTest->update($request->only(['name', 'sub_module_id', 'question_set_id']));

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'PostTest updated successfully',
                'statusCode' => 200,
            ],
            'data' => $postTest,
        ]);
    }

    public function destroy($id)
    {
        $postTest = PostTest::findOrFail($id);
        $postTest->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'PostTest deleted successfully',
                'statusCode' => 200,
            ],
        ]);
    }
}
