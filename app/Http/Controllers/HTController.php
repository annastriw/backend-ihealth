<?php

namespace App\Http\Controllers;

use App\Models\HT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HTController extends Controller
{
    public function index()
    {
        $hts = HT::all();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'List of HTs retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $hts,
        ]);
    }

    public function getBySubModule($sub_module_id)
    {
        $ht = HT::where('sub_module_id', $sub_module_id)->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'HT retrieved successfully for the given sub_module_id',
                'statusCode' => 200,
            ],
            'data' => $ht,
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

        $path = $request->file('file_path')->store('ht_materials', 'public');

        $ht = HT::create([
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
                'message' => 'HT created successfully',
                'statusCode' => 201,
            ],
            'data' => $ht,
        ], 201);
    }

    public function show($id)
    {
        $ht = HT::find($id);

        if (!$ht) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'HT not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'HT details retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $ht,
        ]);
    }

    public function update(Request $request, $id)
    {
        $ht = HT::find($id);

        if (!$ht) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'HT not found',
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
            if ($ht->file_path && Storage::disk('public')->exists($ht->file_path)) {
                Storage::disk('public')->delete($ht->file_path);
            }

            $ht->file_path = $request->file('file')->store('ht_materials', 'public');
        }

        $ht->sub_module_id = $request->sub_module_id ?? $ht->sub_module_id;
        $ht->name = $request->name ?? $ht->name;
        $ht->content = $request->content ?? $ht->content;
        $ht->video_url = $request->video_url ?? $ht->video_url;

        $ht->save();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'HT updated successfully',
                'statusCode' => 200,
            ],
            'data' => $ht,
        ]);
    }

    public function destroy($id)
    {
        $ht = HT::find($id);

        if (!$ht) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'HT not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $ht->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'HT deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }
}
