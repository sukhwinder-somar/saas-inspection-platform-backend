<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Asset;
use App\Models\Inspection;
use App\Models\Notification;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $userId = auth()->id();

        // Asset statistics
        $totalAssets = Asset::count();
        $assetsByStatus = Asset::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Inspection statistics
        $totalInspections = Inspection::count();
        $inspectionsThisMonth = Inspection::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $inspectionsByStatus = Inspection::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $myInspections = Inspection::where('inspector_id', $userId)->count();
        $myInspectionsThisMonth = Inspection::where('inspector_id', $userId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Recent activity
        $recentInspections = Inspection::with(['asset', 'inspector'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($inspection) {
                return [
                    'id' => $inspection->id,
                    'asset_name' => $inspection->asset->name,
                    'status' => $inspection->status,
                    'score' => $inspection->score,
                    'inspector_name' => $inspection->inspector->name,
                    'completed_at' => $inspection->completed_at,
                    'created_at' => $inspection->created_at,
                ];
            });

        // Overdue inspections
        $overdueAssets = Asset::whereNotNull('next_maintenance')
            ->where('next_maintenance', '<', Carbon::now())
            ->count();

        // Notifications
        $unreadNotifications = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        // Compliance status
        $complianceIssues = Asset::whereJsonContains('compliance_requirements', 'required')
            ->whereDoesntHave('inspections', function ($query) {
                $query->where('status', 'completed')
                    ->where('completed_at', '>=', Carbon::now()->subDays(30));
            })
            ->count();

        return response()->json([
            'assets' => [
                'total' => $totalAssets,
                'by_status' => $assetsByStatus,
                'overdue_maintenance' => $overdueAssets,
                'compliance_issues' => $complianceIssues,
            ],
            'inspections' => [
                'total' => $totalInspections,
                'this_month' => $inspectionsThisMonth,
                'by_status' => $inspectionsByStatus,
                'my_total' => $myInspections,
                'my_this_month' => $myInspectionsThisMonth,
            ],
            'recent_activity' => $recentInspections,
            'notifications' => [
                'unread_count' => $unreadNotifications,
            ],
            'summary' => [
                'assets_needing_attention' => $overdueAssets + $complianceIssues,
                'inspections_completion_rate' => $totalInspections > 0
                    ? round((($inspectionsByStatus['completed'] ?? 0) / $totalInspections) * 100, 1)
                    : 0,
                'average_inspection_score' => Inspection::where('status', 'completed')
                    ->whereNotNull('score')
                    ->avg('score') ?? 0,
            ],
        ]);
    }
}
