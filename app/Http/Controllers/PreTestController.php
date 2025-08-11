<?php

namespace App\Http\Controllers;

use App\Models\PreTest;
use Illuminate\Http\Request;

class PreTestController extends Controller
{
    public function index()
    {
        $preTests = PreTest::with('subModule.module')->get();

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => $preTests,
        ]);
    }

    public function getBySubModule($sub_module_id)
    {
        $preTests = PreTest::where('sub_module_id', $sub_module_id)->get();

        return response()->json([
            'meta' => ['status' => 'success'],
            'data' => $preTests,
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
            $preTest = PreTest::create([
                'sub_module_id' => $request->sub_module_id,
                'question_set_id' => $request->question_set_id,
                'name' => $request->name,
            ]);

            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'message' => 'PreTest created successfully',
                    'statusCode' => 201
                ],
                'data' => $preTest,
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
        $preTest = PreTest::with([
            'questionSet.questions' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'questionSet.questions.options' => function ($query) {
                $query->orderBy('option_index', 'asc');
            },
            'subModule.module'
        ])->findOrFail($id);

        $questions = $preTest->questionSet->questions->map(function ($question) {
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
            'id' => $preTest->id,
            'name' => $preTest->name,
            'submodule' => [
                'id' => $preTest->subModule->id,
                'name' => $preTest->subModule->name,
            ],
            'question_set_id' => $preTest->question_set_id,
            'questions' => $questions,
        ];

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'PreTest fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $data,
        ]);
    }

    public function update(Request $request, $id)
    {
        $preTest = PreTest::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'sub_module_id' => 'sometimes|exists:sub_modules,id',
            'question_set_id' => 'sometimes|exists:question_sets,id',
        ]);

        $preTest->update($request->only(['name', 'sub_module_id', 'question_set_id']));

        return response()->json([
            'meta' => ['status' => 'success', 'message' => 'PreTest updated'],
            'data' => $preTest,
        ]);
    }

    public function destroy($id)
    {
        $preTest = PreTest::findOrFail($id);
        $preTest->delete();

        return response()->json([
            'meta' => ['status' => 'success', 'message' => 'PreTest deleted'],
        ]);
    }
}
