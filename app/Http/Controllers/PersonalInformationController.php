<?php

namespace App\Http\Controllers;

use App\Models\PersonalInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PersonalInformationController extends Controller
{
    /**
     * Menampilkan seluruh data personal information
     */
    public function index()
    {
        $personalInformations = PersonalInformation::all();

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
     * Menyimpan data personal information baru (berdasarkan user login)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'place_of_birth' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'age' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
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
                'name' => $validated['name'],
                'place_of_birth' => $validated['place_of_birth'],
                'date_of_birth' => $validated['date_of_birth'],
                'age' => $validated['age'],
                'gender' => $validated['gender'],
                'work' => $validated['work'],
                'is_married' => $validated['is_married'],
                'last_education' => $validated['last_education'],
                'origin_disease' => $validated['origin_disease'],
                'disease_duration' => $validated['disease_duration'],
                'history_therapy' => $validated['history_therapy'],
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
            Log::error('Error saving personal information: ' . $th->getMessage());

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
     * Menampilkan detail berdasarkan ID
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
     * Update data berdasarkan user login
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
            'gender' => 'sometimes|string|max:255',
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
     * Menghapus data berdasarkan ID
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
     * Ambil data berdasarkan user_id
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
     * Cek apakah user sudah mengisi personal information
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
     * Menampilkan personal information milik user login
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
