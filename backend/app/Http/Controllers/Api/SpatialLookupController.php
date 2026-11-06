<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Addressing\Models\AddressSubmission;
use App\Domains\Spatial\Models\BoundaryProposal;
use OpenApi\Attributes as OA;

class SpatialLookupController extends Controller
{
    #[OA\Get(
        path: '/api/v1/spatial/states',
        operationId: 'getStates',
        summary: 'جلب الولايات',
        tags: ['Spatial Lookup']
    )]
    #[OA\Response(response: 200, description: 'قائمة الولايات')]
    public function getStates()
    {
        $layer = MapLayer::where('name', 'الولايات')->first();
        if (!$layer) return response()->json(['states' => []]);

        $states = GeoFeature::where('layer_id', $layer->id)
            ->whereIn('status', ['verified', 'active'])
            ->select('id', DB::raw("properties->>'name' as name"))
            ->orderBy('name')
            ->get();

        return response()->json(['states' => $states]);
    }

    #[OA\Get(
        path: '/api/v1/spatial/localities',
        operationId: 'getLocalities',
        summary: 'جلب المحليات (اختيارياً حسب الولاية)',
        tags: ['Spatial Lookup']
    )]
    #[OA\Parameter(name: 'state_id', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'قائمة المحليات')]
    public function getLocalities(Request $request)
    {
        $request->validate(['state_id' => 'nullable|uuid']);

        $layer = MapLayer::where('name', 'المحليات')->first();
        if (!$layer) return response()->json(['localities' => []]);

        $query = GeoFeature::where('layer_id', $layer->id)
            ->whereIn('status', ['verified', 'active'])
            ->select('id', 'parent_id', DB::raw("properties->>'name' as name"));

        if ($request->state_id) {
            $state = GeoFeature::find($request->state_id);
            if ($state && $state->geom) {
                $query->whereRaw("ST_Contains(?, geom)", [$state->geom]);
            }
        }

        return response()->json(['localities' => $query->orderBy('name')->get()]);
    }

    #[OA\Get(
        path: '/api/v1/spatial/neighborhoods',
        operationId: 'getNeighborhoods',
        summary: 'جلب الأحياء داخل محلية',
        tags: ['Spatial Lookup']
    )]
    #[OA\Parameter(name: 'locality_id', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'قائمة الأحياء')]
    public function getNeighborhoods(Request $request)
    {
        $request->validate(['locality_id' => 'required|uuid']);

        $layer = MapLayer::where('name', 'الأحياء')->first();
        if (!$layer) return response()->json(['neighborhoods' => []]);

        $locality = GeoFeature::find($request->locality_id);
        if (!$locality || !$locality->geom) {
            return response()->json(['neighborhoods' => []]);
        }

        $neighborhoods = GeoFeature::where('layer_id', $layer->id)
            ->whereIn('status', ['verified', 'active'])
            ->whereRaw("ST_Contains(?, geom)", [$locality->geom])
            ->select('id', DB::raw("properties->>'name' as name"))
            ->orderBy('name')
            ->get();

        return response()->json(['neighborhoods' => $neighborhoods]);
    }

    #[OA\Get(
        path: '/api/v1/spatial/radius',
        operationId: 'radiusSearchPublic',
        summary: 'بحث دائري حول موقع (عام)',
        description: 'للتطبيق — استعلام عن المعالم الجغرافية ضمن نصف قطر. نفس وظيفة B2B لكن بدون API Key.',
        tags: ['Spatial Lookup']
    )]
    #[OA\Parameter(name: 'lat', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Parameter(name: 'lng', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Parameter(name: 'radius', in: 'query', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'layer', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'نتائج البحث الدائري')]
    public function radiusSearchPublic(Request $request)
    {
        return $this->radiusSearch($request);
    }

    #[OA\Get(
        path: '/api/v1/b2b/spatial/radius',
        operationId: 'radiusSearch',
        summary: 'بحث دائري حول موقع',
        description: 'استعلام عن المعالم الجغرافية ضمن نصف قطر محدد حول إحداثيات معينة.',
        tags: ['B2B Spatial'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'lat', in: 'query', required: true, description: 'خط العرض', schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Parameter(name: 'lng', in: 'query', required: true, description: 'خط الطول', schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Parameter(name: 'radius', in: 'query', required: true, description: 'نصف القطر بالمتر (10-50000)', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'layer', in: 'query', required: false, description: 'تصفية حسب الطبقة (اختياري)', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'عدد النتائج (1-200)', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'نتائج البحث الدائري')]
    #[OA\Response(response: 404, description: 'الطبقة غير موجودة')]
    public function radiusSearch(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:50000',
            'layer' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:200',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $radius = (int) $request->radius;
        $limit = (int) ($request->limit ?? 50);

        $layerId = null;
        if ($request->layer) {
            $layer = MapLayer::where('name', $request->layer)->first();
            if (!$layer) {
                return response()->json(['message' => 'الطبقة غير موجودة'], 404);
            }
            $layerId = $layer->id;
        }

        $query = GeoFeature::select(
                'geo_features.id',
                'geo_features.layer_id',
                'map_layers.name as layer_name',
                DB::raw("geo_features.properties->>'name' as name"),
                DB::raw('ST_X(ST_Centroid(geo_features.geom)) as lng'),
                DB::raw('ST_Y(ST_Centroid(geo_features.geom)) as lat'),
                'geo_features.status',
                'geo_features.properties'
            )
            ->join('map_layers', 'map_layers.id', '=', 'geo_features.layer_id')
            ->whereNotNull('geo_features.geom')
            ->whereRaw("ST_DWithin(geo_features.geom::geography, ST_MakePoint(?, ?)::geography, ?)", [$lng, $lat, $radius])
            ->orderByRaw("ST_Distance(ST_Centroid(geo_features.geom)::geography, ST_MakePoint(?, ?)::geography) ASC", [$lng, $lat]);

        if ($layerId) {
            $query->where('geo_features.layer_id', $layerId);
        }

        $results = $query->limit($limit)->get()->map(function ($item) {
            $props = is_string($item->properties) ? json_decode($item->properties, true) : ($item->properties ?? []);
            $vType = 'pending';
            if ($item->status === 'verified' || $item->status === 'active') {
                $vType = !empty($props['verified_by']) ? 'institutional' : 'community';
            }
            return [
                'id' => $item->id,
                'layer_id' => $item->layer_id,
                'layer' => $item->layer_name,
                'name' => $item->name,
                'lng' => $item->lng,
                'lat' => $item->lat,
                'status' => $item->status,
                'verification_type' => $vType,
            ];
        });

        return response()->json([
            'query' => compact('lat', 'lng', 'radius'),
            'count' => $results->count(),
            'results' => $results,
        ]);
    }

    #[OA\Get(
        path: '/api/v1/b2b/export',
        operationId: 'exportGeoJSON',
        summary: 'تصدير GeoJSON لطبقة كاملة',
        description: 'تصدير جميع معالم طبقة محددة بصيغة GeoJSON. يدعم الطبقات: الولايات، المحليات، الأحياء، القرى والمدن، أو أي طبقة أخرى.',
        tags: ['B2B Export'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'layer', in: 'query', required: true, description: 'اسم الطبقة (مثال: الأحياء, المحليات, الولايات)', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'GeoJSON file download')]
    #[OA\Response(response: 404, description: 'الطبقة غير موجودة')]
    public function exportGeoJSON(Request $request)
    {
        $request->validate(['layer' => 'required|string|max:100']);

        $layer = MapLayer::where('name', $request->layer)->first();
        if (!$layer) {
            return response()->json(['message' => 'الطبقة غير موجودة'], 404);
        }

        return response()->streamDownload(function () use ($layer) {
            echo '{"type":"FeatureCollection","features":[';
            $first = true;

            DB::table('geo_features as gf')
                ->leftJoin('geo_features as p', 'p.id', '=', 'gf.parent_id')
                ->where('gf.layer_id', $layer->id)
                ->whereIn('gf.status', ['active', 'verified'])
                ->select(
                    'gf.id',
                    'gf.status',
                    'gf.parent_id',
                    'gf.properties',
                    'p.properties as parent_properties',
                    DB::raw('ST_AsGeoJSON(gf.geom) as geometry')
                )
                ->orderBy('gf.id')
                ->cursor()
                ->each(function ($feature) use (&$first, $layer) {
                    if (!$feature->geometry) return;

                    if (!$first) echo ',';
                    $first = false;

                    $props = is_string($feature->properties) ? json_decode($feature->properties, true) : (array)$feature->properties;
                    $props['id'] = $feature->id;
                    $props['status'] = $feature->status;
                    $props['layer'] = $layer->name;

                    if ($feature->parent_id) {
                        $parentProps = is_string($feature->parent_properties) ? json_decode($feature->parent_properties, true) : (array)$feature->parent_properties;
                        $props['parent_name'] = $parentProps['name'] ?? null;
                    }

                    $f = [
                        'type' => 'Feature',
                        'geometry' => json_decode($feature->geometry),
                        'properties' => $props,
                    ];

                    echo json_encode($f);
                });

            echo ']}';
        }, $layer->name . '.geojson', [
            'Content-Type' => 'application/geo+json',
        ]);
    }

    #[OA\Post(
        path: '/api/v1/b2b/batch/geocode',
        operationId: 'batchGeocode',
        summary: 'معالجة دفعة من الإحداثيات',
        description: 'استقبال مصفوفة من الإحداثيات (lat/lng) وإرجاع العناوين + الأحياء + S2 Tokens. يدعم حتى 500 نقطة في المرة الواحدة.',
        tags: ['B2B Batch'],
        security: [['sanctum' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['points'],
            properties: [
                new OA\Property(property: 'points', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'lat', type: 'number', example: 15.5871),
                        new OA\Property(property: 'lng', type: 'number', example: 32.5232),
                    ]
                ))
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'نتائج المعالجة الدفعية')]
    public function batchGeocode(Request $request)
    {
        $request->validate([
            'points' => 'required|array|max:500',
            'points.*.lat' => 'required|numeric|between:-90,90',
            'points.*.lng' => 'required|numeric|between:-180,180',
        ]);

        $points = $request->points;
        
        if (empty($points)) {
            return response()->json([
                'processed' => 0,
                'results' => [],
            ]);
        }

        $values = [];
        $bindings = [];
        foreach ($points as $i => $pt) {
            $values[] = "(?, ?::double precision, ?::double precision)";
            $bindings[] = $i;
            $bindings[] = (float) $pt['lat'];
            $bindings[] = (float) $pt['lng'];
        }
        $valuesSql = implode(', ', $values);

        $rawResults = DB::select("
            SELECT p.idx, p.lat, p.lng,
                   (SELECT properties->>'name' FROM geo_features gf JOIN map_layers ml ON ml.id = gf.layer_id WHERE ml.name = 'الولايات' AND gf.status IN ('active', 'verified') AND ST_Contains(gf.geom, ST_SetSRID(ST_MakePoint(p.lng, p.lat), 4326)) LIMIT 1) as state,
                   (SELECT properties->>'name' FROM geo_features gf JOIN map_layers ml ON ml.id = gf.layer_id WHERE ml.name = 'المحليات' AND gf.status IN ('active', 'verified') AND ST_Contains(gf.geom, ST_SetSRID(ST_MakePoint(p.lng, p.lat), 4326)) LIMIT 1) as locality,
                   (SELECT properties->>'name' FROM geo_features gf JOIN map_layers ml ON ml.id = gf.layer_id WHERE ml.name = 'الأحياء' AND gf.status IN ('active', 'verified') AND ST_Contains(gf.geom, ST_SetSRID(ST_MakePoint(p.lng, p.lat), 4326)) LIMIT 1) as neighborhood
            FROM (VALUES {$valuesSql}) AS p(idx, lat, lng)
            ORDER BY p.idx ASC
        ", $bindings);

        $results = array_map(function ($row) {
            return [
                'index' => (int) $row->idx,
                'lat' => (float) $row->lat,
                'lng' => (float) $row->lng,
                'state' => $row->state,
                'locality' => $row->locality,
                'neighborhood' => $row->neighborhood,
            ];
        }, $rawResults);

        return response()->json([
            'processed' => count($results),
            'results' => $results,
        ]);
    }

    #[OA\Get(
        path: '/api/v1/spatial/pending-counts',
        operationId: 'getPendingCounts',
        summary: 'عدد التقديمات بانتظار المراجعة لكل محلية',
        tags: ['Spatial Lookup']
    )]
    #[OA\Response(response: 200, description: 'Counts per locality')]
    public function getPendingCounts()
    {
        $totalPending = AddressSubmission::pending()->count() + BoundaryProposal::pending()->count();

        $byLocality = DB::table('address_submissions')
            ->select('locality_id as loc_id', DB::raw('COUNT(*) as count'))
            ->where('status', 'pending')
            ->whereNotNull('locality_id')
            ->groupBy('locality_id')
            ->union(
                DB::table('boundary_proposals')
                    ->select('locality_id as loc_id', DB::raw('COUNT(*) as count'))
                    ->where('status', 'pending')
                    ->whereNotNull('locality_id')
                    ->groupBy('locality_id')
            )
            ->get();

        $merged = [];
        foreach ($byLocality as $row) {
            if (!isset($merged[$row->loc_id])) {
                $merged[$row->loc_id] = 0;
            }
            $merged[$row->loc_id] += (int) $row->count;
        }

        return response()->json([
            'total_pending' => $totalPending,
            'by_locality' => $merged,
        ]);
    }
}