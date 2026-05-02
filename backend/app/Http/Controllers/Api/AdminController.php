<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Reporting\Services\AdminDashboardService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    protected $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    #[OA\Get(
        path: '/api/admin/dashboard',
        operationId: 'adminDashboard',
        summary: 'إحصائيات لوحة التحكم (مبسط)',
        description: 'جلب ملخص مبسط للوحة التحكم مع دور المستخدم.',
        tags: ['Admin'],
        security: [['sanctum' => []]]
    )]
    #[OA\Response(response: 200, description: 'بيانات اللوحة الأساسية')]
    public function dashboard(Request $request)
    {
        return response()->json([
            'metrics' => $this->dashboardService->getDashboardMetrics(),
            'role' => $request->user()->role,
            'status' => 'active'
        ]);
    }
}
