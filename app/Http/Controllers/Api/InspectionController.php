<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Inspection;
use App\Models\InspectionResponse;

class InspectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $inspections = Inspection::with(['asset', 'checklist_template', 'inspector'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->asset_id, function ($query, $assetId) {
                $query->where('asset_id', $assetId);
            })
            ->when($request->inspector_id, function ($query, $inspectorId) {
                $query->where('inspector_id', $inspectorId);
            })
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $inspections->map(function ($inspection) {
                return [
                    'id' => $inspection->id,
                    'status' => $inspection->status,
                    'score' => $inspection->score,
                    'started_at' => $inspection->started_at,
                    'completed_at' => $inspection->completed_at,
                    'asset' => [
                        'id' => $inspection->asset->id,
                        'name' => $inspection->asset->name,
                        'asset_id' => $inspection->asset->asset_id,
                        'location' => $inspection->asset->location,
                    ],
                    'template' => [
                        'id' => $inspection->checklist_template->id,
                        'name' => $inspection->checklist_template->name,
                    ],
                    'inspector' => [
                        'id' => $inspection->inspector->id,
                        'name' => $inspection->inspector->name,
                    ],
                ];
            }),
            'pagination' => [
                'current_page' => $inspections->currentPage(),
                'last_page' => $inspections->lastPage(),
                'per_page' => $inspections->perPage(),
                'total' => $inspections->total(),
            ]
        ]);
    }

    public function show(Inspection $inspection): JsonResponse
    {
        $inspection->load(['asset', 'checklist_template', 'inspector', 'responses']);

        return response()->json([
            'data' => [
                'id' => $inspection->id,
                'status' => $inspection->status,
                'score' => $inspection->score,
                'started_at' => $inspection->started_at,
                'completed_at' => $inspection->completed_at,
                'notes' => $inspection->notes,
                'checklist_data' => $inspection->checklist_data,
                'asset' => [
                    'id' => $inspection->asset->id,
                    'name' => $inspection->asset->name,
                    'asset_id' => $inspection->asset->asset_id,
                    'location' => $inspection->asset->location,
                    'qr_code' => $inspection->asset->qr_code,
                ],
                'template' => [
                    'id' => $inspection->checklist_template->id,
                    'name' => $inspection->checklist_template->name,
                    'description' => $inspection->checklist_template->description,
                ],
                'inspector' => [
                    'id' => $inspection->inspector->id,
                    'name' => $inspection->inspector->name,
                    'email' => $inspection->inspector->email,
                ],
                'responses' => $inspection->responses->map(function ($response) {
                    return [
                        'id' => $response->id,
                        'question_id' => $response->question_id,
                        'response_value' => $response->response_value,
                        'response_type' => $response->response_type,
                        'media_url' => $response->media_url,
                        'notes' => $response->notes,
                        'created_at' => $response->created_at,
                    ];
                }),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'checklist_template_id' => 'required|exists:checklist_templates,id',
        ]);

        $inspection = Inspection::create([
            'asset_id' => $request->asset_id,
            'checklist_template_id' => $request->checklist_template_id,
            'inspector_id' => auth()->id(),
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Inspection created successfully',
            'data' => [
                'id' => $inspection->id,
                'status' => $inspection->status,
                'started_at' => $inspection->started_at,
            ]
        ], 201);
    }

    public function complete(Request $request, Inspection $inspection): JsonResponse
    {
        $request->validate([
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|string',
            'responses.*.response_value' => 'required',
            'responses.*.response_type' => 'required|string',
            'responses.*.notes' => 'nullable|string',
            'responses.*.media_url' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Save inspection responses
        foreach ($request->responses as $responseData) {
            InspectionResponse::create([
                'inspection_id' => $inspection->id,
                'question_id' => $responseData['question_id'],
                'response_value' => $responseData['response_value'],
                'response_type' => $responseData['response_type'],
                'notes' => $responseData['notes'] ?? null,
                'media_url' => $responseData['media_url'] ?? null,
            ]);
        }

        // Calculate score based on responses
        $totalQuestions = count($request->responses);
        $passedQuestions = collect($request->responses)->filter(function ($response) {
            return in_array($response['response_value'], ['pass', 'yes', 'satisfactory', 'good']);
        })->count();

        $score = $totalQuestions > 0 ? round(($passedQuestions / $totalQuestions) * 100, 2) : 0;

        // Update inspection
        $inspection->update([
            'status' => 'completed',
            'completed_at' => now(),
            'score' => $score,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Inspection completed successfully',
            'data' => [
                'id' => $inspection->id,
                'status' => $inspection->status,
                'score' => $inspection->score,
                'completed_at' => $inspection->completed_at,
            ]
        ]);
    }
}
