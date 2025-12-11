<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FeedbackMessage;
use Illuminate\Http\Request;

class AdminFeedbackMessageController extends Controller
{
    public function index()
    {
        $feedbacks = FeedbackMessage::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($f) {
                return [
                    'id' => $f->id,
                    'nama_pasien' => $f->user?->name ?? '-',
                    'tanggal' => $f->created_at->format('Y-m-d H:i'),
                    'preview' => mb_substr($f->message, 0, 50) . (strlen($f->message) > 50 ? '...' : ''),
                    'message_full' => $f->message,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $feedbacks,
        ]);
    }
}
