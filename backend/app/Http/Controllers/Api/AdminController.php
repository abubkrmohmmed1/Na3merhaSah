<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Reporting\Services\AdminDashboardService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function dashboard(Request $request)
    {
        return response()->json([
            'metrics' => $this->dashboardService->getDashboardMetrics(),
            'role' => $request->user()->role,
            'status' => 'active'
        ]);
    }
}
