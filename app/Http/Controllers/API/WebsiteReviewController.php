<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebsiteReview;
use Illuminate\Support\Facades\Auth;

class WebsiteReviewController extends Controller{




    
    public function store(Request $request)
    {
        \Log::info('AUTH USER ID:', ['id' => auth()->id()]);
    
        $request->validate([
            'answers'    => 'required|array|size:10',
            'answers.*'  => 'required|integer|min:1|max:5',
            'suggestion' => 'nullable|string',
            'email'      => 'nullable|email', // <- email diperlakukan opsional seperti suggestion
        ]);
    
        $review = WebsiteReview::create([
            // tetap gunakan implementasi user_id kamu saat ini agar fitur lain aman
            'user_id'    => '86255787-dcc9-47bc-814b-e1ed1aa70af1', // ganti jika perlu
            'answers'    => $request->input('answers'),
            'suggestion' => $request->input('suggestion'),
            'email'      => $request->input('email'),
        ]);
    
        return response()->json([
            'message' => 'Ulasan berhasil disimpan.',
            'data'    => $review,
        ], 201);
    }
    
    
    

    public function index()
    {
        $reviews = WebsiteReview::with('user')->latest()->get();

        return response()->json($reviews);
    }
}
