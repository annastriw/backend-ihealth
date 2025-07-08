<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateUserLocationRequest;



class UserController extends Controller
{
    public function index()
    {
        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Daftar user berhasil diambil',
                'statusCode' => 200
            ],
            'data' => User::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
        ]);

        $user = User::create($validated);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'User berhasil dibuat',
                'statusCode' => 201
            ],
            'data' => $user
        ], 201);
    }

    public function getMedicalPersonals()
    {
        $users = User::where('role', 'medical_personal')->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Daftar user dengan role medical_personal berhasil diambil',
                'statusCode' => 200
            ],
            'data' => $users
        ]);
    }

    public function resetPassword($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'User tidak ditemukan',
                    'statusCode' => 404
                ],
                'data' => null
            ], 404);
        }

        $user->password = Hash::make('password');
        $user->save();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Password berhasil direset menjadi password',
                'statusCode' => 200
            ],
            'data' => $user
        ]);
    }

    public function show($id)
    {
        $user = User::with('historyScreening.screening')->find($id);

        if (!$user) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'User tidak ditemukan',
                    'statusCode' => 404
                ],
                'data' => null
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'User ditemukan',
                'statusCode' => 200
            ],
            'data' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'User tidak ditemukan',
                    'statusCode' => 404
                ],
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
        ]);

        $user->update($validated);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'User berhasil diperbarui',
                'statusCode' => 200
            ],
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'User tidak ditemukan',
                    'statusCode' => 404
                ],
                'data' => null
            ], 404);
        }

        $user->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'User berhasil dihapus',
                'statusCode' => 200
            ],
            'data' => null
        ]);
    }

    public function storeLocation(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'meta' => [
                'status' => 'error',
                'message' => 'User tidak terautentikasi',
                'statusCode' => 401
            ],
            'data' => null
        ], 401);
    }

    $validated = $request->validate([
        'latitude' => 'required|string',
        'longitude' => 'required|string',
        'address' => 'nullable|string',
        'kelurahan' => 'nullable|string',
        'rw' => 'nullable|string',
    ]);

    $user->latitude = $validated['latitude'];
    $user->longitude = $validated['longitude'];
    $user->address = $validated['address'] ?? null;
    $user->kelurahan = $validated['kelurahan'] ?? null;
    $user->rw = $validated['rw'] ?? null;
 
    $user->save();

    return response()->json([
        'meta' => [
            'status' => 'success',
            'message' => 'Lokasi user berhasil disimpan',
            'statusCode' => 201
        ],
        'data' => $user
    ], 201);
}

public function checkUserLocation(Request $request)
{
    $user = $request->user();

    $isCompleted = !empty($user->latitude) && !empty($user->longitude);

    return response()->json([
        'meta' => [
            'status' => 'success',
            'message' => 'User location check completed',
            'statusCode' => 200,
        ],
        'data' => [
            'is_completed' => $isCompleted,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'address' => $user->address,
        ],
    ]);
}

public function getAllUsersLocationInfo()
{
    $users = User::where('role', 'user')
        ->select('id', 'name', 'email', 'latitude', 'longitude', 'address', 'kelurahan', 'rw', 'disease_type')
        ->get();

    return response()->json([
        'meta' => [
            'status' => 'success',
            'message' => 'Data lokasi user dengan role patient berhasil diambil',
            'statusCode' => 200,
        ],
        'data' => $users
    ]);
}

public function getLocation(Request $request)
{
    $user = $request->user();

    return response()->json([
        'meta' => [
            'status' => 'success',
            'message' => 'Data lokasi user berhasil diambil',
            'statusCode' => 200,
        ],
        'data' => [
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'address' => $user->address,
            'kelurahan' => $user->kelurahan,
            'rw' => $user->rw,
        ],
    ]);
}

public function updateLocation(UpdateUserLocationRequest $request)
{
    $user = $request->user();

    $user->update($request->validated());

    return response()->json([
        'meta' => [
            'status' => 'success',
            'message' => 'Lokasi berhasil diperbarui',
            'statusCode' => 200,
        ],
        'data' => $user
    ]);
}

}
