<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Addressing\Models\AddressSubmission;
use App\Domains\Spatial\Models\BoundaryProposal;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SubmissionsReviewController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'addresses');

        $pendingAddresses = AddressSubmission::pending()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingNeighborhoods = BoundaryProposal::pending()
            ->neighborhoods()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingVillages = BoundaryProposal::pending()
            ->villages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $mobileSubmissions = GeoFeature::where('status', 'pending')
            ->whereHas('mapLayer')
            ->with('mapLayer')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPending = $pendingAddresses->count() + $pendingNeighborhoods->count()
            + $pendingVillages->count() + $mobileSubmissions->count();

        $states = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الولايات'))
            ->select('id', DB::raw("properties->>'name' as name"))
            ->orderBy('name')
            ->get();

        $localities = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'المحليات'))
            ->select('id', DB::raw("properties->>'name' as name"), 'parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.submissions.index', compact(
            'tab', 'pendingAddresses', 'pendingNeighborhoods', 'pendingVillages',
            'mobileSubmissions', 'totalPending', 'states', 'localities'
        ));
    }

    public function approveMobile(string $id)
    {
        $feature = GeoFeature::where('status', 'pending')->findOrFail($id);
        $feature->update(['status' => 'verified']);

        // Activate all associated user actions (adds, confirms, votes, flags) so points are officially awarded!
        \App\Domains\Community\Models\UserAction::where('geo_feature_id', $feature->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'active',
                'updated_at' => now(),
            ]);

        // تسجيل تأكيد من الادمن
        \App\Domains\Community\Models\UserAction::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'geo_feature_id' => $feature->id,
                'action_type' => 'confirm',
            ],
            [
                'points' => \App\Domains\Community\Models\UserAction::pointsForAction('confirm'),
                'status' => 'active',
            ]
        );

        return back()->with('success', 'تمت الموافقة على المكان وتفعيل نقاط المشاركين بنجاح!');
    }

    public function rejectMobile(string $id)
    {
        $feature = GeoFeature::where('status', 'pending')->findOrFail($id);
        $feature->update(['status' => 'rejected']);

        // Delete associated pending user actions since the place was rejected
        \App\Domains\Community\Models\UserAction::where('geo_feature_id', $feature->id)->delete();

        return back()->with('success', 'تم رفض المكان وإلغاء نقاط المشاركين.');
    }

    public function approveAddress(string $id)
    {
        $submission = AddressSubmission::pending()->findOrFail($id);
        $submission->approve();
        return back()->with('success', 'تمت الموافقة على العنوان');
    }

    public function rejectAddress(Request $request, string $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $submission = AddressSubmission::pending()->findOrFail($id);
        $submission->reject($request->reason);
        return back()->with('success', 'تم رفض العنوان');
    }

    public function approveProposal(string $id)
    {
        $proposal = BoundaryProposal::pending()->findOrFail($id);

        $layerName = $proposal->proposal_type === 'neighborhood' ? 'الأحياء' : 'القرى والمدن';
        $layer = MapLayer::where('name', $layerName)->firstOrFail();

        $feature = GeoFeature::create([
            'layer_id' => $layer->id,
            'parent_id' => $proposal->locality_id,
            'properties' => [
                'name' => $proposal->name,
                'description' => $proposal->description,
                'submitted_by' => $proposal->user_id,
                'proposal_id' => $proposal->id,
            ],
            'status' => 'verified',
            'latitude' => $proposal->lat,
            'longitude' => $proposal->lng,
        ]);

        \App\Domains\Community\Models\UserAction::create([
            'user_id' => $proposal->user_id,
            'geo_feature_id' => $feature->id,
            'action_type' => 'add',
            'points' => \App\Domains\Community\Models\UserAction::pointsForAction('add'),
            'status' => 'active'
        ]);

        $proposal->approve();

        return back()->with('success', 'تمت الموافقة على الاقتراح');
    }

    public function rejectProposal(Request $request, string $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $proposal = BoundaryProposal::pending()->findOrFail($id);
        $proposal->reject($request->reason);
        return back()->with('success', 'تم رفض الاقتراح');
    }
}