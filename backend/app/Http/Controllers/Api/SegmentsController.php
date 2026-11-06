<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class SegmentsController extends Controller
{
    #[OA\Get(
        path: '/api/v1/b2b/real-estate/property-card',
        operationId: 'propertyCard',
        summary: 'بطاقة العقار — تفاصيل كاملة عن موقع عقار',
        description: 'تجيب الحي + المحلية + الولاية + S2 Token + عدد الخدمات القريبة (مدارس، مستشفيات، صيدليات، مطاعم) ضمن نصف قطر 500 متر.',
        tags: ['قطاع العقارات'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'lat', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Parameter(name: 'lng', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Response(response: 200, description: 'بطاقة العقار')]
    public function propertyCard(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;

        $point = DB::raw("ST_SetSRID(ST_MakePoint($lng, $lat), 4326)");

        $neighborhood = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الأحياء'))
            ->whereIn('status', ['active', 'verified'])
            ->whereRaw("ST_Contains(geom, $point)")
            ->select('id', DB::raw("properties->>'name' as name"))
            ->first();

        $locality = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'المحليات'))
            ->whereIn('status', ['active', 'verified'])
            ->whereRaw("ST_Contains(geom, $point)")
            ->select('id', DB::raw("properties->>'name' as name"))
            ->first();

        $state = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الولايات'))
            ->whereIn('status', ['active', 'verified'])
            ->whereRaw("ST_Contains(geom, $point)")
            ->select('id', DB::raw("properties->>'name' as name"))
            ->first();

        // Count nearby POIs within 500m
        $nearby = DB::selectOne("
            SELECT
                COUNT(*) FILTER (WHERE ml.name = 'المدارس') as schools,
                COUNT(*) FILTER (WHERE ml.name = 'المستشفيات') as hospitals,
                COUNT(*) FILTER (WHERE ml.name = 'الصيدليات') as pharmacies,
                COUNT(*) FILTER (WHERE ml.name = 'المطاعم') as restaurants,
                COUNT(*) FILTER (WHERE ml.name = 'البقالات الكبيرة') as groceries,
                COUNT(*) FILTER (WHERE ml.name = 'البنوك') as banks,
                COUNT(*) FILTER (WHERE ml.name = 'المساجد' OR ml.name LIKE '%مسجد%') as mosques
            FROM geo_features gf
            JOIN map_layers ml ON ml.id = gf.layer_id
            WHERE gf.status IN ('active', 'verified')
              AND ST_DWithin(gf.geom::geography, ST_MakePoint($lng, $lat)::geography, 500)
        ");

        $s2Service = app(\App\Domains\Spatial\Services\S2GeometryService::class);
        $s2Token = $s2Service->latLngToToken($lat, $lng);
        $plusCode = $s2Service->generatePlusCode($lat, $lng);

        return response()->json([
            'property' => [
                'lat' => $lat,
                'lng' => $lng,
                's2_token' => $s2Token,
                'plus_code' => $plusCode,
                'state' => $state?->name,
                'locality' => $locality?->name,
                'neighborhood' => $neighborhood?->name,
            ],
            'nearby_services' => [
                'schools' => (int) ($nearby->schools ?? 0),
                'hospitals' => (int) ($nearby->hospitals ?? 0),
                'pharmacies' => (int) ($nearby->pharmacies ?? 0),
                'restaurants' => (int) ($nearby->restaurants ?? 0),
                'groceries' => (int) ($nearby->groceries ?? 0),
                'banks' => (int) ($nearby->banks ?? 0),
                'mosques' => (int) ($nearby->mosques ?? 0),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/v1/b2b/insurance/verify-address',
        operationId: 'verifyAddress',
        summary: 'التحقق من العنوان ودرجة الثقة',
        description: 'يتحقق من صحة عنوان بناءً على إحداثياته ويرجع درجة الثقة (0-100%) والموقع الفعلي.',
        tags: ['قطاع التأمين'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'lat', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Parameter(name: 'lng', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Parameter(name: 'claimed_address', in: 'query', required: false, description: 'العنوان المدّعى (اختياري — للمقارنة)', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'نتيجة التحقق')]
    public function verifyAddress(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'claimed_address' => 'nullable|string|max:500',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;

        $neighborhood = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الأحياء'))
            ->whereIn('status', ['active', 'verified'])
            ->whereRaw("ST_Contains(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])
            ->select('id', DB::raw("properties->>'name' as name"))
            ->first();

        $hasLocality = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'المحليات'))
            ->whereIn('status', ['active', 'verified'])
            ->whereRaw("ST_Contains(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])
            ->exists();
        $hasState = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الولايات'))
            ->whereIn('status', ['active', 'verified'])
            ->whereRaw("ST_Contains(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])
            ->exists();

        $confidence = 10;
        if ($neighborhood) $confidence = 97;
        elseif ($hasLocality) $confidence = 82;
        elseif ($hasState) $confidence = 55;

        $result = [
            'verified' => $neighborhood !== null,
            'confidence_score' => $confidence,
            'actual_neighborhood' => $neighborhood?->name ?? 'غير معروف',
        ];

        if ($request->claimed_address) {
            $match = false;
            if ($neighborhood) {
                $match = mb_stripos($request->claimed_address, $neighborhood->name) !== false;
            }
            $result['claimed_address'] = $request->claimed_address;
            $result['address_match'] = $match;
            if (!$match && $neighborhood) {
                $result['suggested_address'] = $neighborhood->name;
            }
        }

        return response()->json($result);
    }

    #[OA\Get(
        path: '/api/v1/b2b/insurance/risk-profile',
        operationId: 'riskProfile',
        summary: 'تقييم المخاطر لموقع',
        description: 'أقرب مستشفى، مركز إطفاء، مركز شرطة من موقع معين — لتقييم مخاطر التأمين.',
        tags: ['قطاع التأمين'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'lat', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Parameter(name: 'lng', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float'))]
    #[OA\Response(response: 200, description: 'ملف المخاطر')]
    public function riskProfile(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;

        $nearestHospital = DB::selectOne("
            SELECT properties->>'name' as name,
                   ST_Distance(geom::geography, ST_MakePoint(?, ?)::geography) as distance
            FROM geo_features gf
            JOIN map_layers ml ON ml.id = gf.layer_id
            WHERE ml.name = 'المستشفيات' AND gf.status IN ('active', 'verified')
            ORDER BY geom::geography <-> ST_MakePoint(?, ?)::geography
            LIMIT 1
        ", [$lng, $lat, $lng, $lat]);

        $nearestPolice = DB::selectOne("
            SELECT properties->>'name' as name,
                   ST_Distance(geom::geography, ST_MakePoint(?, ?)::geography) as distance
            FROM geo_features gf
            JOIN map_layers ml ON ml.id = gf.layer_id
            WHERE ml.name = 'مراكز الشرطة' AND gf.status IN ('active', 'verified')
            ORDER BY geom::geography <-> ST_MakePoint(?, ?)::geography
            LIMIT 1
        ", [$lng, $lat, $lng, $lat]);

        $hospitalCount = DB::selectOne("
            SELECT COUNT(*) as cnt FROM geo_features gf
            JOIN map_layers ml ON ml.id = gf.layer_id
            WHERE ml.name = 'المستشفيات' AND gf.status IN ('active', 'verified')
              AND ST_DWithin(gf.geom::geography, ST_MakePoint(?, ?)::geography, 5000)
        ", [$lng, $lat]);

        $fireStationCount = DB::selectOne("
            SELECT COUNT(*) as cnt FROM geo_features gf
            JOIN map_layers ml ON ml.id = gf.layer_id
            WHERE ml.name = 'مراكز الإطفاء' AND gf.status IN ('active', 'verified')
              AND ST_DWithin(gf.geom::geography, ST_MakePoint(?, ?)::geography, 5000)
        ", [$lng, $lat]);

        return response()->json([
            'nearest_hospital' => [
                'name' => $nearestHospital?->name ?? 'غير متوفر',
                'distance_meters' => round((float) ($nearestHospital?->distance ?? 0)),
            ],
            'nearest_police_station' => [
                'name' => $nearestPolice?->name ?? 'غير متوفر',
                'distance_meters' => round((float) ($nearestPolice?->distance ?? 0)),
            ],
            'hospitals_within_5km' => (int) ($hospitalCount->cnt ?? 0),
            'fire_stations_within_5km' => (int) ($fireStationCount->cnt ?? 0),
        ]);
    }

    #[OA\Get(
        path: '/api/v1/b2b/advertising/neighborhood-stats',
        operationId: 'neighborhoodStats',
        summary: 'إحصائيات حي — للتخطيط الإعلاني',
        description: 'إحصائيات كاملة عن حي: عدد المنشآت، توزيع الخدمات، أشهر الأنشطة.',
        tags: ['قطاع الإعلانات'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'neighborhood_id', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'إحصائيات الحي')]
    public function neighborhoodStats(Request $request)
    {
        $request->validate(['neighborhood_id' => 'required|uuid']);

        $neighborhood = GeoFeature::with('mapLayer')->find($request->neighborhood_id);
        if (!$neighborhood || $neighborhood->mapLayer?->name !== 'الأحياء') {
            return response()->json(['message' => 'الحي غير موجود'], 404);
        }

        // Only use the neighborhood name from properties for lookup
        $neighborhoodName = $neighborhood->properties['name'] ?? '';
        $stats = DB::select("
            SELECT ml.name as service, COUNT(*) as count
            FROM geo_features gf
            JOIN map_layers ml ON ml.id = gf.layer_id
            WHERE ml.name IN ('المطاعم','المقاهي','المخابز','محطات الوقود',
                  'الصيدليات','المستشفيات','البنوك','الصرافات الآلية',
                  'البقالات الكبيرة','البقالات الصغيرة','المدارس',
                  'مراكز التسوق','الفنادق')
              AND gf.status IN ('active', 'verified')
              AND ST_Contains((SELECT geom FROM geo_features WHERE id = ?), gf.geom)
            GROUP BY ml.name
            ORDER BY count DESC
        ", [$request->neighborhood_id]);

        $stats = array_map(fn($row) => ['service' => $row->service, 'count' => (int) $row->count], $stats);

        return response()->json([
            'neighborhood' => $neighborhoodName,
            'total_features' => array_sum(array_column($stats, 'count')),
            'services' => $stats,
        ]);
    }

    public function b2bProfile(Request $request)
    {
        $company = $request->get('api_company');
        $apiKey = $request->get('api_key_record');

        $usageToday = DB::table('api_usage_logs')
            ->where('company_id', $company->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        $usageMonth = DB::table('api_usage_logs')
            ->where('company_id', $company->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        return response()->json([
            'company' => [
                'name' => $company->name,
                'plan' => $company->plan,
                'status' => $company->status,
            ],
            'api_key' => [
                'name' => $apiKey->name,
                'last_used_at' => $apiKey->last_used_at,
            ],
            'usage' => [
                'today' => $usageToday,
                'this_month' => $usageMonth,
                'limit_daily' => ($company->plan === 'Pro') ? 10000 : (($company->plan === 'Enterprise') ? 100000 : 100),
            ]
        ]);
    }
}
