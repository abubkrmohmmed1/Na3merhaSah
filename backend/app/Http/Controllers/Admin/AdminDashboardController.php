<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Domains\Reporting\Models\Report;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        // Calculate statistics based on real data
        $stats = [
            'total' => Report::count(),
            'new' => Report::where('status', Report::STATUS_STARTED)->count(),
            'under_review' => Report::whereIn('status', [
                Report::STATUS_GOVT_RECEIVED, 
                Report::STATUS_SURVEYOR_ASSIGNED, 
                Report::STATUS_SITE_VISITED,
                Report::STATUS_ENGINEERING_PHASE,
                Report::STATUS_BIDDING_PHASE,
                Report::STATUS_EXECUTION,
                Report::STATUS_ADMIN_APPROVAL
            ])->count(),
            'completed' => Report::where('status', Report::STATUS_RESOLVED)->count(),
        ];

        // Fetch recent reports for the table and map
        $recentReports = Report::withCoordinates()->with('address')->orderBy('created_at', 'desc')->take(10)->get();

        // Pass mapping data points
        $mapPoints = $recentReports->map(function ($report) {
            return [
                'id' => $report->id,
                'lat' => $report->location_lat,
                'lng' => $report->location_lng,
                'status' => $report->status,
                'address' => $report->address ? $report->address->digital_address : 'غير محدد',
            ];
        })->filter(function($item) {
            return $item['lat'] !== null && $item['lng'] !== null;
        });

        return view('admin.dashboard', compact('stats', 'recentReports', 'mapPoints'));
    }
}
