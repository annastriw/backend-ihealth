<?php

namespace App\Http\Controllers;

use App\Models\SusResponse;
use Illuminate\Http\Request;

class AdminSusResponseController extends Controller
{
    /**
     * LIST SUS (Admin)
     * Kolom: Nama Pasien | Tanggal | SUS Score | Q1–Q10 Converted
     */
    public function index(Request $request)
    {
        $responses = SusResponse::with(['user', 'details'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Mapping untuk tabel admin
        $formatted = $responses->map(function ($item) {
            // Siapkan array untuk 10 item converted
            $converted = [];
            for ($i = 1; $i <= 10; $i++) {
                $converted["q{$i}"] = optional(
                    $item->details->firstWhere('question_id', $i)
                )->answer_converted ?? null;
            }

            return array_merge([
                'id'           => $item->id,
                'patient_name' => $item->user->name ?? '-',
                'date'         => $item->created_at->format('Y-m-d'),
                'sus_score'    => $item->total_score,
            ], $converted);
        });

        return response()->json([
            'status' => 'success',
            'data'   => $formatted,
        ]);
    }

    /**
     * DETAIL SUS (Admin)
     * Menampilkan raw + converted untuk semua 10 pertanyaan
     */
    public function show($id)
    {
        $response = SusResponse::with(['user', 'details'])->findOrFail($id);

        // Build tabel Q1–Q10 (raw & converted)
        $detailTable = [];
        foreach (range(1, 10) as $i) {
            $d = $response->details->firstWhere('question_id', $i);
            $detailTable[] = [
                'question_id'      => $i,
                'answer_raw'       => $d->answer_raw ?? null,
                'answer_converted' => $d->answer_converted ?? null
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'             => $response->id,
                'patient_name'   => $response->user->name,
                'date'           => $response->created_at->format('Y-m-d H:i:s'),
                'total_score'    => $response->total_score,
                'interpretation' => $response->interpretation,
                'details'        => $detailTable
            ]
        ]);
    }
}
