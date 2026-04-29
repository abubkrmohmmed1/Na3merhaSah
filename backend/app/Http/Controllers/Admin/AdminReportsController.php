<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Domains\Reporting\Models\Report;

use App\Domains\Addressing\Models\Address;

class AdminReportsController extends Controller
{
    /**
     * Display a list of all reports for the admin.
     */
    public function index(Request $request): View
    {
        $query = Report::with('address')->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(20);
        $currentStatus = $request->status ?? 'all';

        return view('admin.reports.index', compact('reports', 'currentStatus'));
    }

    public function map(): View
    {
        // 1. Registered Locations (All addresses)
        $allAddresses = Address::withCoordinates()->get()->map(function($addr) {
            return [
                'type' => 'address',
                'lat' => $addr->location_lat,
                'lng' => $addr->location_lng,
                'neighborhood' => $addr->neighborhood,
                'address_str' => $addr->address_str,
            ];
        })->filter(fn($a) => $a['lat'] && $a['lng']);

        // 2. Report Points with categories
        $reports = Report::withCoordinates()->with('address')->get();
        $reportPoints = $reports->map(function ($report) {
            return [
                'type' => 'report',
                'id' => $report->id,
                'lat' => $report->location_lat,
                'lng' => $report->location_lng,
                'status' => $report->status,
                'category_id' => $report->category_id,
                'address' => $report->address ? $report->address->address_str : 'غير محدد',
            ];
        })->filter(fn($p) => $p['lat'] && $p['lng']);

        $summary = [
            'total' => $reports->count(),
            'pending' => $reports->where('status', '!=', 'resolved')->count(),
            'resolved' => $reports->where('status', 'resolved')->count(),
            'avg_rating' => number_format($reports->whereNotNull('user_rating')->avg('user_rating') ?? 0, 1),
        ];

        return view('admin.map', compact('reportPoints', 'allAddresses', 'summary'));
    }

    /**
     * Display the details of a single report.
     */
    public function show(string $id): View
    {
        $report = Report::withCoordinates()->with('address')->findOrFail($id);

        return view('admin.reports.show', compact('report'));
    }

    public function update(Request $request, string $id)
    {
        $report = Report::findOrFail($id);
        // Change status to surveyor assigned
        $report->status = Report::STATUS_SURVEYOR_ASSIGNED;
        $report->save();

        return redirect()->route('admin.reports.surveyor', $id)->with('success', 'تم ارسال القرار وتحويل البلاغ للمساح بنجاح');
    }

    public function surveyor(string $id): View
    {
        $report = Report::with('address')->findOrFail($id);
        return view('admin.reports.surveyor', compact('report', 'id'));
    }

    public function updateSurveyor(Request $request, string $id)
    {
        $request->validate([
            'surveyor_decision' => 'required|in:immediate,modification',
            'report' => 'nullable|string',
            'area' => 'nullable|numeric|min:0',
            'surveyor_images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $report = Report::findOrFail($id);

        // Handle image uploads
        $imagePaths = $report->surveyor_images ?? [];
        if ($request->hasFile('surveyor_images')) {
            foreach ($request->file('surveyor_images') as $file) {
                $path = $file->store('surveyor/images', 'public');
                $imagePaths[] = '/storage/' . $path;
            }
        }

        // Update report with surveyor data
        $report->update([
            'surveyor_decision' => $request->surveyor_decision,
            'surveyor_notes' => $request->report,
            'surveyor_area' => $request->area,
            'surveyor_images' => $imagePaths,
            'status' => Report::STATUS_ADMIN_APPROVAL,
            'first_response_at' => $report->first_response_at ?? now(),
        ]);

        return redirect()->route('admin.reports.approval', $id)
            ->with('success', 'تم رفع تقرير المساح بنجاح وتحويل البلاغ للاعتماد الإداري');
    }

    public function approval(string $id): View
    {
        $report = Report::with('address')->findOrFail($id);
        return view('admin.reports.approval', compact('report', 'id'));
    }

    public function updateApproval(Request $request, string $id)
    {
        $request->validate([
            'admin_decision' => 'required|in:immediate,project',
            'approved_cost' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $report = Report::findOrFail($id);

        // Update status based on decision
        $newStatus = $request->admin_decision === 'immediate' ? Report::STATUS_RESOLVED : Report::STATUS_EXECUTION;

        $report->update([
            'status' => $newStatus,
            'workflow_metadata' => array_merge($report->workflow_metadata ?? [], [
                'admin_decision' => $request->admin_decision,
                'approved_cost' => $request->approved_cost,
                'admin_notes' => $request->notes,
                'approved_at' => now()->toDateTimeString(),
            ]),
            'resolved_at' => $newStatus === Report::STATUS_RESOLVED ? now() : $report->resolved_at,
        ]);

        return redirect()->route('admin.reports.index')
            ->with('success', 'تم اعتماد التقرير بنجاح وتحديث حالة البلاغ');
    }

    public function project(string $id): View
    {
        $report = Report::with('address')->findOrFail($id);
        return view('admin.reports.project', compact('report', 'id'));
    }

    public function updateProject(Request $request, string $id)
    {
        $report = Report::findOrFail($id);
        // Change status to resolved
        $report->status = Report::STATUS_RESOLVED;
        $report->save();

        return redirect()->route('admin.reports.index')->with('success', 'تم اكمال المشروع بنجاح وتم إغلاق الشكوى');
    }
}
