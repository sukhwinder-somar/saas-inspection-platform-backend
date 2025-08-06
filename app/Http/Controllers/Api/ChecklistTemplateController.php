<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ChecklistTemplate;

class ChecklistTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $templates = ChecklistTemplate::when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'category' => $template->category,
                    'version' => $template->version,
                    'estimated_duration' => $template->estimated_duration,
                    'question_count' => count($template->template_data['sections'] ?? []),
                    'is_active' => $template->is_active,
                    'created_at' => $template->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
            ]
        ]);
    }

    public function show(ChecklistTemplate $template): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'category' => $template->category,
                'version' => $template->version,
                'estimated_duration' => $template->estimated_duration,
                'instructions' => $template->instructions,
                'template_data' => $template->template_data,
                'is_active' => $template->is_active,
                'created_at' => $template->created_at,
                'updated_at' => $template->updated_at,
            ]
        ]);
    }
}
