<?php

namespace App\Domains\Reporting\Services;

use App\Domains\Reporting\Models\Report;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardService
{
    public function getDashboardMetrics(): array
    {
        return [
            'reception_metrics'  => $this->getReceptionMetrics(),
            'response_metrics'   => $this->getResponseMetrics(),
            'completion_metrics' => $this->getCompletionMetrics(),
        ];
    }

    private function getReceptionMetrics(): array
    {
        return [
            'total_reports'   => Report::count(),
            'monthly_reports' => Report::whereMonth('created_at', Carbon::now()->month)->count(),
            'by_category'     => Report::select('category_id', DB::raw('count(*) as total'))
                                        ->groupBy('category_id')->get(),
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

    private function getCompletionMetrics(): array
    {
        $total = Report::count();
        $closed = Report::where('status', Report::STATUS_RESOLVED)->count();
        
        return [
            'completion_rate' => $total > 0 ? round(($closed / $total) * 100, 2) : 0,
            'pending_count'   => Report::where('status', '!=', Report::STATUS_RESOLVED)->count(),
        ];
    }
}
