<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Reporting\Models\Report;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminKPIController extends Controller
{
    public function index(): View
    {
        // 1. Basic Stats
        $totalResolved = Report::where('status', 'resolved')->count();
        $avgRating = Report::whereNotNull('user_rating')->avg('user_rating') ?? 0;

        // 2. Map Arabic options to numerical values for calculation
        $map = [
            'ممتاز' => 5, 'جيد' => 3, 'ضعيف' => 1,
            'في الموعد' => 5, 'مبكر' => 5, 'متأخر' => 1,
            'محترم' => 5, 'عادي' => 3, 'غير لائق' => 1,
            'نظيف' => 5, 'مقبول' => 3, 'سيء' => 1
        ];

        // 3. Calculate Performance Indicators (simplified for this demo)
        // In a real app, we'd use a more robust way to handle these strings
        $reports = Report::whereNotNull('user_rating')->get();
        
        $kpis = [
            'quality' => $this->calculateAvg($reports, 'feedback_quality', $map),
            'time' => $this->calculateAvg($reports, 'feedback_time', $map),
            'behavior' => $this->calculateAvg($reports, 'feedback_behavior', $map),
            'cleanliness' => $this->calculateAvg($reports, 'feedback_cleanliness', $map),
        ];

        // 4. Issue Distribution
        $issues = Report::whereNotNull('feedback_main_issue')
            ->select('feedback_main_issue', DB::raw('count(*) as total'))
            ->groupBy('feedback_main_issue')
            ->get();

        // 5. Category Distribution
        $categories = Report::select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->get()
            ->map(function($item) {
                $labels = [1 => 'مياه', 2 => 'كهرباء', 3 => 'طرق', 4 => 'صرف صحي', 5 => 'مباني', 6 => 'طوارئ'];
                return [
                    'name' => $labels[$item->category_id] ?? 'أخرى',
                    'total' => $item->total
                ];
            });

        // 6. Location Distribution (by neighborhood)
        $locations = Report::join('addresses', 'reports.address_id', '=', 'addresses.id')
            ->select('addresses.neighborhood', DB::raw('count(*) as total'))
            ->groupBy('addresses.neighborhood')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return view('admin.kpi.index', compact('totalResolved', 'avgRating', 'kpis', 'issues', 'categories', 'locations'));
    }

    private function calculateAvg($reports, $field, $map)
    {
        $count = 0;
        $sum = 0;
        foreach ($reports as $report) {
            $val = $report->$field;
            if ($val && isset($map[$val])) {
                $sum += $map[$val];
                $count++;
            }
        }
        return $count > 0 ? ($sum / $count) : 0;
    }
}
