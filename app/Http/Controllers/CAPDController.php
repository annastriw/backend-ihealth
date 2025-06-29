<?php

namespace App\Http\Controllers;

use App\Models\CAPD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CAPDController extends Controller
{
    public function index()
    {
        $capds = CAPD::all();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'List of CAPDs retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $capds,
        ]);
    }

    public function getBySubModule($sub_module_id)
    {
        $capds = CAPD::where('sub_module_id', $sub_module_id)->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'CAPDs retrieved successfully for the given sub_module_id',
                'statusCode' => 200,
            ],
            'data' => $capds,
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

        $path = $request->file('file_path')->store('capd_materials', 'public');

        $capd = CAPD::create([
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
                'message' => 'CAPD created successfully',
                'statusCode' => 201,
            ],
            'data' => $capd,
        ], 201);
    }

    public function show($id)
    {
        $capd = CAPD::find($id);

        if (!$capd) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'CAPD not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'CAPD details retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $capd,
        ]);
    }

    public function update(Request $request, $id)
    {
        $capd = CAPD::find($id);

        if (!$capd) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'capd not found',
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
            if ($capd->file_path && Storage::disk('public')->exists($capd->file_path)) {
                Storage::disk('public')->delete($capd->file_path);
            }

            $capd->file_path = $request->file('file')->store('capd_materials', 'public');
        }

        $capd->sub_module_id = $request->sub_module_id ?? $capd->sub_module_id;
        $capd->name = $request->name ?? $capd->name;
        $capd->content = $request->content ?? $capd->content;
        $capd->video_url = $request->video_url ?? $capd->video_url;

        $capd->save();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'CAPD updated successfully',
                'statusCode' => 200,
            ],
            'data' => $capd,
        ]);
    }

    public function destroy($id)
    {
        $capd = CAPD::find($id);

        if (!$capd) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'CAPD not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $capd->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'CAPD deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }
}
