<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Reporting\Actions\ReportIssueAction;
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
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $report = $action->execute(
            $request->all(),
            $request->file('images', [])
        );

        return response()->json([
            'message' => 'Report submitted successfully.',
            'data' => new ReportResource($report),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        // For Task 2.4 (Listing)
        $reports = \App\Domains\Reporting\Models\Report::with('address')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return ReportResource::collection($reports)->response();
    }
}
