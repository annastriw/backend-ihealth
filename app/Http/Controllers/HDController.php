<?php

namespace App\Http\Controllers;

use App\Models\HD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HDController extends Controller
{
    public function index()
    {
        $hds = HD::all();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'List of HDs retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $hds,
        ]);
    }

    public function getBySubModule($sub_module_id)
    {
        $hd = HD::where('sub_module_id', $sub_module_id)->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'HD retrieved successfully for the given sub_module_id',
                'statusCode' => 200,
            ],
            'data' => $hd,
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

        $path = $request->file('file_path')->store('hd_materials', 'public');

        $hd = HD::create([
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
                'message' => 'HD created successfully',
                'statusCode' => 201,
            ],
            'data' => $hd,
        ], 201);
    }

    public function show($id)
    {
        $hd = HD::find($id);

        if (!$hd) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'HD not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'HD details retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $hd,
        ]);
    }

    public function update(Request $request, $id)
    {
        $hd = HD::find($id);

        if (!$hd) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'HD not found',
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
            if ($hd->file_path && Storage::disk('public')->exists($hd->file_path)) {
                Storage::disk('public')->delete($hd->file_path);
            }

            $hd->file_path = $request->file('file')->store('hd_materials', 'public');
        }

        $hd->sub_module_id = $request->sub_module_id ?? $hd->sub_module_id;
        $hd->name = $request->name ?? $hd->name;
        $hd->content = $request->content ?? $hd->content;
        $hd->video_url = $request->video_url ?? $hd->video_url;

        $hd->save();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'HD updated successfully',
                'statusCode' => 200,
            ],
            'data' => $hd,
        ]);
    }

    public function destroy($id)
    {
        $hd = HD::find($id);

        if (!$hd) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'HD not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $hd->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'HD deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }
}
