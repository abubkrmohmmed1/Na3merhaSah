<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        // Reports system removed.
        return view('admin.dashboard', [
            'stats' => ['total' => 0, 'new' => 0, 'under_review' => 0, 'completed' => 0],
            'recentReports' => [],
            'mapPoints' => []
        ]);
    }
}
