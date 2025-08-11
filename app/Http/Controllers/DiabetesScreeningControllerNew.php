<?php

namespace App\Http\Controllers;

use App\Models\DiabetesScreening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiabetesScreeningControllerNew extends Controller
{
    public function index()
    {
        $screenings = DiabetesScreening::all();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'All diabetes screenings fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $screenings,
        ]);
    }

    public function show($id)
    {
        $screening = DiabetesScreening::find($id);

        if (!$screening) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Diabetes Screening not found',
                    'statusCode' => 404,
                ]
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Diabetes Screening detail fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $screening,
        ]);
    }

    public function byUserId($userId)
    {
        $screenings = DiabetesScreening::where('user_id', $userId)->get();

        if ($screenings->isEmpty()) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'No diabetes screening data found for the given user ID',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Diabetes screenings fetched successfully for user ID',
                'statusCode' => 200,
            ],
            'data' => $screenings,
        ]);
    }


    public function destroy($id)
    {
        $screening = DiabetesScreening::find($id);

        if (!$screening) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Diabetes Screening not found',
                    'statusCode' => 404,
                ]
            ], 404);
        }

        $screening->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Diabetes Screening deleted successfully',
                'statusCode' => 200,
            ]
        ]);
    }
}
