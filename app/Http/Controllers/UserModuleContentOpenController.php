<?php

namespace App\Http\Controllers;

use App\Models\UserModuleContentOpen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserModuleContentOpenController extends Controller
{
    /**
     * Menyimpan atau memperbarui waktu terakhir pengguna membuka konten modul.
     */
    public function updateLastOpened(string $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $record = UserModuleContentOpen::updateOrCreate(
            [
                'user_id' => $user->id,
                'module_content_id' => $id,
            ],
            [
                'last_opened_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Last opened timestamp updated successfully.',
            'data' => [
                'last_opened_at' => $record->last_opened_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Mengambil waktu terakhir pengguna membuka konten modul.
     */
    public function getLastOpened(string $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $record = UserModuleContentOpen::where('user_id', $user->id)
            ->where('module_content_id', $id)
            ->first();

        return response()->json([
            'last_opened_at' => optional($record?->last_opened_at)->toIso8601String(),
        ]);
    }
}
