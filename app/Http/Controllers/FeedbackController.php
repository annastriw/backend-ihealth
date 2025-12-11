<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeedbackRequest;
use App\Models\FeedbackMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    public function store(StoreFeedbackRequest $request)
    {
        $feedback = FeedbackMessage::create([
            'id' => Str::uuid(),
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $feedback
        ]);
    }
}
