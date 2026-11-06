<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Domains\Billing\Models\ApiKey;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Spatial\Models\GeoFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortalController extends Controller
{
    public function login()
    {
        return view('company.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
        ]);

        $apiKey = ApiKey::where('key', $request->api_key)
            ->with('company')
            ->valid()
            ->first();

        if (!$apiKey) {
            return back()->with('error', 'مفتاح API غير صالح أو منتهي الصلاحية');
        }

        session(['company_id' => $apiKey->company->id, 'company_api_key_id' => $apiKey->id]);

        return redirect()->route('company.dashboard');
    }

    public function logout()
    {
        session()->forget(['company_id', 'company_api_key_id']);
        return redirect()->route('company.login');
    }

    public function segmentDetail(Request $request, string $segment)
    {
        $company = $request->get('api_company');
        $apiKey = $request->get('api_key');

        $data = [
            'real_estate' => [
                'name' => 'العقارات', 'icon' => '🏠',
                'problem' => 'المكاتب العقارية تبيع أوهاماً — "بيت في الحاج يوسف" بدون عنوان دقيق. المشتري ما يقدر يتأكد من الموقع أو الخدمات المحيطة.',
                'solution' => 'بطاقة العقار الرقمية: إحداثيات دقيقة + الحي + عدد المدارس والمستشفيات والصيدليات والمطاعم ضمن 500 متر. API متخصص يرجع كل حاجة في طلب واحد.',
                'endpoints' => [
                    ['name' => 'بطاقة العقار 🏷️', 'method' => 'GET', 'url' => '/api/v1/b2b/real-estate/property-card?lat=15.5871&lng=32.5232', 'desc' => 'الحي + المحلية + الولاية + S2 Token + Plus Code + 7 أنواع خدمات قريبة'],
                    ['name' => 'الخدمات القريبة', 'method' => 'GET', 'url' => '/api/v1/b2b/spatial/radius?lat=15.5871&lng=32.5232&radius=500&limit=20', 'desc' => 'تفاصيل الخدمات (أسماء) حول العقار'],
                    ['name' => 'ويدجت الخريطة', 'method' => 'Embed', 'url' => '/company/widget/autocomplete', 'desc' => 'خريطة تفاعلية في موقعك — اضغط على الخريطة يجيب العنوان'],
                ],
                'product' => 'بطاقة العقار API + Location Widget',
                'price' => '$100/شهر للمكتب، $0.10/بطاقة',
                'curl' => "curl -X GET \"http://localhost:8000/api/v1/b2b/real-estate/property-card?lat=15.5871&lng=32.5232\" \\\n  -H \"X-API-Key: YOUR_KEY\"",
            ],
            'insurance' => [
                'name' => 'التأمين', 'icon' => '🛡️',
                'problem' => 'شركات التأمين تخسر ملايين بسبب مطالبات وهمية. لا يوجد نظام للتحقق من عنوان العميل. لا يمكن تقييم المخاطر حسب المنطقة.',
                'solution' => 'Address Confidence API: يتأكد هل العنوان حقيقي ويرجع نسبة ثقة. Risk Profile API: أقرب مستشفى + مطافئ + مركز شرطة. Batch Claims: CSV مطالبات ← تحقق آلي.',
                'endpoints' => [
                    ['name' => 'التحقق من العنوان ✅', 'method' => 'GET', 'url' => '/api/v1/b2b/insurance/verify-address?lat=15.5871&lng=32.5232&claimed_address=الرياض', 'desc' => 'نسبة الثقة (0-100%) + مطابقة العنوان المدّعى'],
                    ['name' => 'ملف المخاطر 🔥', 'method' => 'GET', 'url' => '/api/v1/b2b/insurance/risk-profile?lat=15.5871&lng=32.5232', 'desc' => 'أقرب مستشفى، أقرب مركز شرطة، عدد المستشفيات ضمن 5 كم'],
                    ['name' => 'معالجة دفعة 📋', 'method' => 'POST', 'url' => '/api/v1/b2b/batch/geocode', 'desc' => 'رفع 500 نقطة ← التحقق من الكل'],
                ],
                'product' => 'Address Confidence API + Risk Profile API + Batch',
                'price' => '$0.05/request، $500-1000/دفعة',
                'curl' => "curl -X GET \"http://localhost:8000/api/v1/b2b/insurance/verify-address?lat=15.5871&lng=32.5232\" \\\n  -H \"X-API-Key: YOUR_KEY\"",
            ],
            'advertising' => [
                'name' => 'الإعلانات', 'icon' => '📢',
                'problem' => '"أريد إعلاني يوصل سكان الرياض والطائف فقط" — لكن حدود الأحياء غير متوفرة رقمياً. اللوحات توضع بدون دراسة.',
                'solution' => 'Neighborhood Stats API: إحصائيات كل حي (كم مطعم، بنك، صيدلية...). GeoJSON Export: حدود الأحيان جاهزة للـ GIS.',
                'endpoints' => [
                    ['name' => 'إحصائيات الحي 📊', 'method' => 'GET', 'url' => '/api/v1/b2b/advertising/neighborhood-stats?neighborhood_id={id}', 'desc' => 'إحصائيات كاملة عن حي: المطاعم، المقاهي، المدارس، محطات الوقود...'],
                    ['name' => 'تصدير الأحياء 🗺️', 'method' => 'GET', 'url' => '/api/v1/b2b/export?layer=الأحياء', 'desc' => 'حدود 2,112 حي بصيغة GeoJSON مع auto-download'],
                ],
                'product' => 'Neighborhood Stats API + GeoJSON Export',
                'price' => '$1-3k ترخيص تصدير، $0.02/request',
                'curl' => "curl -X GET \"http://localhost:8000/api/v1/b2b/advertising/neighborhood-stats?neighborhood_id=NEIGHBORHOOD_ID\" \\\n  -H \"X-API-Key: YOUR_KEY\"",
            ],
            'agriculture' => [
                'name' => 'الزراعة', 'icon' => '🌾',
                'problem' => '677 قرية بدون إحداثيات دقيقة. توزيع المدخلات الزراعية يضيع. تسجيل المزارعين للتمويل صعب بدون نظام عنونة ريفي.',
                'solution' => 'Village Directory API: كل القرى مع إحداثياتها. Batch Geocoding: أسماء القرى → إحداثيات مع المحلية والولاية.',
                'endpoints' => [
                    ['name' => 'القرى', 'method' => 'GET', 'url' => '/api/v1/b2b/spatial/radius?lat=14.4&lng=33.5&radius=50000', 'desc' => 'جلب القرى حول منطقة'],
                    ['name' => 'Batch Geocode 📦', 'method' => 'POST', 'url' => '/api/v1/b2b/batch/geocode', 'desc' => 'رفع قائمة إحداثيات ← الحصول على المحلية + الولاية + الحي'],
                    ['name' => 'تصدير القرى', 'method' => 'GET', 'url' => '/api/v1/b2b/export?layer=القرى والمدن', 'desc' => 'جميع القرى المسجلة'],
                ],
                'product' => 'Village Directory API + Batch Geocoding',
                'price' => '$500/شهر للقطاع الخاص، عقود منظمات $10-30k',
                'curl' => "curl -X POST \"http://localhost:8000/api/v1/b2b/batch/geocode\" \\\n  -H \"Content-Type: application/json\" \\\n  -H \"X-API-Key: YOUR_KEY\" \\\n  -d '{\"points\":[{\"lat\":14.4,\"lng\":33.5}]}'",
            ],
            'tourism' => [
                'name' => 'السياحة', 'icon' => '🏨',
                'problem' => 'الفنادق لا تستطيع عرض "شنو حوالي الفندق" للسائح. 794 مطعم و 114 فندق مسجل — فرصة لربطهم.',
                'solution' => 'Nearby POI Widget: خريطة + قائمة الخدمات القريبة من الفندق. Nearby API: مطاعم ومقاهي وأسواق ضمن 500 متر.',
                'endpoints' => [
                    ['name' => 'قرب الخدمات', 'method' => 'GET', 'url' => '/api/v1/b2b/spatial/radius?lat=15.5871&lng=32.5232&radius=500&limit=20', 'desc' => 'مطاعم ومقاهي وأسواق قريبة من الفندق'],
                    ['name' => 'بطاقة موقع الفندق', 'method' => 'GET', 'url' => '/api/v1/b2b/real-estate/property-card?lat=15.5871&lng=32.5232', 'desc' => 'تقرير كامل عن موقع الفندق + الخدمات القريبة'],
                ],
                'product' => 'Nearby API + Hotel Location Widget',
                'price' => '$100-200/شهر للفندق',
                'curl' => "curl -X GET \"http://localhost:8000/api/v1/b2b/spatial/radius?lat=15.5871&lng=32.5232&radius=500\" \\\n  -H \"X-API-Key: YOUR_KEY\"",
            ],
            'telecom' => [
                'name' => 'الاتصالات', 'icon' => '📡',
                'problem' => 'في نركب الأبراج؟ في نمد الألياف؟ لا توجد خرائط إدارية دقيقة للتخطيط.',
                'solution' => 'Admin Boundaries License: كل الحدود (ولاية/محلية/حي) بصيغة GeoJSON. تحليل كثافة المنشآت لتحديد المناطق ذات النشاط التجاري العالي.',
                'endpoints' => [
                    ['name' => 'تصدير الولايات', 'method' => 'GET', 'url' => '/api/v1/b2b/export?layer=الولايات', 'desc' => 'حدود 19 ولاية — GeoJSON download'],
                    ['name' => 'تصدير المحليات', 'method' => 'GET', 'url' => '/api/v1/b2b/export?layer=المحليات', 'desc' => 'حدود 193 محلية'],
                    ['name' => 'تصدير الأحياء', 'method' => 'GET', 'url' => '/api/v1/b2b/export?layer=الأحياء', 'desc' => 'حدود 2,112 حي'],
                ],
                'product' => 'Admin Boundaries License + Coverage Reports',
                'price' => '$20-50k/سنة ترخيص بيانات',
                'curl' => "curl -X GET \"http://localhost:8000/api/v1/b2b/export?layer=المحليات\" \\\n  -H \"X-API-Key: YOUR_KEY\" -o localities.geojson",
            ],
            'emergency' => [
                'name' => 'الطوارئ', 'icon' => '🚑',
                'problem' => 'بلاغ عن حادث: "في شارع النيل" — وين؟ أقرب سيارة إسعاف من الحادث وين؟',
                'solution' => 'Reverse Geocode API: أي إحداثيات ← عنوان + حي + S2 Token. Risk Profile API: أقرب مستشفى ومركز شرطة. مناسب لغرف الطوارئ المجتمعية.',
                'endpoints' => [
                    ['name' => 'تحديد موقع', 'method' => 'GET', 'url' => '/api/v1/b2b/addresses/reverse?lat=15.5871&lng=32.5232', 'desc' => 'العنوان + الحي + S2 Token من أي إحداثيات'],
                    ['name' => 'أقرب مستشفى', 'method' => 'GET', 'url' => '/api/v1/b2b/insurance/risk-profile?lat=15.5871&lng=32.5232', 'desc' => 'أقرب مستشفى + المسافة + عدد المستشفيات ضمن 5 كم'],
                ],
                'product' => 'Emergency Location API (مجاني للجهات الرسمية)',
                'price' => 'مجاني للطوارئ — عقود حكومية $15-30k/سنة',
                'curl' => "curl -X GET \"http://localhost:8000/api/v1/b2b/addresses/reverse?lat=15.5871&lng=32.5232\" \\\n  -H \"X-API-Key: YOUR_KEY\"",
            ],
        ];

        $segment = $data[$segment] ?? null;
        if (!$segment) abort(404);

        return view('company.segment_detail', compact('company', 'apiKey', 'segment'));
    }

    public function codeExamples(Request $request)
    {
        $company = $request->get('api_company');
        $apiKey = $request->get('api_key');
        return view('company.code_examples', compact('company', 'apiKey'));
    }

    public function segments(Request $request)
    {
        $company = $request->get('api_company');
        $apiKey = $request->get('api_key');

        $segments = [
            'real_estate' => ['name' => 'العقارات', 'icon' => '🏠', 'color' => '#4B7DF3'],
            'insurance' => ['name' => 'التأمين', 'icon' => '🛡️', 'color' => '#10B981'],
            'advertising' => ['name' => 'الإعلانات', 'icon' => '📢', 'color' => '#F59E0B'],
            'agriculture' => ['name' => 'الزراعة', 'icon' => '🌾', 'color' => '#8B5CF6'],
            'tourism' => ['name' => 'السياحة', 'icon' => '🏨', 'color' => '#EC4899'],
            'telecom' => ['name' => 'الاتصالات', 'icon' => '📡', 'color' => '#06B6D4'],
            'emergency' => ['name' => 'الطوارئ', 'icon' => '🚑', 'color' => '#EF4444'],
        ];

        return view('company.segments', compact('company', 'apiKey', 'segments'));
    }

    public function myLocations(Request $request)
    {
        $company = $request->get('api_company');
        $apiKey = $request->get('api_key');

        $locations = \App\Domains\Spatial\Models\GeoFeature::where('status', 'verified')
            ->whereJsonContains('properties->verified_by', $company->id)
            ->with('mapLayer')
            ->orderBy('created_at', 'desc')
            ->get();

        $layers = \App\Domains\Spatial\Models\MapLayer::where('is_public', true)
            ->orWhere('name', 'like', '٪')
            ->orderBy('name')
            ->get();

        return view('company.my_locations', compact('company', 'apiKey', 'locations', 'layers'));
    }

    public function addLocation(Request $request)
    {
        $company = $request->get('api_company');

        $request->validate([
            'name' => 'required|string|max:255',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'layer' => 'required|string|max:100',
        ]);

        $layer = \App\Domains\Spatial\Models\MapLayer::firstOrCreate(
            ['name' => $request->layer],
            ['description' => 'مواقع مؤسسية', 'type' => 'points', 'is_public' => true]
        );

        $feature = \App\Domains\Spatial\Models\GeoFeature::create([
            'layer_id' => $layer->id,
            'geometry_wkt' => sprintf('POINT(%F %F)', $request->lng, $request->lat),
            'properties' => [
                'name' => $request->name,
                'verified_by' => $company->id,
                'verified_by_name' => $company->name,
                'source' => 'company_portal',
            ],
            'status' => 'verified',
        ]);

        return redirect()->route('company.my-locations')
            ->with('success', '✅ تم إضافة الموقع وتوثيقه مباشرة');
    }

    public function dashboard(Request $request)
    {
        $company = $request->get('api_company');
        $apiKey = $request->get('api_key');

        $company->load('apiKeys');

        // Count features
        $totalFeatures = GeoFeature::count();
        $totalKeys = $company->apiKeys->count();
        $activeKeys = $company->apiKeys->whereNull('revoked_at')->count();

        // Layer breakdown
        $layers = MapLayer::select('name')
            ->withCount(['geoFeatures as feature_count' => function ($q) {
                $q->whereIn('status', ['active', 'verified']);
            }])
            ->orderByDesc('feature_count')
            ->take(10)
            ->get();

        // Quick stats
        $states = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الولايات'))
            ->whereIn('status', ['active', 'verified'])->count();
        $localities = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'المحليات'))
            ->whereIn('status', ['active', 'verified'])->count();
        $neighborhoods = GeoFeature::whereHas('mapLayer', fn($q) => $q->where('name', 'الأحياء'))
            ->whereIn('status', ['active', 'verified'])->count();

        return view('company.dashboard', [
            'company' => $company,
            'apiKey' => $apiKey,
            'data' => compact('totalFeatures', 'totalKeys', 'activeKeys', 'layers', 'states', 'localities', 'neighborhoods'),
        ]);
    }
}
