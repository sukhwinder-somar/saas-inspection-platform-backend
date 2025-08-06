<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Asset;
use App\Models\Inspection;
use App\Models\ChecklistTemplate;
use App\Http\Resources\AssetResource;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assets = Asset::with(['organization'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('asset_id', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->paginate(20);

        return response()->json([
            'data' => AssetResource::collection($assets),
            'pagination' => [
                'current_page' => $assets->currentPage(),
                'last_page' => $assets->lastPage(),
                'per_page' => $assets->perPage(),
                'total' => $assets->total(),
            ]
        ]);
    }

    public function show(Asset $asset): JsonResponse
    {
        $asset->load(['organization', 'inspections' => function ($query) {
            $query->latest()->limit(5);
        }]);

        return response()->json([
            'data' => new AssetResource($asset),
            'recent_inspections' => $asset->inspections->map(function ($inspection) {
                return [
                    'id' => $inspection->id,
                    'status' => $inspection->status,
                    'score' => $inspection->score,
                    'completed_at' => $inspection->completed_at,
                    'inspector_name' => $inspection->inspector->name ?? 'Unknown',
                ];
            }),
        ]);
    }

    public function inspect(Request $request, Asset $asset): JsonResponse
    {
        $request->validate([
            'checklist_template_id' => 'required|exists:checklist_templates,id',
        ]);

        $template = ChecklistTemplate::findOrFail($request->checklist_template_id);

        // Check if there's already an active inspection for this asset
        $activeInspection = Inspection::where('asset_id', $asset->id)
            ->where('status', 'in_progress')
            ->first();

        if ($activeInspection) {
            return response()->json([
                'message' => 'There is already an active inspection for this asset',
                'inspection' => [
                    'id' => $activeInspection->id,
                    'status' => $activeInspection->status,
                    'started_at' => $activeInspection->started_at,
                ]
            ], 409);
        }

        // Create new inspection
        $inspection = Inspection::create([
            'asset_id' => $asset->id,
            'checklist_template_id' => $template->id,
            'inspector_id' => auth()->id(),
            'status' => 'in_progress',
            'started_at' => now(),
            'checklist_data' => $template->template_data,
        ]);

        return response()->json([
            'message' => 'Inspection started successfully',
            'inspection' => [
                'id' => $inspection->id,
                'status' => $inspection->status,
                'started_at' => $inspection->started_at,
                'checklist_data' => $inspection->checklist_data,
            ]
        ], 201);
    }
}
