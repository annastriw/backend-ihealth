<?php

namespace App\Http\Controllers;

use App\Models\WebsiteReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebsiteReviewController extends Controller
{
    /**
     * Simpan ulasan website baru untuk user yang sedang login.
     */
    public function store(Request $request)
    {
        \Log::info('AUTH USER ID:', ['id' => Auth::id()]);

        $request->validate([
            'answers'    => 'required|array|size:10',
            'answers.*'  => 'required|integer|min:1|max:5',
            'suggestion' => 'nullable|string',
        ]);

        $review = WebsiteReview::create([
            'user_id'    => Auth::id(),
            'answers'    => $request->input('answers'),
            'suggestion' => $request->input('suggestion'),
        ]);

        return response()->json([
            'message' => 'Ulasan berhasil disimpan.',
            'data'    => $review->load('user'),
        ], 201);
    }
}
