<?php

namespace App\Http\Controllers;

use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ModuleContentController extends Controller
{
    public function index()
    {
        $contents = ModuleContent::with('subModule')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'List of Module Contents retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $contents,
        ]);
    }

    public function getBySubModule($sub_module_id)
    {
        $contents = ModuleContent::where('sub_module_id', $sub_module_id)->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Module Contents retrieved successfully for the given sub_module_id',
                'statusCode' => 200,
            ],
            'data' => $contents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub_module_id' => 'required|uuid|exists:sub_modules,id',
            'file_path' => 'required|file|mimes:pdf|max:10240',
            'name' => 'required|string',
            'content' => 'required|string',
            'video_url' => 'required|string',
            'type' => 'required|in:ht,dm,km',
        ]);

        $filePath = $request->file('file_path')->store('module_contents', 'public');

        $content = ModuleContent::create([
            'id' => Str::uuid(),
            'sub_module_id' => $validated['sub_module_id'],
            'file_path' => $filePath,
            'name' => $validated['name'],
            'content' => $validated['content'],
            'video_url' => $validated['video_url'],
            'type' => $validated['type'],
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Module Content created successfully',
                'statusCode' => 201,
            ],
            'data' => $content,
        ], 201);
    }

    public function show($id)
    {
        $content = ModuleContent::find($id);

        if (!$content) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Module Content not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Module Content details retrieved successfully',
                'statusCode' => 200,
            ],
            'data' => $content,
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info('🛠️ Update request masuk', [
            'id' => $id,
            'request_data_all' => $request->all(),
        ]);

        $content = ModuleContent::find($id);

        if (!$content) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Module Content not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'sub_module_id' => 'nullable|uuid|exists:sub_modules,id',
            'file_path' => 'nullable|file|mimes:pdf|max:10240',
            'name' => 'nullable|string',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string',
            'type' => 'nullable|in:ht,dm,km',
        ]);

        if ($request->hasFile('file_path')) {
            if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
                Storage::disk('public')->delete($content->file_path);
            }

            $newPath = $request->file('file_path')->store('module_contents', 'public');
            $content->file_path = $newPath;
        }

        foreach (['sub_module_id', 'name', 'content', 'video_url', 'type'] as $field) {
            if ($request->has($field)) {
                $content->$field = $validated[$field];
            }
        }

        if ($content->isDirty()) {
            $content->save();
            Log::info('✅ Data berhasil diupdate', $content->toArray());
        } else {
            $content->touch();
            Log::info('ℹ️ Tidak ada perubahan, hanya update timestamp');
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Module Content updated successfully',
                'statusCode' => 200,
            ],
            'data' => $content,
        ]);
    }

    public function destroy($id)
    {
        $content = ModuleContent::find($id);

        if (!$content) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Module Content not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
            Storage::disk('public')->delete($content->file_path);
        }

        $content->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Module Content deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }
}
