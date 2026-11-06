# سجل مهام التنفيذ - Sprint 2 (تلقيح البيانات المفتوحة)

هذا المستند يتتبع التقدم في الملحمة الثانية: سحب البيانات آلياً من OpenStreetMap لتعبئة الخريطة الأولية للشركات.

### ✅ قصة المستخدم 2.1: بناء الأمر البرمجي (Console Command)
- [x] تنفيذ أمر `make:command IngestOpenStreetMapData`.
- [x] إعداد الاتصال بـ Overpass API باستخدام `Illuminate\Support\Facades\Http`.
- [x] إضافة (Headers) مخصصة (`User-Agent`) لتخطي حماية المنع من الخادم (Error 406).

### ✅ قصة المستخدم 2.2: جلب البيانات وحقنها (Data Parsing & Ingestion)
- [x] كتابة استعلام Overpass QL لجلب (الصيدليات، المخابز، المستشفيات) مع تحديد الصندوق الجغرافي (Bounding Box) للخرطوم.
- [x] مطابقة أو إنشاء طبقة (MapLayer) مناسبة.
- [x] حفظ البيانات في `GeoFeature` واستغلال توليد `s2_cell_id` الأوتوماتيكي.
- [x] تشغيل السكربت ونجاح استيراد **731 صيدلية** كأول طبقة مكتملة!

### ✅ إضافات 14 مايو 2026 — استيراد الأحياء من OSM
- [x] إصلاح bbox (كان معكوساً: west/south/east/north بدل south/west/north/east)
- [x] إصلاح WKT لـ MultiPolygon: إضافة أقواس إضافية لكل polygon
- [x] إصلاح الحلقات غير المغلقة
- [x] إضافة معالجة OSM nodes (كان يتجاهل 2000 node!)
- [x] تحديث query الـ Overpass ليشمل `place=*` (village, suburb, neighbourhood)
- [x] إصلاح `getBoundsFromFeature` (WKB parameter cast)
- [x] النتيجة: **279 → 2,112 حي** (منها 1,482 في الجزيرة، + النيل الأبيض 145، سنار 58، القضارف 24)

---
*حالة السبرينت:* **مكتمل بنسبة 100% 🚀**
