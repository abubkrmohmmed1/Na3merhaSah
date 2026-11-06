<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminKPIController extends Controller
{
    public function index(): View
    {
        $totalFeatures = GeoFeature::count();
        $verifiedFeatures = GeoFeature::where('status', 'verified')->count();
        $pendingFeatures = GeoFeature::where('status', 'pending')->count();
        $archivedFeatures = GeoFeature::where('status', 'archived')->count();

        $byLayer = MapLayer::withCount('geoFeatures')
            ->withCount([
                'geoFeatures as verified_count' => fn($q) => $q->where('status', 'verified'),
                'geoFeatures as pending_count' => fn($q) => $q->where('status', 'pending'),
            ])
            ->orderBy('geo_features_count', 'desc')
            ->get();

        $adminLayers = ['الولايات', 'المحليات', 'الأحياء', 'الحدود الإدارية'];
        $serviceLayers = $byLayer->whereNotIn('name', $adminLayers);
        $adminLayersData = $byLayer->whereIn('name', $adminLayers);

        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = GeoFeature::whereDate('created_at', $date->format('Y-m-d'))->count();
            $dailyStats[] = [
                'date' => $date->format('m/d'),
                'count' => $count,
            ];
        }

        $unknownCount = GeoFeature::where(function($q) {
            $q->whereRaw("properties->>'name' = 'Unknown'")
              ->orWhereRaw("properties->>'name' = 'مجهول'")
              ->orWhereRaw("properties->>'name' IS NULL");
        })->count();

        $verifiedByLayer = $byLayer->where('geo_features_count', '>', 0)->take(10);

        return view('admin.kpi.index', compact(
            'totalFeatures', 'verifiedFeatures', 'pendingFeatures', 'archivedFeatures',
            'byLayer', 'serviceLayers', 'adminLayersData', 'verifiedByLayer',
            'dailyStats', 'unknownCount'
        ));
    }
}