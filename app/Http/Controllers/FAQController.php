<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FAQController extends Controller
{
    public function index()
    {
        $faqs = FAQ::all();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'FAQs retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $faqs,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);

        $faq = FAQ::create([
            'id' => Str::uuid(),
            'question' => $request->question,
            'answer' => $request->answer,
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'FAQ created successfully',
                'statusCode' => 201,
            ],
            'data' => $faq,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $faq = FAQ::findOrFail($id);

        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);

        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'FAQ updated successfully',
                'statusCode' => 200,
            ],
            'data' => $faq,
        ]);
    }

    public function destroy($id)
    {
        $faq = FAQ::findOrFail($id);
        $faq->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'FAQ deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }

    public function show($id)
    {
        $faq = FAQ::findOrFail($id);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'FAQ detail retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $faq,
        ]);
    }
}
