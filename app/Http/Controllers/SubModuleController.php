<?php

namespace App\Http\Controllers;

use App\Models\SubModule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubModuleController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $subModules = SubModule::with(['module', 'preTests'])->get();

        $subModules = $subModules->map(function ($subModule) use ($user) {
            $preTestIds = $subModule->preTests->pluck('id');

            // Cek apakah semua pretest sudah dikerjakan user
            $donePreTestCount = $user->historyPreTest()
                ->whereIn('pre_test_id', $preTestIds)
                ->count();

            $subModule->isLocked = $donePreTestCount < $preTestIds->count();

            // Optional: hapus relasi preTests jika tidak ingin dikirim ke frontend
            unset($subModule->preTests);

            return $subModule;
        });

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
        $subModules = SubModule::with('preTests') // ambil relasi preTests
            ->where('module_id', $module_id)
            ->orderBy('created_at', 'asc')
            ->get();

        $user = auth()->user();

        $subModules->transform(function ($subModule) use ($user) {
            $preTestIds = $subModule->preTests->pluck('id');

            // Cek apakah user sudah mengerjakan semua pre-test
            $hasDoneAllPreTests = $preTestIds->every(function ($preTestId) use ($user) {
                return $user->historyPreTest()
                    ->where('pre_test_id', $preTestId)
                    ->exists();
            });

            // Tambahkan properti isLocked (true jika belum semua pretest dikerjakan)
            $subModule->isLocked = !$hasDoneAllPreTests;

            return $subModule;
        });

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Sub Modules retrieved successfully with isLocked status',
                'statusCode' => 200,
            ],
            'data' => $subModules,
        ]);
    }


    public function show($id)
    {
        $subModule = SubModule::with([
            'moduleContents',
            'preTests',
            'postTests'
        ])->find($id);

        if (!$subModule) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Submodul tidak ditemukan',
                    'statusCode' => 404
                ],
                'data' => null
            ], 404);
        }

        // ======== Tambahan logic untuk isLocked ========
        $user = auth()->user();

        $allPreTestIds = $subModule->preTests->pluck('id');

        $donePreTestIds = $user->historyPreTest()
            ->whereIn('pre_test_id', $allPreTestIds)
            ->pluck('pre_test_id')
            ->unique();

        $hasDoneAllPreTests = $donePreTestIds->count() === $allPreTestIds->count();

        $subModule->isLocked = !$hasDoneAllPreTests;
        // ===============================================

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Submodul ditemukan',
                'statusCode' => 200
            ],
            'data' => $subModule
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
