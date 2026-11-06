# خطة التنفيذ الرشيقة (Agile Sprints & User Stories)
**لمشروع "نعمرها صح" - التحول لمنصة استخبارات مكانية (Geospatial Data Platform)**

تم تقسيم خطة التحول الاستراتيجي إلى (Epics) رئيسية، وتمت صياغتها في شكل (Sprints) لتسهيل التنفيذ.

---

## ✅ الملحمة الأولى: إعادة هيكلة الأساس المعماري (Core Architecture) - [مكتملة 100%]
**الهدف:** تحضير قاعدة البيانات لدعم الطبقات الجغرافية بدلاً من البلاغات الفردية.

### Sprint 1: تأسيس الطبقات والمخزون الجغرافي (Layers & GeoFeatures)
- [x] إنشاء جداول `map_layers` و `geo_features`.
- [x] ربط نظام الإحداثيات بـ PostGIS و S2 Geometry.
- [x] أتمتة حساب الـ Plus Code والـ S2 Cell ID عند الحفظ.

---

## ✅ الملحمة الثانية: تلقيح البيانات وحل البداية الباردة (Data Seeding) - [مكتملة 100%]
**الهدف:** ملء النظام ببيانات حقيقية للمنشآت والطرق (Cold Start Problem).

### Sprint 2: استيراد البيانات المفتوحة (Open Data Ingestion)
- [x] بناء أمر `php artisan map:ingest-osm` لدعم أي (Tag).
- [x] استيراد أكثر من 2,500 معلم جغرافي (صيدليات، مطاعم، بنوك، مخابز، منازل، إلخ).
- [x] تطوير واجهة الخريطة الإدارية بفلتر منسدل (Dropdown) ومفتاح خريطة (Legend) ملون.
- [x] **إضافة (14 مايو):** استيراد أحياء الجزيرة من OSM — 279 → 2,112 حي (1,482 للجزيرة وحدها)
- [x] **إضافة:** دعم `place=*` tags (village, suburb, neighbourhood) في Overpass query
- [x] **إضافة:** إصلاح bbox (كان معكوساً)
- [x] **إضافة:** إصلاح WKT لـ MultiPolygon + إغلاق rings

---

## ✅ الملحمة الثالثة: بوابة الأعمال والتدقيق (B2B Gateway & Data Quality) - [مكتملة 100%]
**الهدف:** تنظيف البيانات وتقديمها كخدمة مدفوعة للشركات.

### Sprint 3: جودة البيانات والتدقيق الإداري (Data Quality & Admin Audit) ✅
- [x] **Story 3.1:** بناء محرك الفلترة الهرمي (State -> Locality -> Neighborhood) باستخدام دالة `ST_Contains` في PostGIS.
- [x] **Story 3.2:** تحويل كافة الحدود الإدارية من (LineString) إلى (Solid Polygon) لضمان تلوين المساحات باحترافية.
- [x] **Story 3.3:** تنظيف قاعدة البيانات وتركيز العمليات على الولايات الاستراتيجية (الخرطوم، الجزيرة، القضارف).
- [x] **Story 3.4:** تطوير واجهة "الأسماء المجهولة" لتمكين المشرفين من تصحيح بيانات OSM ميدانياً.
- [x] **Story 3.5:** (12 مايو) تنظيف الملفات المعطلة وتحويل AdminKPIController لدعم GeoFeatures.
- [x] **Story 3.6:** (12 مايو) إصلاح فلتر الأسماء المجهولة للعمل مع "مجهول" و "Unknown".
- [x] **إضافة (14 مايو):** إصلاح pending badges على الخريطة (Point → Polygon بcentroid)
- [x] **إضافة:** إصلاح `DB::raw` Expression في `autoDetectParent`

---

## ✅ الملحمة الثالثة — ب (B2B API Gateway) - [مكتملة 100%]

### Sprint 4: تأمين وتوثيق واجهة API للشركات (B2B API Gateway) ✅
- [x] **Story 4.1:** نظام مفاتيح API (جدول `companies` + `api_keys`، `ApiKeyMiddleware`، `TierRateLimit`)
- [x] **Story 4.2:** Endpoint البحث الدائري (`/b2b/spatial/radius` مع `ST_DWithin`)
- [x] **Story 4.3:** توثيق Swagger للإضافات الجديدة + إصلاح الـ paths
- [x] **Story 4.4:** Company Portal dashboard (`/company/login` → dashboard مع stats + API tester)

### Sprint 4.5: منتجات البيانات والويدجتس (Data Products & Widgets) ✅
- [x] **Story 4.5.1:** GeoJSON Export API (`/b2b/export?layer=`)
- [x] **Story 4.5.2:** Code Examples (cURL, PHP, JavaScript, Python)
- [x] **Story 4.5.3:** Postman Collection
- [x] **Story 4.5.4:** صفحة القطاعات في Company Portal (لكل قطاع: API examples + Use Cases)

### Sprint 4.6: التوسع للمؤسسات (Enterprise Segments) ✅
- [x] **Story 4.6.1:** تحليل 7 قطاعات مستهدفة (عقارات، تأمين، إعلانات، زراعة، سياحة، اتصالات، طوارئ)
- [x] **Story 4.6.2:** تحديد المنتج المناسب لكل قطاع + نموذج الربح
- [x] **Story 4.6.3:** Batch Geocoding API
- [x] **Story 4.6.4:** Address Autocomplete Widget

---

## 📱 الملحمة الرابعة: تحديث التجربة الرقمية (UX/UI Refresh & Community)
**الهدف:** تحويل التطبيق إلى أداة تفاعلية لجمع وتأكيد البيانات ميدانياً.

### Sprint 5: تحديث واجهات الموبايل (Mobile UI Refresh & UGC) — [التالي]
- [ ] **Story 5.1:** إعادة تصميم الواجهة الرئيسية للموبايل لتصبح "خريطة ذكية" بدلاً من قائمة بلاغات.
- [ ] **Story 5.2:** إضافة طبقات المعالم (POI Layers) في الخريطة داخل الموبايل.
- [ ] **Story 5.3:** تنفيذ نظام "تدقيق المجتمع" (Confirm/Flag inaccurate data) مع نظام نقاط ومكافآت (Gamification).
- [ ] **Story 5.4:** تحسين أداء الخريطة في الموبايل لتعمل بكفاءة في ظروف الإنترنت الضعيفة.

---

## 📈 الملحمة الخامسة: العمليات والنمو (Ops & Growth)
**الهدف:** الموازاة بين البرمجة وبناء السوق وجلب العملاء.

### Sprint 6 (Ops): المبيعات وإدارة البيانات المجتمعية
- [ ] **Story 6.1:** إعداد (Pitch Deck) موجه لشركات التوصيل (ترحال، يلا نطلب) يبرز توفير 30% من تكاليف العنونة.
- [ ] **Story 6.2:** استقطاب 50 "سفير بيانات" من طلاب الجامعات لتدقيق الأحياء ميدانياً.
- [ ] **Story 6.3:** إطلاق أول نسخة تجريبية (PoC) مع شريك لوجستي محلي.
