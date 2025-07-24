<?php

namespace App\Http\Controllers;

use App\Models\AdminScreeningDASSHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ScreeningDASSReportController extends Controller
{
    public function getAllScreeningHistories()
    {
        $histories = AdminScreeningDASSHistory::with('user')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'history_id' => $item->id,
                    'submitted_at' => $item->created_at->toDateTimeString(),
                    'user_id' => $item->user->id,
                    'name' => $item->user->name,
                    'email' => $item->user->email,
                ];
            });

        return response()->json([
            'message' => 'Berhasil mengambil seluruh riwayat screening DASS',
            'data' => $histories,
        ]);
    }

    public function deleteHistory($id)
    {
        $history = AdminScreeningDASSHistory::find($id);

        if (!$history) {
            return response()->json([
                'message' => 'Data screening DASS tidak ditemukan.',
            ], 404);
        }

        $history->delete();

        return response()->json([
            'message' => 'Data screening DASS berhasil dihapus.',
        ], 200);
    }
}
