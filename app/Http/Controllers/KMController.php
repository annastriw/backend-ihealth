<?php

namespace App\Http\Controllers;

use App\Models\KM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KMController extends Controller
{
    public function index()
    {
        $kms = KM::all();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'List of KMs retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $kms,
        ]);
    }

    public function getBySubModule($sub_module_id)
    {
        $kms = KM::where('sub_module_id', $sub_module_id)->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'KM retrieved successfully for the given sub_module_id',
                'statusCode' => 200,
            ],
            'data' => $kms,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_module_id' => 'required|uuid|exists:sub_modules,id',
            'file_path' => 'required|file',
            'name' => 'required|string',
            'content' => 'required|string',
            'video_url' => 'required|string',
        ]);

        $path = $request->file('file_path')->store('km_materials', 'public');

        $km = KM::create([
            'id' => Str::uuid(),
            'sub_module_id' => $request->sub_module_id,
            'file_path' => $path,
            'name' => $request->name,
            'content' => $request->content,
            'video_url' => $request->video_url,
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'KM created successfully',
                'statusCode' => 201,
            ],
            'data' => $km,
        ], 201);
    }

    public function show($id)
    {
        $km = KM::find($id);

        if (!$km) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'KM not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'KM details retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $km,
        ]);
    }

    public function update(Request $request, $id)
    {
        $km = KM::find($id);

        if (!$km) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'KM not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $request->validate([
            'sub_module_id' => 'nullable|uuid',
            'file' => 'nullable|file',
            'name' => 'nullable|string',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            if ($km->file_path && Storage::disk('public')->exists($km->file_path)) {
                Storage::disk('public')->delete($km->file_path);
            }

            $km->file_path = $request->file('file')->store('km_materials', 'public');
        }

        $km->sub_module_id = $request->sub_module_id ?? $km->sub_module_id;
        $km->name = $request->name ?? $km->name;
        $km->content = $request->content ?? $km->content;
        $km->video_url = $request->video_url ?? $km->video_url;

        $km->save();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'KM updated successfully',
                'statusCode' => 200,
            ],
            'data' => $km,
        ]);
    }

    public function destroy($id)
    {
        $km = KM::find($id);

        if (!$km) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'KM not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $km->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'KM deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }
}
