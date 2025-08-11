<?php

namespace App\Http\Controllers;

use App\Models\DM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DMController extends Controller
{
    public function index()
    {
        $dms = DM::all();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'List of DMs retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $dms,
        ]);
    }

    public function getBySubModule($sub_module_id)
    {
        $dms = DM::where('sub_module_id', $sub_module_id)->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'DMs retrieved successfully for the given sub_module_id',
                'statusCode' => 200,
            ],
            'data' => $dms,
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

        $path = $request->file('file_path')->store('dm_materials', 'public');

        $dm = DM::create([
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
                'message' => 'DM created successfully',
                'statusCode' => 201,
            ],
            'data' => $dm,
        ], 201);
    }

    public function show($id)
    {
        $dm = DM::find($id);

        if (!$dm) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'DM not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'DM details retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $dm,
        ]);
    }

    public function update(Request $request, $id)
    {
        $dm = DM::find($id);

        if (!$dm) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'DM not found',
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
            if ($dm->file_path && Storage::disk('public')->exists($dm->file_path)) {
                Storage::disk('public')->delete($dm->file_path);
            }

            $dm->file_path = $request->file('file')->store('dm_materials', 'public');
        }

        $dm->sub_module_id = $request->sub_module_id ?? $dm->sub_module_id;
        $dm->name = $request->name ?? $dm->name;
        $dm->content = $request->content ?? $dm->content;
        $dm->video_url = $request->video_url ?? $dm->video_url;

        $dm->save();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'DM updated successfully',
                'statusCode' => 200,
            ],
            'data' => $dm,
        ]);
    }

    public function destroy($id)
    {
        $dm = DM::find($id);

        if (!$dm) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'DM not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $dm->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'DM deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }
}
