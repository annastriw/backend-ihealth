<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SusResponse;
use App\Models\SusResponseDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SusAnalyticsController extends Controller
{
    public function index()
    {
        // =========================
        // BASIC AGGREGATE METRICS
        // =========================
        $totalResponses = SusResponse::count();
        $todayResponses = SusResponse::whereDate('created_at', Carbon::today())->count();
        $last30daysResponses = SusResponse::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        // =====================================
        // AVERAGE SUS SCORE & INTERPRETATION
        // =====================================
        $avgScore = SusResponse::avg('total_score') ?? 0;
        $avgScore = round($avgScore, 2);

        $interpretation = \App\Helpers\SusHelper::interpret($avgScore);

        // ============================
        // DAILY TIME SERIES (TREND)
        // ============================
        $trend = SusResponse::select(
                DB::raw('DATE(created_at) AS date'),
                DB::raw('COUNT(*) AS total'),
                DB::raw('AVG(total_score) AS avg_score')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'ASC')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->date,
                    'avg_sus' => round($row->avg_score, 2),
                    'count' => $row->total,
                ];
            });

        // ===================================
        // AVERAGE PER ITEM (1–10) RAW SCALE
        // ===================================
        $avgPerItem = [];

        for ($i = 1; $i <= 10; $i++) {
            $avg = SusResponseDetail::where('question_id', $i)->avg('answer_raw');
            $avgPerItem[] = round(floatval($avg ?? 0), 2);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'average_sus' => $avgScore,
                'interpretation' => $interpretation,
                'total_responses' => $totalResponses,
                'responses_today' => $todayResponses,
                'responses_30_days' => $last30daysResponses,
                'daily_trend' => $trend,
                'average_per_item' => $avgPerItem
            ]
        ]);
    }
}
