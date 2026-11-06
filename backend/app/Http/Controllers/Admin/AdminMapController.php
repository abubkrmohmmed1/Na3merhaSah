<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use App\Domains\Addressing\Models\Address;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Spatial\Models\GeoFeature;
use Illuminate\Support\Facades\DB;

class AdminMapController extends Controller
{
    public function index(): View
    {
        $layers = \App\Domains\Spatial\Models\MapLayer::all();
        return view('admin.map', compact('layers'));
    }

    public function mapData(): \Illuminate\Http\JsonResponse
    {
        ini_set('memory_limit', '-1');
        
        // 1. Registered Locations (All addresses)
        $allAddresses = Address::withCoordinates()->get()->map(function($addr) {
            return [
                'lat' => $addr->location_lat,
                'lng' => $addr->location_lng,
                'address_str' => $addr->address_str,
            ];
        })->filter(fn($a) => $a['lat'] && $a['lng']);

        // 2. All Layers
        $layers = MapLayer::all();

        // 3. GeoJSON for point features (pharmacies, restaurants, etc.)
        $geoFeatures = GeoFeature::select(
            'id', 'layer_id', 'properties', 'status', 's2_cell_id', 'plus_code',
            DB::raw('ST_Y(ST_Centroid(geom::geometry)) as lat, ST_X(ST_Centroid(geom::geometry)) as lng'),
            DB::raw('ST_AsGeoJSON(geom) as geojson')
        )->get();

        $layerPoints = $geoFeatures->map(function ($feature) {
            return [
                'id' => $feature->id,
                'layer_id' => $feature->layer_id,
                'lat' => $feature->lat,
                'lng' => $feature->lng,
                'geojson' => $feature->geojson,
                'name' => $feature->properties['name'] ?? 'معلم جغرافي',
                'tags' => $feature->properties['tags'] ?? [],
                's2' => $feature->s2_cell_id,
                'plus_code' => $feature->plus_code,
            ];
        })->filter(fn($p) => $p['geojson']);

        // 4. Hierarchy: States with their localities, neighborhoods, and villages
        $hierarchy = $this->buildHierarchy();

        // 5. Villages data (for Gezira and other states)
        $villages = $this->getVillagesGeoJson();

        // 6. GeoJSON for administrative boundaries (for drawing on map)
        $adminBoundaries = $this->getAdminBoundariesGeoJson();

        // 7. Pending submission counts per locality
        $pendingCounts = $this->getPendingCountsPerLocality();

        return response()->json([
            'allAddresses' => $allAddresses->values()->toArray(),
            'layers' => $layers,
            'layerPoints' => $layerPoints->values()->toArray(),
            'hierarchy' => $hierarchy,
            'adminBoundaries' => $adminBoundaries,
            'villages' => $villages,
            'pendingCounts' => $pendingCounts
        ]);
    }

    private function getPendingCountsPerLocality(): array
    {
        $locLayer = \App\Domains\Spatial\Models\MapLayer::where('name', 'المحليات')->first();
        if (!$locLayer) return [];

        $localities = \App\Domains\Spatial\Models\GeoFeature::where('layer_id', $locLayer->id)
            ->select('id', \DB::raw("properties->>'name' as name"))
            ->get();

        $counts = [];

        foreach ($localities as $loc) {
            $addrCount = \App\Domains\Addressing\Models\AddressSubmission::pending()
                ->where('locality_id', $loc->id)->count();
            $hoodCount = \App\Domains\Spatial\Models\BoundaryProposal::pending()
                ->where('locality_id', $loc->id)
                ->where('proposal_type', 'neighborhood')->count();
            $villageCount = \App\Domains\Spatial\Models\BoundaryProposal::pending()
                ->where('locality_id', $loc->id)
                ->where('proposal_type', 'village')->count();

            $total = $addrCount + $hoodCount + $villageCount;
            if ($total > 0) {
                $counts[$loc->id] = [
                    'total' => $total,
                    'addresses' => $addrCount,
                    'neighborhoods' => $hoodCount,
                    'villages' => $villageCount,
                ];
            }
        }

        return $counts;
    }

