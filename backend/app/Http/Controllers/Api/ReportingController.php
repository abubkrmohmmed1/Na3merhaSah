<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Reporting\Actions\ReportIssueAction;
use App\Domains\Reporting\Models\Report;
use App\Domains\Reporting\Resources\ReportResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportingController extends Controller
{
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

    public function show(string $id)
    {
        $report = Report::withCoordinates()->with('address')->findOrFail($id);
        return new ReportResource($report);
    }

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
