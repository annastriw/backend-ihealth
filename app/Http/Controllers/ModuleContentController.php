<?php

namespace App\Http\Controllers;

use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModuleContentController extends Controller
{
    public function index()
    {
        $contents = ModuleContent::with('subModule')->orderBy('created_at', 'desc')->get();
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
        $request->validate([
            'sub_module_id' => 'required|uuid|exists:sub_modules,id',
            'file_path' => 'required|file',
            'name' => 'required|string',
            'content' => 'required|string',
            'video_url' => 'required|string',
            'type' => 'required|in:capd,hd',
        ]);

        $path = $request->file('file_path')->store('module_contents', 'public');

        $content = ModuleContent::create([
            'id' => Str::uuid(),
            'sub_module_id' => $request->sub_module_id,
            'file_path' => $path,
            'name' => $request->name,
            'content' => $request->content,
            'video_url' => $request->video_url,
            'type' => $request->type,
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

        $request->validate([
            'sub_module_id' => 'nullable|uuid',
            'file' => 'nullable|file',
            'name' => 'nullable|string',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string',
            'type' => 'nullable|in:capd,hd',
        ]);

        if ($request->hasFile('file')) {
            if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
                Storage::disk('public')->delete($content->file_path);
            }

            $content->file_path = $request->file('file')->store('module_contents', 'public');
        }

        $content->sub_module_id = $request->sub_module_id ?? $content->sub_module_id;
        $content->name = $request->name ?? $content->name;
        $content->content = $request->content ?? $content->content;
        $content->video_url = $request->video_url ?? $content->video_url;
        $content->type = $request->type ?? $content->type;

        $content->save();

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