    private function buildHierarchy(): array
    {
        $states = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الولايات'))
            ->whereIn('status', ['verified', 'active', 'pending'])
            ->with(['children' => function($q) {
                $q->whereHas('mapLayer', fn($q) => $q->where('name', 'المحليات'))
                  ->whereIn('status', ['verified', 'active', 'pending'])
                  ->with([
                      'children' => function($q) {
                          $q->whereHas('mapLayer', fn($q) => $q->where('name', 'الأحياء'))
                            ->whereIn('status', ['verified', 'active', 'pending']);
                      },
                      'allChildren' => function($q) {
                          $q->whereHas('mapLayer', fn($q) => $q->where('name', 'القرى والمدن'));
                      },
                  ]);
            }])
            ->orderBy('properties->name')
            ->get();

        return $states->map(fn($state) => [
            'id' => $state->id,
            'name' => $state->properties['name'] ?? '',
            'layer' => 'الولايات',
            'children' => $state->children->map(fn($locality) => [
                'id' => $locality->id,
                'name' => $locality->properties['name'] ?? '',
                'layer' => 'المحليات',
                'children' => $locality->children->map(fn($hood) => [
                    'id' => $hood->id,
                    'name' => $hood->properties['name'] ?? '',
                    'layer' => 'الأحياء',
                ])->values(),
                'villages' => $locality->allChildren->map(fn($v) => [
                    'id' => $v->id,
                    'name' => $v->properties['name'] ?? '',
                    'layer' => 'القرى والمدن',
                ])->values(),
            ])->values(),
        ])->values()->toArray();
    }

    private function getVillagesGeoJson(): array
    {
        $villagesLayer = MapLayer::where('name', 'القرى والمدن')->first();
        if (!$villagesLayer) return [];

        $villages = GeoFeature::where('layer_id', $villagesLayer->id)
            ->whereNotNull('geom')
            ->get();

        return $villages->map(fn($v) => [
            'type' => 'Feature',
            'properties' => [
                'id' => $v->id,
                'name' => $v->properties['name'] ?? '',
                'layer' => 'القرى والمدن',
                'layer_id' => $v->layer_id,
                'parent_id' => $v->parent_id,
                'geom_type' => DB::selectOne('SELECT ST_GeometryType(geom) as type FROM geo_features WHERE id = ?', [$v->id])->type ?? 'ST_Point',
            ],
            'geometry' => json_decode(DB::selectOne('SELECT ST_AsGeoJSON(geom) as gj FROM geo_features WHERE id = ?', [$v->id])->gj ?? '{}'),
        ])->toArray();
    }

private function getAdminBoundariesGeoJson(): array
      {
          $features = GeoFeature::with('mapLayer')
              ->select([
                  'id', 'layer_id', 'parent_id', 'properties', 'status'
              ])
              ->selectRaw('ST_AsGeoJSON(geom) as geojson')
              ->selectRaw('ST_GeometryType(geom) as geom_type')
              ->whereHas('mapLayer', fn($q) => $q->whereIn('name', ['الولايات', 'المحليات', 'الأحياء']))
              ->whereIn('status', ['verified', 'active', 'pending'])
              ->whereNotNull('geom')
              ->get();

          // تشخيص — اعرف أنواع الهندسة
          \Log::info('=== ADMIN BOUNDARIES GEOM TYPES ===');
          foreach ($features->groupBy('geom_type') as $type => $group) {
              \Log::info("Type: {$type} | Count: {$group->count()}");
              foreach ($group->take(1) as $f) {
                  \Log::info("  Sample: {$f->properties['name']} | Layer: " . ($f->mapLayer?->name ?? 'null'));
              }
          }

          return $features->map(fn($f) => [
              'type' => 'Feature',
              'properties' => [
                  'id' => $f->id,
                  'name' => $f->properties['name'] ?? '',
                  'layer' => $f->mapLayer?->name ?? 'unknown',
                  'layer_id' => $f->layer_id,
                  'parent_id' => $f->parent_id,
                  'geom_type' => $f->geom_type,
              ],
              'geometry' => json_decode($f->geojson),
          ])->toArray();
      }
}