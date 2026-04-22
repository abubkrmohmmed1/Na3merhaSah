# لوحة مهام السبرينتات: مشروع "نعمرها صح" (Sprint Board)

هذه الوثيقة تمثل حالة المهام التشغيلية لكل سبرينت، لتُستخدم كمرجع لحظي لمدى تقدمنا في الجانب العملي. سيتم تحديث هذا المستند تلقائياً معتقدم العمل.

---

## السبرينت 1: البنية التحتية والبيانات المكانية (Infrastructure & Spatial DB)
**الحالة العامة:** (تم إنجازه ✅)

| م | المهمة | التفاصيل (Task Description) | الحالة | المسؤول |
|---|---|---|---|---|
| 1.1 | تهيئة واجهة المواطن (Frontend) | إنشاء مشروع Flutter وإعداد مجلدات `Feature-First` ومكتبات `core`. | ✅ مكتمل | Tech Lead |
| 1.2 | تهيئة الخوادم الخلفية (Backend) | تنصيب مشروع Laravel باستخدام Composer للواجهة الخلفية. | ✅ مكتمل | Tech Lead |
| 1.3 | تطبيق هيكل DDD للـ Backend | إنشاء مجلدات Domain-Driven Design وهي (Addressing, Reporting, Spatial). | ✅ مكتمل | Tech Lead |
| 1.4 | تجهيز قاعدة البيانات PostGIS | تنصيب PostgreSQL + PostGIS وإعداد قاعدة بيانات `namerha_sah_db`. | ✅ مكتمل | Tech Lead / DBA |
| 1.5 | إعداد `dotenv` والـ Migrations | ربط Laravel مع قاعدة البيانات وتجهيز جداول (reports, addresses) للبيانات المكانية. | ✅ مكتمل | Tech Lead |
| 1.6 | إدراج S2 Geometry | استدعاء مكاتب S2 Geometry وتجهيز الـ Models للتعامل مع الـ Spatial Indexing. | ✅ مكتمل | Tech Lead |
| 1.7 | مواءمة DB مع الـ Workflow والـ UI | تحديث البلاغات بـ SoftDeletes وحالات الـ State Machine وربطها بالعناوين `address_id`. | ✅ مكتمل | Tech Lead |

---

## السبرينت 2: برمجة خدمات العنونة (Addressing Logic)
**الحالة العامة:** (تم إنجازه ✅)

| م | المهمة | التفاصيل (Task Description) | الحالة | المسؤول |
|---|---|---|---|---|
| 2.1 | Reverse Geocoding API | بناء Endpoint تستلم (Lat/Lng) وترجع تفاصيل العنوان الجغرافي عبر S2. | ✅ مكتمل | Backend Engineer |
| 2.2 | خوارزمية الإكمال التلقائي | تفعيل Auto-complete سريع للعناوين باستخدام Search Indexing ومؤشرات S2. | ✅ مكتمل | Backend Engineer |
| 2.3 | Endpoint استقبال البلاغ (POST) | دعم `multipart/form-data` للصور والتفاصيل، وتنفيذ **Spatial Join** فوري مع الـ `address_id`. | ✅ مكتمل | Backend Engineer |
| 2.4 | Endpoints قوائم البلاغات (GET) | توظيف `ReportResource` كبيانات مختزلة للـ UI، مع تفعيل صلاحيات الأدوار (المواطن يرى بلاغه، الجهة ترى بلاغات نطاقها المكاني). | ✅ مكتمل | Backend Engineer |

---

## السبرينت 3: تطبيق المواطن (Flutter App & Map UI)
**الحالة العامة:** (تم إنجازه ✅)

| م | المهمة | التفاصيل (Task Description) | الحالة | المسؤول |
|---|---|---|---|---|
| 3.1 | التوثيق وإدارة الصلاحيات | ربط Sanctum وتفعيل نظام الأدوار وتصفية البلاغات حسب هوية المستخدم (Auth Filtering). | ✅ مكتمل | Tech Lead |
| 3.2 | واجهات التسجيل المتقدمة | إضافة حقول (الرقم الوطني، عنوان السكن) مع التقاط إحداثيات المنزل تلقائياً للتوثيق. | ✅ مكتمل | Front Engineer |
| 3.3 | الخرائط المصغرة والتصفية | تفعيل الخرائط المصغرة (Lite Mode) وشريط تصفية الحالات في قائمة التقارير السابقة. | ✅ مكتمل | Front Engineer |

