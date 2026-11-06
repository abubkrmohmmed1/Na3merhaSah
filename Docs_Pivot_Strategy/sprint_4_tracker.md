# سجل مهام التنفيذ - Sprint 4 (B2B API Gateway + Enterprise Segments)
**مُحدَّث: 14 مايو 2026**

هذا المستند يتتبع التقدم في الملحمة الثالثة: تأمين وتوثيق وتوسيع الواجهة البرمجية للشركات.

---

## ✅ Sprint 4 — البنية الأساسية للـ B2B API

### Story 4.1: نظام مفاتيح API للشركات ✅
- [x] جدول `companies` (uuid, name, email, phone, plan, status)
- [x] جدول `api_keys` (uuid, company_id FK, key, name, last_used_at, expires_at, revoked_at)
- [x] `App\Domains\Billing\Models\Company` + `App\Domains\Billing\Models\ApiKey`
- [x] `ApiKeyMiddleware` — يتحقق من `X-API-Key` header ويجلِب الشركة
- [x] `TierRateLimit` — Free (100/يوم), Pro (10k/يوم), Enterprise (100k/يوم)
- [x] Admin UI كامل: index, create, edit, show, generate key, revoke key
- [x] ربط الـ middleware في `bootstrap/app.php`

### Story 4.2: البحث الدائري (Radius Search) ✅
- [x] `GET /api/v1/b2b/spatial/radius?lat=&lng=&radius=&layer=&limit=`
- [x] يستخدم `ST_DWithin(geom::geography, ...)` بدقة أمتار
- [x] دعم تصفية حسب الطبقة (اختياري)
- [x] توثيق Swagger (OA annotations)

### Story 4.3: توثيق Swagger ✅
- [x] إصلاح 4 مسارات غلط (ناقص `v1/` في AuthController + AddressingController)
- [x] إضافة توثيق 4 endpoints إدارة (approve/reject submissions + proposals)
- [x] إضافة توثيق Radius Search
- [x] استبعاد DTOs من scan الـ swagger
- [x] توليد `storage/api-docs/api-docs.json` (نجح بدون أخطاء)

### Story 4.4: Company Portal ✅
- [x] صفحة دخول بـ API Key (`/company/login`)
- [x] Dashboard: إحصائيات المنصة (ولايات، محليات، أحياء، معالم)
- [x] عرض مفاتيح API مع حالتها
- [x] API Tester حي في المتصفح (JavaScript Fetch)
- [x] تسجيل خروج

---

## ✅ Sprint 4.5 — منتجات البيانات والويدجتس

### Story 4.5.1: GeoJSON Export ✅
- [x] `GET /api/v1/b2b/export?layer=&format=geojson`
- [x] يدعم: الولايات، المحليات، الأحياء، القرى والمدن، أي طبقة
- [x] Swagger annotations
- [x] Auto-download

### Story 4.5.2: Code Examples ✅
- [x] صفحة في Company Portal تعرض أمثلة لكل endpoint بـ 4 لغات:
  - cURL (قابل للنسخ)
  - PHP (Guzzle)
  - JavaScript (Fetch)
  - Python (requests)

### Story 4.5.3: Postman Collection ✅
- [x] ملف `storage/api-docs/namerha_sah_postman.json` جاهز للاستيراد

### Story 4.5.4: Segment Pages ✅
- [x] صفحة لكل قطاع (عقارات، تأمين، إعلانات، زراعة، سياحة، اتصالات، طوارئ)
- [x] كل صفحة تحتوي: المشكلة ← الحل ← API call + نتيجة
- [x] Code Examples مخصصة لكل قطاع

---

## ✅ Sprint 4.6 — التوسع للمؤسسات

### Story 4.6.1: تحليل القطاعات ✅
- [x] تحليل 7 قطاعات: عقارات، تأمين، إعلانات، زراعة، سياحة، اتصالات، طوارئ
- [x] لكل قطاع: المشكلة، الحل، المنتج المقترح، نموذج الربح

### Story 4.6.2: نموذج الربح لكل قطاع ✅
- [x] العقارات: $50k/شهر (بطاقة عقار + Widget)
- [x] التأمين: $15-25k/شهر (Address Confidence API)
- [x] الإعلانات: $10-20k/شهر (GeoJSON + Geo-fence)
- [x] الزراعة: $15-30k متنوع (Village Directory)
- [x] السياحة: $5-10k/شهر (Hotel Widget)
- [x] الاتصالات: $100k+/سنة (Data License)
- [x] الطوارئ: $15-30k/سنة (عقود حكومية)

### Story 4.6.3: Batch Geocoding API ✅
- [x] `POST /api/v1/b2b/batch/geocode`
- [x] يقبل JSON array بالإحداثيات
- [x] يرجع عناوين + أحياء + S2 Tokens

### Story 4.6.4: Address Autocomplete Widget ✅
- [x] `<script>` embeddable widget
- [x] اكتب اسم الحي → يتكمل العنوان تلقائياً
- [x] يعرض الخريطة + النتيجة

---

## 🔧 الإصلاحات التقنية المرفقة (14 مايو 2026)

| الإصلاح | الملف | الوصف |
|---------|-------|-------|
| bbox معكوس | `ImportNeighborhoodsFromOSM.php` | كان `minLon,minLat,...` والصحيح `south,west,north,east` |
| WKT MultiPolygon | `ImportNeighborhoodsFromOSM.php` | كان ناقص أقواس إضافية لكل polygon |
| نطاق مغلق | `ImportNeighborhoodsFromOSM.php` | الحلقات غير المغلقة تسبب خطأ في PostGIS |
| دعم Node | `ImportNeighborhoodsFromOSM.php` | كان يتجاهل OSM nodes |
| DB::raw Expression | `GeoFeature.php` | `autoDetectParent()` كان يحاول bind الـ Expression كـ parameter |
| Pending badges | `map.blade.php` | كان ينتظر `Point` geometry لكن المحليات `Polygon` |
| Swagger paths | AuthController, AddressingController | `v1/` مفقودة |
| DTOs exclude | `config/l5-swagger.php` | excluded `app/Domains/*/DTOs` |

---

## 📊 إحصائيات Sprint 4

| المقياس | القيمة |
|---------|--------|
| جداول جديدة | 2 |
| Middleware جديدة | 3 |
| Controllers جديدة | 2 |
| Views جديدة | 8 |
| Endpoints جديدة | 10 |
| ملفات تم إصلاحها | 7 |
| ملفات docs تم إنشاؤها | 3 |
| القطاعات المحللة | 7 |
