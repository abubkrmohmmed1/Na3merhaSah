<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppManagementController extends Controller
{
    public function index()
    {
        $versions = DB::table('app_versions')->orderByDesc('version_code')->get();
        $announcements = DB::table('system_announcements')
            ->leftJoin('app_events', 'app_events.id', '=', 'system_announcements.event_id')
            ->select('system_announcements.*', 'app_events.title as event_title')
            ->orderByDesc('system_announcements.created_at')
            ->get();

        $events = DB::table('app_events')->where('is_active', true)->get();

        $pendingCount = DB::table('geo_features')
            ->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')
            ->whereIn('map_layers.name', ['الأحياء', 'القرى والمدن'])
            ->where('geo_features.status', 'pending')
            ->whereRaw("geo_features.properties->>'source' = 'boundary_challenge'")
            ->count();

        return view('admin.app_management.index', compact('versions', 'announcements', 'events', 'pendingCount'));
    }

    public function storeVersion(Request $request)
    {
        $data = $request->validate([
            'platform' => 'required|in:all,android,ios',
            'version_code' => 'required|integer|min:1',
            'version_name' => 'required|string|max:50',
            'download_url' => 'required|url',
            'release_notes' => 'nullable|string',
            'is_mandatory' => 'boolean',
        ]);

        $data['is_mandatory'] = $request->boolean('is_mandatory', false);
        $data['is_active'] = true;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('app_versions')->insert($data);

        return redirect()->back()->with('success', '✅ تم تسجيل إصدار التطبيق الجديد بنجاح!');
    }

    public function toggleVersion(int $id)
    {
        $version = DB::table('app_versions')->where('id', $id)->first();
        if (!$version) abort(404);

        DB::table('app_versions')->where('id', $id)->update([
            'is_active' => !$version->is_active,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', '✅ تم تعديل حالة التفعيل للإصدار بنجاح!');
    }

    public function deleteVersion(int $id)
    {
        DB::table('app_versions')->where('id', $id)->delete();
        return redirect()->back()->with('success', '🗑️ تم حذف إصدار التطبيق.');
    }

    public function storeAnnouncement(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,event',
            'event_id' => 'nullable|integer',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        $data['is_active'] = true;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('system_announcements')->insert($data);

        return redirect()->back()->with('success', '📢 تم نشر الإعلان العام بنجاح!');
    }

    public function toggleAnnouncement(int $id)
    {
        $announcement = DB::table('system_announcements')->where('id', $id)->first();
        if (!$announcement) abort(404);

        DB::table('system_announcements')->where('id', $id)->update([
            'is_active' => !$announcement->is_active,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', '✅ تم تعديل حالة تفعيل الإعلان بنجاح!');
    }

    public function deleteAnnouncement(int $id)
    {
        DB::table('system_announcements')->where('id', $id)->delete();
        return redirect()->back()->with('success', '🗑️ تم سحب وحذف الإعلان بنجاح.');
    }
}
