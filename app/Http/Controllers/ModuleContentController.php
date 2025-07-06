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
            'file_path' => 'required|file|mimes:pdf|max:10240',
            'name' => 'required|string',
            'content' => 'required|string',
            'video_url' => 'required|string',
            'type' => 'required|in:ht,dm,km',
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
    Log::info('🛠️ Update request masuk', [
        'id' => $id,
        'request_data_post' => $request->post(),
        'request_data_all' => $request->all(),
        'request_keys' => $request->keys(),
        'has_file' => $request->hasFile('file_path'),
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

    Log::info('📥 Validated data', $validated);
    Log::info('📄 Data sebelum update', $content->toArray());

    // Handle file jika ada
    if ($request->hasFile('file_path')) {
        if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
            Storage::disk('public')->delete($content->file_path);
        }

        $path = $request->file('file_path')->store('module_contents', 'public');
        $content->file_path = $path;

        Log::info('📁 File PDF baru disimpan di', ['path' => $path]);
    } else {
        Log::info('ℹ️ Tidak ada file PDF baru diupload');
    }

    // Update nilai-nilai yang dikirim
    foreach (['sub_module_id', 'name', 'content', 'video_url', 'type'] as $field) {
        if ($request->has($field)) {
            $content->$field = $request->input($field);
        }
    }

    // ⛔ Cegah perubahan yang tidak tersimpan jika data tidak berubah
    if ($content->isDirty()) {
        $content->save();
        Log::info('✅ Data berhasil diupdate karena ada perubahan', $content->fresh()->toArray());
    } else {
        // Paksa update timestamp
        $content->touch();
        Log::info('ℹ️ Tidak ada perubahan data, hanya update timestamp', $content->fresh()->toArray());
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

    public function markAsOpened($id)
    {
        $content = ModuleContent::find($id);

        if (!$content) {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        }

        $content->last_opened_at = now()->setTimezone('Asia/Jakarta');
        $content->save();

        return response()->json([
            'message' => 'Updated',
            'last_opened_at' => $content->last_opened_at->toIso8601String(),
        ]);
    }
}
