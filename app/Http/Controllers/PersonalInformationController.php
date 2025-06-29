<?php

namespace App\Http\Controllers;

use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Http\Request;


class PersonalInformationController extends Controller
{
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

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'place_of_birth' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'age' => 'required|string',
            'work' => 'required|string',
            'gender' => 'required|string|max:10',
            'is_married' => 'required|boolean',
            'last_education' => 'required|string|max:255',
            'origin_disease' => 'required|string|max:255',
            'patient_type' => 'required|string|max:255',
            'disease_duration' => 'required|string|max:255',
            'history_therapy' => 'required|string|max:255',
        ]);

        try {
            $personalInformation = PersonalInformation::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'place_of_birth' => $request->place_of_birth,
                'date_of_birth' => $request->date_of_birth,
                'age' => $request->age,
                'gender' => $request->gender,
                'work' => $request->work,
                'is_married' => $request->is_married,
                'last_education' => $request->last_education,
                'origin_disease' => $request->origin_disease,
                'patient_type' => $request->patient_type,
                'disease_duration' => $request->disease_duration,
                'dialisis_duration' => $request->dialisis_duration,
                'history_therapy' => $request->history_therapy,
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
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => $th->getMessage(),
                    'statusCode' => 500
                ]
            ], 500);
        }
    }

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

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'place_of_birth' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date',
            'age' => 'sometimes|string',
            'gender' => 'sometimes|string|max:10',
            'is_married' => 'sometimes|boolean',
            'last_education' => 'sometimes|string|max:255',
            'origin_disease' => 'sometimes|string|max:255',
            'patient_type' => 'sometimes|string|max:255',
            'disease_duration' => 'sometimes|string|max:255',
            'history_therapy' => 'sometimes|string|max:255',
        ]);

        $personalInformation->update($request->all());

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Personal Information updated successfully',
                'statusCode' => 200,
            ],
            'data' => $personalInformation,
        ]);
    }

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
