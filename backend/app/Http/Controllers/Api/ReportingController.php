<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Reporting\Actions\ReportIssueAction;
use App\Domains\Reporting\Models\Report;
use App\Domains\Reporting\Resources\ReportResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ReportingController extends Controller
{
    #[OA\Post(
        path: '/api/reports',
        operationId: 'storeReport',
        summary: 'رفع بلاغ جديد',
        description: 'إنشاء بلاغ جديد بالموقع والصور والوصف.',
        tags: ['Reports'],
        security: [['sanctum' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['lat', 'lng', 'description', 'category_id'],
                properties: [
                    new OA\Property(property: 'lat', type: 'number', example: 24.7136),
                    new OA\Property(property: 'lng', type: 'number', example: 46.6753),
                    new OA\Property(property: 'description', type: 'string', example: 'يوجد تسريب مياه في الشارع'),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(property: 'images[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary'))
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'تم إنشاء البلاغ')]
    public function store(Request $request, ReportIssueAction $action): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'description' => 'required|string',
            'category_id' => 'required|integer',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $report = $action->execute(
            array_merge($request->only(['lat', 'lng', 'description', 'category_id', 'workflow_step']), ['user_id' => auth()->id()]),
            $request->file('images', [])
        );

        return response()->json([
            'message' => 'Report submitted successfully.',
            'data' => new ReportResource($report),
        ], 201);
    }

    #[OA\Get(
        path: '/api/reports/{id}',
        operationId: 'showReport',
        summary: 'عرض تفاصيل بلاغ محدد',
        description: 'جلب تفاصيل البلاغ بالمعرف الخاص به.',
        tags: ['Reports'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'نجاح')]
    #[OA\Response(response: 404, description: 'البلاغ غير موجود')]
    public function show(string $id)
    {
        $report = Report::withCoordinates()->with('address')->findOrFail($id);
        return new ReportResource($report);
    }

    #[OA\Post(
        path: '/api/reports/{id}/feedback',
        operationId: 'reportFeedback',
        summary: 'تقييم البلاغ بعد إغلاقه',
        description: 'إضافة تقييم من المواطن على جودة حل المشكلة.',
        tags: ['Reports'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['user_rating'],
            properties: [
                new OA\Property(property: 'user_rating', type: 'integer', example: 5),
                new OA\Property(property: 'user_feedback', type: 'string', example: 'عمل ممتاز وسريع')
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'تم التقييم بنجاح')]
    #[OA\Response(response: 403, description: 'غير مصرح')]
    public function feedback(Request $request, string $id)
    {
        $request->validate([
            'user_feedback' => 'nullable|string',
            'user_rating' => 'required|integer|min:1|max:5',
            'feedback_quality' => 'nullable|string',
            'feedback_time' => 'nullable|string',
            'feedback_behavior' => 'nullable|string',
            'feedback_cleanliness' => 'nullable|string',
            'feedback_main_issue' => 'nullable|string',
        ]);

        $report = Report::findOrFail($id);
        
        // Ensure only the owner can give feedback
        if ($report->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $report->update([
            'user_feedback' => $request->user_feedback,
            'user_rating' => $request->user_rating,
            'feedback_quality' => $request->feedback_quality,
            'feedback_time' => $request->feedback_time,
            'feedback_behavior' => $request->feedback_behavior,
            'feedback_cleanliness' => $request->feedback_cleanliness,
            'feedback_main_issue' => $request->feedback_main_issue,
        ]);

        return response()->json(['message' => 'Feedback submitted successfully']);
    }

    #[OA\Get(
        path: '/api/reports',
        operationId: 'getUserReports',
        summary: 'جلب قائمة بلاغات المواطن',
        description: 'ترجع هذه الواجهة قائمة بجميع البلاغات التي رفعها المستخدم الحالي.',
        tags: ['Reports'],
        security: [['sanctum' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'عملية ناجحة'
    )]
    #[OA\Response(
        response: 401,
        description: 'غير مصرح (Unauthenticated)'
    )]
    public function index(Request $request): JsonResponse
    {
        $reports = \App\Domains\Reporting\Models\Report::withCoordinates()
            ->with('address')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => ReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
            ],
        ]);
    }
}