---

## السبرينت 4: لوحة تحكم الإدارة (Dashboard & Analytics)
**الحالة العامة:** (مخطط له 📅)

| م | المهمة | التفاصيل (Task Description) | الحالة | المسؤول |
|---|---|---|---|---|
| 4.1 | لوحة الإدارة Backend و UI | بناء الهيكل الأساسي للوحة تحكم الإدارة باستخدام (Livewire أو Vue.js). | ⏸️ لم يبدأ | Backend/UI |
| 4.2 | الخريطة الحرارية (Heatmap) | قراءة الإحداثيات من الـ (Materialized Views) وعرض نقاط التكدس الجغرافية للبلاغات. | ⏸️ لم يبدأ | GIS/Backend |
| 4.3 | توجيه وتوزيع المهام آلياً | ميكنة توجيه البلاغ للقسم الإداري المسؤول (البلدية المعنية) استناداً إلى تقاطع الموقع مع مضلع (Polygon) الحي. | ⏸️ لم يبدأ | Tech Lead |

---

## Sprint 5: Workflow Implementation & Step Validation
**Overall Status:** (Completed) 

| # | Task | Task Description | Status | Owner |
|---|---|---|---|---|
| 5.1 | Extend Report Model with Workflow Metadata | Add workflow_step and workflow_metadata fields to existing Report model with JSON casting. | ✅ Completed | Tech Lead |
| 5.1.1 | Database Migration for Workflow Fields | Create and run a migration to add `workflow_step` (enum) and `workflow_metadata` (json) to the `reports` table. | ✅ Completed | Tech Lead |
| 5.2 | Enhance ReportIssueAction with Step Validation | Extend existing action to support step-based validation, completed steps tracking, and validation status. | Completed | Tech Lead |
| 5.3 | Add Workflow State Management to ReportBloc | Add UpdateWorkflowStep event and WorkflowStepUpdated state for real-time workflow tracking. | Completed | Tech Lead |
| 5.4 | Implement Workflow Step Tracking in UI | Add workflow step tracking, timestamp recording, and progress monitoring to ReportIssueScreen. | Completed | Tech Lead |
| 5.5 | Create WorkflowValidator Helper Class | Build comprehensive validation helper with step validation, progress calculation, and error handling. | Completed | Tech Lead |
| 5.6 | Create Workflow Tests | Implement feature tests for workflow validation, progress tracking, and report creation with metadata. | Completed | Tech Lead |

**Key Features Delivered:**
- Multi-step workflow validation (Location, Category, Description, Images, Review)
- Real-time progress tracking with percentage completion
- Workflow metadata storage with timestamps
- Step-based error handling and warnings
- Offline support for workflow data
- Integration with existing spatial services and API endpoints

---

## السبرينت 6: لوحة تحكم الإدارة (Dashboard & Analytics)
**الحالة العامة:** (مخطط له 📅)

| م | المهمة | التفاصيل (Task Description) | الحالة | المسؤول |
|---|---|---|---|---|
| 6.1 | لوحة الإدارة Backend و UI | بناء الهيكل الأساسي للوحة تحكم الإدارة باستخدام (Livewire أو Vue.js). | ⏸️ لم يبدأ | Backend/UI |
| 6.2 | الخريطة الحرارية (Heatmap) | قراءة الإحداثيات من الـ (Materialized Views) وعرض نقاط التكدس الجغرافية للبلاغات. | ⏸️ لم يبدأ | GIS/Backend |
| 6.3 | توجيه وتوزيع المهام آلياً | ميكنة توجيه البلاغ للقسم الإداري المسؤول (البلدية المعنية) استناداً إلى تقاطع الموقع مع مضلع (Polygon) الحي. | ⏸️ لم يبدأ | Tech Lead |
