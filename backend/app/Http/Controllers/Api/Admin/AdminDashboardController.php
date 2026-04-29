<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Reporting\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Get KPIs and statistics for Nizar's Dashboard.
     */
    public function index(): JsonResponse
    {
        $totalReports = Report::count();
        $resolvedReports = Report::where('status', Report::STATUS_RESOLVED)->count();
        $pendingReports = $totalReports - $resolvedReports;

        // Reports by category (Simulated names for now)
        $categories = [
            1 => 'مياه',
            2 => 'كهرباء',
            3 => 'طرق',
            4 => 'صرف صحي',
            5 => 'مباني',
            6 => 'طوارئ'
        ];

        $reportsByCategory = Report::select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->get()
            ->map(fn($item) => [
                'name' => $categories[$item->category_id] ?? 'أخرى',
                'count' => $item->count
            ]);

        // Reports by status
        $reportsByStatus = Report::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'summary' => [
                'total' => $totalReports,
                'resolved' => $resolvedReports,
                'pending' => $pendingReports,
                'resolution_rate' => $totalReports > 0 ? round(($resolvedReports / $totalReports) * 100, 2) : 0,
            ],
            'by_category' => $reportsByCategory,
            'by_status' => $reportsByStatus,
            'recent_activity' => Report::orderBy('created_at', 'desc')->limit(5)->get(),
        ]);
    }
}
