<?php

namespace App\Domains\Reporting\Services;

use App\Domains\Reporting\Models\Report;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardService
{
    public function getDashboardMetrics(): array
    {
        // PERF-03: Single aggregated query instead of 5+ separate count queries
        $stats = Report::selectRaw("
            count(*) as total_reports,
            count(case when status = ? then 1 end) as resolved_count,
            count(case when created_at >= ? then 1 end) as monthly_reports
        ", [Report::STATUS_RESOLVED, Carbon::now()->startOfMonth()])
        ->first();

        $total = $stats->total_reports;
        $resolved = $stats->resolved_count;

        return [
            'reception_metrics'  => [
                'total_reports'   => $total,
                'monthly_reports' => $stats->monthly_reports,
                'by_category'     => Report::select('category_id', DB::raw('count(*) as total'))
                                            ->groupBy('category_id')->get(),
            ],
            'response_metrics'   => $this->getResponseMetrics(),
            'completion_metrics' => [
                'completion_rate' => $total > 0 ? round(($resolved / $total) * 100, 2) : 0,
                'pending_count'   => $total - $resolved,
            ],
        ];
    }

    private function getResponseMetrics(): array
    {
        $avgResponse = Report::whereNotNull('first_response_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (first_response_at - created_at))) as avg_seconds')
            ->first()->avg_seconds ?? 0;

        $avgResolution = Report::whereNotNull('resolved_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (resolved_at - created_at))) as avg_seconds')
            ->first()->avg_seconds ?? 0;

        return [
            'avg_first_response_seconds' => round($avgResponse),
            'avg_resolution_seconds'     => round($avgResolution),
        ];
    }
}
