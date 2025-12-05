<?php

namespace App\Http\Controllers;

use App\Models\PersonalInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PersonalInformationController extends Controller
{
    /**
     * ✅ MENAMPILKAN SEMUA DATA (ADMIN)
     */
    public function index()
    {
        $personalInformations = PersonalInformation::with('user')->latest()->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information list fetched successfully',
                'statusCode' => 200
            ],
            'data' => $personalInformations,
        ]);
    }

    /**
     * ✅ STORE DATA (USER LOGIN) - ANTI 500
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'statusCode' => 401
                ]
            ], 401);
        }

        // ✅ CEGAR DOUBLE INPUT
        if (PersonalInformation::where('user_id', $user->id)->exists()) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Personal Information already exists',
                    'statusCode' => 409
                ]
            ], 409);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'place_of_birth' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'age' => 'required|string|max:255',
            'gender' => 'required|string|max:1', // "0" | "1"
            'work' => 'required|string|max:255',
            'is_married' => 'required|boolean',
            'last_education' => 'required|string|max:255',
            'origin_disease' => 'required|string|max:255',
            'disease_duration' => 'required|string|max:255',
            'history_therapy' => 'required|string',
        ]);

        try {
            $personalInformation = PersonalInformation::create([
                'user_id' => $user->id,
                ...$validated
            ]);

            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'message' => 'Personal Information created successfully',
                    'statusCode' => 201
                ],
                'data' => $personalInformation,
            ], 201);

        } catch (\Throwable $th) {
            Log::error('STORE PersonalInformation ERROR: ' . $th->getMessage());

            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Failed to create Personal Information',
                    'statusCode' => 500
                ]
            ], 500);
        }
    }

    /**
     * ✅ DETAIL BERDASARKAN ID
     */
    public function show($id)
    {
        $personalInformation = PersonalInformation::with('user')->findOrFail($id);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $personalInformation,
        ]);
    }

    /**
     * ✅ UPDATE DATA USER LOGIN
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $personalInformation = PersonalInformation::where('user_id', $user->id)->first();

        if (!$personalInformation) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Personal Information not found for this user',
                    'statusCode' => 404
                ]
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'place_of_birth' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date',
            'age' => 'sometimes|string|max:255',
            'gender' => 'sometimes|string|max:1',
            'work' => 'sometimes|string|max:255',
            'is_married' => 'sometimes|boolean',
            'last_education' => 'sometimes|string|max:255',
            'origin_disease' => 'sometimes|string|max:255',
            'disease_duration' => 'sometimes|string|max:255',
            'history_therapy' => 'sometimes|string',
        ]);

        $personalInformation->update($validated);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information updated successfully',
                'statusCode' => 200,
            ],
            'data' => $personalInformation,
        ]);
    }

    /**
     * ✅ DELETE BERDASARKAN ID
     */
    public function destroy($id)
    {
        $personalInformation = PersonalInformation::findOrFail($id);
        $personalInformation->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information deleted successfully',
                'statusCode' => 200,
            ],
        ]);
    }

    /**
     * ✅ AMBIL DATA BERDASARKAN USER ID
     */
    public function getPersonalInformationByUserId($user_id)
    {
        $personalInformation = PersonalInformation::where('user_id', $user_id)->first();

        if (!$personalInformation) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Personal Information not found for this user',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $personalInformation,
        ]);
    }

    /**
     * ✅ CEK APAKAH USER SUDAH MENGISI DATA
     */
    public function checkUserPersonalInformation(Request $request)
    {
        $user = $request->user();

        $isCompleted = PersonalInformation::where('user_id', $user->id)->exists();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information check completed',
                'statusCode' => 200,
            ],
            'data' => [
                'is_completed' => $isCompleted,
            ],
        ]);
    }

    /**
     * ✅ TAMPILKAN DATA USER LOGIN
     */
    public function showAuthenticatedUserPersonalInformation(Request $request)
    {
        $user = $request->user();

        $personalInformation = PersonalInformation::where('user_id', $user->id)->first();

        if (!$personalInformation) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Personal Information not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information fetched successfully',
                'statusCode' => 200,
            ],
            'data' => $personalInformation,
        ]);
    }
}
