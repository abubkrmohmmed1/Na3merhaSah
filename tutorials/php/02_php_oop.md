# 🐘 الدرس الثاني (الموسع): البرمجة كائنية التوجه (OOP) المتقدمة في "نعمّرها صح"

في هذا الدرس سنتعمق في **البرمجة كائنية التوجه (OOP)**. سنشرح مفاهيمها الأربعة الأساسية (التغليف، الوراثة، التعددية، التجريد) وكيف تنظم كود مشروعك وتجعله قوياً وآمناً.

---

## 1. المفاهيم الأساسية الأربعة لـ OOP وكيف تظهر في مشروعك

### أ. التغليف (Encapsulation) وحماية الخصائص:
التغليف هو إخفاء البيانات الداخلية للكائن عن العالم الخارجي والتحكم في كيفية تعديلها من خلال محددات الوصول (Access Modifiers):
* **`public`**: يمكن الوصول للمتغير أو الدالة من أي مكان خارج الكلاس.
* **`protected`**: يمكن الوصول إليها فقط من داخل الكلاس والكلاسات التي ترث منه.
* **`private`**: لا يمكن لأي كائن آخر خارج الكلاس الوصول إليها مطلقاً.

* **تطبيق من الكود الفعلي:** افتح ملف [SubmissionsReviewController.php](file:///c:/namerha_sah/backend/app/Http/Controllers/Admin/SubmissionsReviewController.php) (السطر 16). ستجد تعريف الدوال كـ `public`:
  ```php
  public function index(Request $request): View
  ```
  هذا يتيح لنظام التوجيه (Routing) في Laravel استدعاء هذه الدالة من الخارج لمعالجة الطلبات القادمة من الويب.

---

### ب. الوراثة (Inheritance) وإعادة استخدام الكود:
الوراثة تعني إنشاء كلاس ابن يرث كل خصائص ودوال الكلاس الأب، مع القدرة على إضافة أو تعديل وظائف جديدة.

* **تطبيق من الكود الفعلي:** افتح ملف [GeoFeature.php](file:///c:/namerha_sah/backend/app/Domains/Spatial/Models/GeoFeature.php):
  ```php
  class GeoFeature extends Model
  ```
  الكلاس `Model` الأب يحتوي على آلاف الأسطر البرمجية المسؤولة عن معالجة الاتصال بالـ Database، وعمليات الحذف والحفظ، ومراقبة التغييرات. بفضل الوراثة `extends`، حصل كلاس `GeoFeature` على كل هذه الميزات العملاقة في كلمة واحدة!

---

### ج. التجريد (Abstraction) والفئات المجردة:
التجريد هو إخفاء التفاصيل المعقدة وعرض الوظائف الأساسية فقط. في Laravel، تعتبر الكلاسات الأساسية مثل `Controller` أو `Migration` فئات مجردة لا نقوم بإنشاء كائن منها مباشرة، بل نرث منها فقط لنبني عليها منطقنا الخاص.

---

## 2. فضاءات الأسماء ومحمل الكلاسات التلقائي (Namespaces & Autoloading)
بدلاً من كتابة `require_once` أو `include` لربط الملفات ببعضها في كل صفحة (الطريقة القديمة والمملة لـ PHP)، يستخدم مشروعك نظام تحميل تلقائي ذكي بمساعدة **Composer** ومعيار **PSR-4**.

انظر للملف [SubmissionsReviewController.php](file:///c:/namerha_sah/backend/app/Http/Controllers/Admin/SubmissionsReviewController.php):
```php
namespace App\Http\Controllers\Admin;
```
هذا المسار يطابق مجلدات المشروع الحقيقية على القرص الصلب:
`app -> Http -> Controllers -> Admin`

وعندما نريد استخدام موديل من نطاق آخر، نستورده هكذا:
```php
use App\Domains\Spatial\Models\GeoFeature;
```
يقوم نظام Laravel تلقائياً بالبحث عن الملف المقابل للمسار واستدعائه في أجزاء من الثانية!

---

## 3. خاصية ترويج المعاملات في الباني (Constructor Property Promotion - PHP 8)
هذه إحدى الميزات الفائقة في PHP 8 والتي تتيح لنا تعريف خصائص الكلاس وحقنها تلقائياً داخل دالة البناء (Constructor) في سطر واحد مختصر جداً.

* **تطبيق من الكود الفعلي:** افتح ملف خدمة الأراضي والمناطق [TerritoryService.php](file:///c:/namerha_sah/backend/app/Domains/Spatial/Services/TerritoryService.php) (السطر 14):
  ```php
  public function __construct(
      protected S2GeometryService $s2Service
  ) {}
  ```
  **الشرح:** هذا السطر البسيط يقوم تلقائياً بـ:
  1. تعريف خاصية محمية `$s2Service` من نوع الكلاس `S2GeometryService`.
  2. استقبال الكائن المحقون وتخزينه في هذه الخاصية تلقائياً عند تشغيل الخدمة، مما يمنع الحاجة لكتابة تعريفات ومهام تقليدية مكررة!

---

## 4. الـ Traits (السمات المشتركة) بالتفصيل
الـ Traits هي كتل برمجية تحتوي على دوال جاهزة يمكن لأي كلاس استخدامها (`use`) لتجنب مشكلة عدم دعم PHP للوراثة المتعددة.

* **الـ Traits في مشروعك:**
  افتح نموذج المساهمة [UserAction.php](file:///c:/namerha_sah/backend/app/Domains/Community/Models/UserAction.php):
  ```php
  use HasFactory;
  ```
  * `HasFactory` هو **Trait** يحتوي على دوال التوليد العشوائي للبيانات لاختبار السيرفر (Factories). بمجرد إدراج هذا الـ Trait، أصبح بإمكان المطورين إنشاء آلاف المساهمات المزيفة للاختبار بضغطة زر واحدة!

---

## ✏️ تدريب عملي موسع:
افتح ملف [TerritoryService.php](file:///c:/namerha_sah/backend/app/Domains/Spatial/Services/TerritoryService.php).
1. ما هو فضاء الأسماء (Namespace) الخاص به؟
2. اكتب قائمة بالـ Classes المستوردة في بداية الملف عبر كلمة `use`.
3. ابحث عن دالة البناء `__construct` واشرح الكلاسات المحقونة بداخلها ونوع محددات الوصول الخاصة بها.
