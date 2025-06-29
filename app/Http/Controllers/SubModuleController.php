<?php

namespace App\Http\Controllers;

use App\Models\SubModule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubModuleController extends Controller
{
    public function index()
    {
        $subModules = SubModule::with('module')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Successfully retrieved all sub-modules',
                'statusCode' => 200,
            ],
            'data' => $subModules,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|uuid|exists:modules,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subModule = SubModule::create([
            'id' => Str::uuid(),
            'module_id' => $validated['module_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Sub Modul created successfully',
                'statusCode' => 201,
            ],
            'data' => $subModule,
        ], 201);
    }

    public function getByModule($module_id)
    {
        $subModules = SubModule::where('module_id', $module_id)->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Sub Modules retrieved successfully for the given module',
                'statusCode' => 200,
            ],
            'data' => $subModules,
        ]);
    }

    public function show($id)
    {
        $subModule = SubModule::with('moduleContents')->find($id);

        if (!$subModule) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Sub Module not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Module found',
                'statusCode' => 200,
            ],
            'data' => $subModule,
        ]);
    }

    public function update(Request $request, $id)
    {
        $subModule = SubModule::find($id);

        if (!$subModule) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Sub Module not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'module_id' => 'required|uuid|exists:modules,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subModule->update([
            'module_id' => $validated['module_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Sub Module updated successfully',
                'statusCode' => 200,
            ],
            'data' => $subModule,
        ]);
    }

    public function destroy($id)
    {
        $subModule = SubModule::find($id);

        if (!$subModule) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Module not found',
                    'statusCode' => 404,
                ],
                'data' => null,
            ], 404);
        }

        $subModule->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Module deleted successfully',
                'statusCode' => 200,
            ],
            'data' => null,
        ]);
    }
}
