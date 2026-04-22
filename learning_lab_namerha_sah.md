# 🔬 المختبر التعليمي المتقدم: مشروع "نعمرها صح"

هذا المستند يُعد السجل الهندسي والمختبر التعليمي التفصيلي لكل سبرينت. يتم هنا توثيق **كل خطوة فعلية** تم تنفيذها، الأخطاء التي واجهتنا، كيف قمنا بحلها، والقرارات المعمارية التي اتخذناها ليكون مرجعاً حياً لفريق العمل.

---

## 🏃‍♂️ السبرينت 1: البنية التحتية والبيانات المكانية (تم إنجازه)

### 1. الهدف المعماري للسبرينت
كان الهدف هو الخروج من عباءة المشاريع التقليدية وتأسيس بيئة عمل متقدمة تتحمل المقياس الكبير (Scalable). تمحور العمل حول هندسة المجلدات بطريقة نظيفة (Clean Architecture) وتجهيز قاعدة بيانات قادرة على معالجة الخرائط والمواقع الجغرافية بدقة.

### 2. تفاصيل ما قمنا بإنجازه (بالخطوات)

#### المهمة 1.1: تهيئة واجهة المواطن (Frontend - Flutter)
* **ماذا فعلنا:** بدلاً من وضع كل أكواد التطبيق في مجلد واحد، قمنا بهيكلة التطبيق بنمط **Feature-First**.
* **التنفيذ الفعلي:** تم تقسيم مجلد `lib` الداخلي لتطبيق Flutter إلى:
  - `core`: يحتوي على (`network`, `theme`, `utils`) لإدارة حالة التطبيق، الألوان، والثوابت.
  - `features`: مجلد لكل ميزة مستقلة، حيث أنشأنا `address_picker` (لالتقاط العنوان) و `report_issue` (لرفع البلاغ). كل ميزة ستُقسم لاحقاً إلى (presentation, domain, data).
* **الفائدة التعليمية:** هذا يضمن أنه عندما يكبر التطبيق ويعمل عليه أكثر من مطور، لن يحدث تداخل في الأكواد.

#### المهمة 1.2 و 1.3: تهيئة الخوادم الخلفية وهيكل DDD (Backend - Laravel)
* **ماذا فعلنا:** تطبيقات Laravel الافتراضية تضع كل الـ Controllers والـ Models في مجلدات عامة. نحن قمنا بتغيير هذا لنمط **Domain-Driven Design (DDD)**.
* **التنفيذ الفعلي:** 
  - داخل مجلد `app`، قمنا بإنشاء مجلد `Domains`.
  - قسمنا المشروع إلى ثلاث نطاقات أساسية: `Addressing`، `Reporting`، و `Spatial`.
  - داخل كل نطاق أنشأنا مجلدات تفصيلية: `Actions` للمنطق، `Models` للبيانات، و `DataTransferObjects`.
* **الفائدة التعليمية:** تقسيم المنطق إلى نطاقات يسهل مستقبلاً تحويل المشروع إلى Microservices إذا احتجنا لذلك، ويفصل تعقيد الخرائط (`Spatial`) عن منطق إدارة البلاغات البسيط (`Reporting`).

#### المهمة 1.4 و 1.5: تجهيز PostGIS والهجرة (Database Migrations)
* **التحدي الأول (تثبيت وربط قاعدة البيانات):**
  - **السيناريو:** حاولنا استخدام أداة سطر الأوامر `psql` عبر الـ PowerShell في نظام Windows لإنشاء قاعدة `namerha_sah_db`.
  - **الخطأ الذي ظهر:** `The term 'psql' is not recognized`.
  - **السبب والحل:** اتضح أن PostgreSQL متبث (الإصدار 18) لكن مساره `bin` غير مضاف لمتغيرات بيئة نظام الويندوز (PATH). تجاوزنا ذلك بتشغيل الأوامر مستخدمين المسار الكامل التنفيذي:
    `& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -c "CREATE DATABASE namerha_sah_db;"`
* **التحدي الثاني (تعديل `.env`):**
  - تم تغيير محرك الاتصال في لارافيل من `sqlite` إلى `pgsql` وتحديد اسم قاعدة البيانات وحساب الـ `postgres`. وكان الدرس الأهم هنا هو تذكر إضافة كلمة المرور الفعلية (التي استخدمت في الـ prompt) لحقل `DB_PASSWORD`.
  - **الكود المستعمل (الإعدادات الجديدة في `.env`):**
    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=namerha_sah_db
    DB_USERNAME=postgres
    DB_PASSWORD=YourPasswordHere
    ```
* **التحدي الثالث (تكوين قواعد البيانات المكانية وتعارض الـ Migrations):**
  - **السيناريو المعماري للأكواد:** قمنا بكتابة ملفي `Migration` متقدمين (000001 و 000002) يدعمان تشفير الحقول الجغرافية `Geometry(Point, 4326)` التي تعتمد على PostGIS وتدعم الفهرس المكاني (Spatial Indexing).
  - **كود تهجير جدول البلاغات (`reports`):**
    ```php
    Schema::create('reports', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('user_id')->nullable();
        // الحقل الأهم: تعريف النقطة الجغرافية بمعيار 4326 مع PostGIS
        $table->geometry('location', 'point', 4326); 
        $table->string('s2_cell_id')->index(); // فهرس للبحث النصي السريع لمعيار S2
        $table->integer('category_id')->nullable();
        $table->enum('status', ['new', 'in-progress', 'resolved'])->default('new');
        $table->timestamps();

        // إضافة Spatial Index لضمان سرعة البحث في الخريطة الحرارية لاحقاً
        $table->spatialIndex('location');
    });
    ```
  - **كود تهجير جدول العناوين (`addresses`):**
    ```php
    Schema::create('addresses', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('s2_cell_id')->unique(); // حقل فريد لربط الخلية بالعنوان
        $table->string('address_str');
        $table->string('neighborhood')->nullable();
        $table->geometry('location', 'point', 4326)->nullable();
        $table->timestamps();
        
        $table->spatialIndex('location');
    });
    ```
  - **ظهور خطأ التعارض:** `SQLSTATE[42P07]: Duplicate table: 7 ERROR: relation reports already exists`.
  - **التحليل والحل:** بعد التفتيش في مجلد `migrations` تبين وجود ملفات سابقة فارغة تحمل نفس أسماء الجداول. نجح الـ Migration الخاص بنا بامتياز وصنع الجداول، ولكن اصطدم بالملفات القديمة المكررة. تم الحل بحذف الملفات القديمة الزائدة وتنظيف مسار العمل.
* **الفائدة التعليمية للفريق:** يجب مراجعة أي ملفات هجرة قديمة قبل تنفيذ `php artisan migrate`. ورسائل الخطأ في قواعد البيانات ليست دائماً "خراب"، بل هنا كانت دليلاً على أن كودنا نجح وأن القديم يعيق الطريق.

#### المهمة 1.6: تصميم خدمات الهندسة المكانية (S2 Geometry & Models)
* **ماذا فعلنا:** لكي يتمكن الـ Backend من تحويل موقع أي بلاغ (Lat/Lng) إلى "رمز خلية" (Token)، قمنا ببناء الخدمة المركزية `S2GeometryService` وتفعيل ملفات الـ Models التابعة للبنية الجديدة `DDD`.
* **التنفيذ الفعلي:**
  - تمت إضافة مسار `app/Domains/Spatial/Services/S2GeometryService.php` لمعالجة كافة الخوارزميات، لنجعل النظام قابلاً للبحث السريع استناداً للخلايا (s2_cell_id) عوضاً عن الاستعلامات الدائرية البطيئة.
  - تم تجهيز الـ Models الأساسية (`Address` و `Report`) داخل نطاقها باستخدام خاصية الـ `HasUuids`.
* **الكود المصدري للـ S2 Service:**
  ```php
  class S2GeometryService
  {
      // دالة لتحويل الموقع إلى S2 Token بدقة تعادل 3متر (المستوى 18)
      public function latLngToToken(float $lat, float $lng, int $level = 18): string
      {
          // سيتصل هذا الكود بمكتبة خارجية أو Microservice فعلية (Go/C++)
          $token = 's2_' . md5($lat . $lng . $level); 
          return substr($token, 0, 10); 
      }
  }
  ```
* **نماذج البيانات (Models) لـ DDD:**
  تطبيق بنية DDD بإنشاء Models متقدمة تستخدم ميزة الجيل الحديث لـ `HasUuids` لتوليد المعرفات الفريدة للموارد.
  - **كود `Report.php` (نموذج البلاغ):**
    ```php
    namespace App\Domains\Reporting\Models;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Concerns\HasUuids;

    class Report extends Model
    {
        use HasUuids; // استخدام UUID بدلاً من الأرقام التسلسلية لحماية النظام

        protected $fillable = [
            'id', 'user_id', 'location', 's2_cell_id', 'category_id', 'status'
        ];
    }
    ```
  - **كود `Address.php` (نموذج العنونة الجغرافية):**
    ```php
    namespace App\Domains\Addressing\Models;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Concerns\HasUuids;

    class Address extends Model
    {
        use HasUuids;

        protected $fillable = [
            'id', 's2_cell_id', 'address_str', 'neighborhood', 'location'
        ];
    }
    ```
* **الدرس المعماري والنهائي للسبرينت 1:** 
الآن يمتلك المشروع أساسات متينة جداً (Database + Spatial + Clean Folders + Architecture). أصبحنا قادرين على تخزين النقاط وبدء العمل على منطق إكمال العنواين وبناء الـ APIs وتطبيقات الجوال على أساس نظيف تماماً!

#### المهمة 1.7: مواءمة قاعدة البيانات ونماذج DDD مع الـ Workflow والتصميم (UI/UX)
* **ماذا فعلنا:** بعد قراءة تدفق العمل (Workflow) المكون من 8 مراحل، وتصاميم واجهة الموبايل، اكتشفنا حاجة لتعديل صلب قاعدة البيانات والنماذج لاستيعابها:
  - **الـ State Machine:** استبدلنا حالات البلاغ البسيطة بخطوات الـ Workflow الفعلية (`started`, `govt_review`, `surveyor_assigned`... الخ). كما أضفنا `SoftDeletes` لأن البلاغات لا تُحذف نهائياً بل تتغير حالتها.
  - **الربط المكاني الصحيح (Spatial Join Linking):** بدلاً من حفظ "العنوان المعرّب" كنص مكرر، أضفنا مفتاحاً أجنبياً `address_id` في جدول `reports` يرتبط بجدول `addresses`. هذا تطبيق نظيف للـ DDD بحيث يتم الربط لحظياً عند استلام إحداثيات المواطن.
  - **واجهات الـ API المختزلة:** تم بناء `ReportResource` خفيف ليرجع (صورة مصغرة، حالة البلاغ بلون جاهز للواجهة، والعنوان) استجابةً لمحدودية شاشات النطاق المفتوح في الموبايل.
* **الفائدة التعليمية:** تصميم قاعدة البيانات يجب ألا يُبنى في فراغ، بل يجب أن يخضع دائماً لقسوة متطلبات الـ UI ورسمة سير العمل (Workflow) منذ السبرينت الأول لتقليل عمليات إعادة البناء (Refactoring) المكلفة.

---

## 🚀 السبرينت 2: برمجة خدمات العنونة والـ APIs (Addressing Logic & Reporting)
**الحالة العامة:** (تم إنجازه ✅)

### 1. الهدف المعماري للسبرينت
تحويل العنونة من مجرد "نص" إلى "كيان مكاني" ذكي مرتبط آلياً بالبلاغات. تم تطبيق منطق الـ **Spatial Join** الفوري لضمان دقة البيانات وتسهيل عمل الجهات الحكومية لاحقاً.

### 2. تفاصيل الأكواد والمنطق البرمجي

#### أ) محرك العنونة العكسية (ReverseGeocodeAction)
الهدف هو تحويل إحداثيات GPS إلى عنوان "نعمرها صح" فريد باستخدام S2 Geometry مع توفير نظام "بحث عن الأقرب" كخيار احتياطي.

*   **التحديث الأخير (استخدام مكتبة S2 فعلية):**
تم استبدال الـ S2 Token المحاكاة بمكتبة `nicklasw/s2-geometry-library-php` لضمان دقة مكانية عالمية حقيقية.

*   **الكود البرمجي المحدث:**
```php
public function latLngToToken(float $latitude, float $longitude, int $level = 21): string
{
    $latLng = S2LatLng::fromDegrees($latitude, $longitude);
    $cellId = S2CellId::fromLatLng($latLng);
    return $cellId->parent($level)->toToken();
}
```

*   **الكود البرمجي (ReverseGeocodeAction):**
```php
public function execute(float $lat, float $lng): ?Address {
    // 1. تحويل الموقع لرمز S2 بمستوى دقة 21 (~3 متر) للبحث السريع
    $token = $this->s2Service->latLngToToken($lat, $lng, 21);

    // 2. محاولة جلب المطابقة الدقيقة من قاعدة البيانات
    $address = Address::where('s2_cell_id', $token)->first();

    // 3. خيار احتياطي (Fallback): استخدام معامل المسافة <-> في PostGIS لجلب الأقرب
    if (!$address) {
        $address = Address::orderByRaw("location <-> 'SRID=4326;POINT($lng $lat)'::geometry")->first();
    }
    return $address;
}
```
*   **الشرح التقني:** نستخدم ترتيب الأولوية (S2 Token أولاً) لأنه أسرع بكثير (نصوص)، وإذا فشل نلجأ إلى العمليات الرياضية المكانية في PostgreSQL لضمان عدم خروج المواطن بدون عنوان.

#### ب) نظام البلاغات والربط المكاني (ReportIssueAction)
ضمان أن كل بلاغ يتم رفعه يُربط آلياً بـ `address_id` بناءً على موقعه الجغرافي.

*   **الكود البرمجي:**
```php
public function execute(array $data, array $imageFiles): Report {
    // 1. تنفيذ الـ Spatial Join لجلب معرف المسكن/العنوان
    $address = $this->reverseGeocodeAction->execute($data['lat'], $data['lng']);

    // 2. حفظ البلاغ بالارتباط المكاني الجديد
    return Report::create([
        'description' => $data['description'],
        'location'    => "SRID=4326;POINT({$data['lng']} {$data['lat']})",
        'address_id'  => $address?->id, // هنا يكمن سر الربط (Spatial Join)
        'images'      => $imagePaths,
        'status'      => 'started',
    ]);
}
```
*   **الشرح التقني:** قمنا بحقن `ReverseGeocodeAction` داخل ميكانيكية رفع البلاغ لترجمة الإحداثيات المبهمة إلى سجل عنوان ثابت. هذا يسهل على الجهات الحكومية فلترة البلاغات حسب الأحياء بدقة 100%.

#### ج) تحسين استهلاك البيانات (ReportResource)
توفير استجابة API "ذكية" وخفيفة تناسب تطبيق الموبايل وسرعات الإنترنت المتغيرة.

*   **الكود البرمجي:**
```php
public function toArray(Request $request): array {
    return [
        'id'              => $this->id,
        'digital_address' => $this->address ? $this->address->address_str : 'جاري التحديد...',
        'thumbnail'       => is_array($this->images) ? ($this->images[0] ?? null) : null,
        'status_color'    => match($this->status) {
             'resolved' => 'green',
             'started', 'govt_review' => 'red',
             default => 'orange',
        },
    ];
}
```
*   **الشرح التقني:** بدلاً من إرسال مصفوفة صور ثقيلة، نرسل `thumbnail` فقط للقوائم، ونرسل لون الحالة (`status_color`) مباشرة من الباكيند ليتم عرضه في الموبايل فوراً دون منطق معقد إضافي في الـ Flutter.


---

## 📱 السبرينت 3: تطبيق المواطن والخرائط (Flutter App & Offline Support)
**الحالة العامة:** (تم إنجازه ✅)

### 1. الهدف المعماري للسبرينت
كان الهدف هو بناء واجهة مواطن (Frontend) لا تكتفي فقط بجمال التصميم (والذي استمديناه من تصميمات سماح)، بل تكون **مرنة تقنياً** للعمل في ظروف السودان (انقطاع إنترنت، هواتف بمواصفات مختلفة). ركزنا على دمج الخرائط مع "العنونة الرقمية" وتأسيس نظام "العمل المنفصل" (Offline First).

### 2. تفاصيل الإنجازات التقنية

#### أ) فلسفة التصميم (Samah's UI Implementation)
*   **ماذا فعلنا:** قمنا بتحويل تصميم Canva إلى نظام ألوان (Theme) وخطوط موحد في Flutter.
*   **التكوين التقني:**
    - **AppColors:** تعريف لوحات الألوان (الذهبي/الأصفر للبلاغات، الرمادي الغامق للـ Headers).
    - **Cairo Font:** دمج خط "Cairo" من Google Fonts لدعم اللغة العربية بشكل احترافي.
    - **Iconsax:** استخدام مكتبة أيقونات عصرية تتطابق مع روح التصميم.

#### ب) واجهة "التقاط العنوان الذكي" (Interactive Address Picker)
هذا هو القلب النابض للتطبيق، حيث يربط المواطن بمحرك العنونة في الباكيند.
*   **كيف يعمل:**
    1.  تظهر خريطة (Google Maps) في وضع "التصفح".
    2.  توجد "دبوس" (Pin) ثابت في مركز الشاشة.
    3.  عند تحريك الخريطة (`onCameraIdle`) يتم إرسال الإحداثيات فوراً إلى API العنونة العكسية الذي بنيناه في السبرينت 2.
    4.  يتم تحديث نص العنوان الرقمي (S2 Address) في بطاقة عائمة أسفل الخريطة لحظياً.
*   **الفائدة التعليمية:** الربط اللحظي بين حركة الخريطة وAPI العنونة يعطي انطباعاً بأن النظام "حي" ويقلل من أخطاء إدخال العناوين من قبل المستخدم.

#### ج) استراتيجية العمل المنفصل (Offline-First Strategy)
في ظل تذبذب الإنترنت، لا يمكننا الاعتماد على السيرفر طوال الوقت. قمنا بتطبيق الحل التالي:
*   **قاعدة البيانات المحلية (Hive):** اخترنا Hive لسرعتها الفائقة في تخزين البيانات كـ (Pairs / Maps) دون الحاجة لإعدادات معقدة.
*   **المنطق البرمجي (Sync Logic):**
    - عند ضغط زر "إرسال البلاغ"، يتأكد التطبيق من حالة الإنترنت عبر `connectivity_plus`.
    - **إذا كان "أوفلاين":** يتم حفظ البلاغ محلياً في صندوق (Box) يُدعى `pending_reports` وإظهار تنبيه للمستخدم.
    - **إذا عاد الإنترنت:** يظهر شريط (Sync Banner) في الشاشة الرئيسية يطلب مزامنة البلاغات المعلقة بضغطة زر واحدة.
*   **الكود البرمجي (BLoC Sync Event):**
    ```dart
    on<SyncOfflineReports>((event, emit) async {
       final pending = _local.getPendingReports();
       for (var report in pending) {
         await _api.submitReport(
           lat: report['lat'], lng: report['lng'],
           description: report['description'], categoryId: report['category_id']
         );
       }
       await _local.clearPendingReports(); // مسح البيانات بعد النجاح
    });
    ```

#### هـ) الانتقال للنظام الحقيقي (Real-World Integration)
بعد مرحلة النمذجة (Mockups)، قمنا بتنفيذ التغييرات التالية لنقل التطبيق لمستوى الإنتاج:
*   **التوثيق المتين (Laravel Sanctum):**
    - استبدلنا التوكنات الوهمية بنظام **Sanctum** لإصدار توكنات فريدة مرتبطة بجلسة المستخدم في قاعدة البيانات.
    - **التسجيل برقم الهاتف:** قمنا بتعديل Migration المستخدمين والموديل لدعم الحقل الفريد `phone` وجعل البريد الإلكتروني اختيارياً، ليتناسب مع الطبيعة الاستخدامية في السودان.
*   **هيكلية الـ API النهائية:** 
    - تم توحيد الـ Endpoints تحت بادئة `/v1/` لضمان قابلية التوسع مستقبلاً وإصدار نسخ جديدة من الـ API دون كسر النسخ القديمة.

#### و) استراتيجية العنونة والطبقات الجغرافية (Spatial Strategy)
بما أن المشروع يبدأ بقاعدة بيانات عناوين فارغة، اعتمدنا استراتيجية "النمو العضوي":
*   **بناء الطبقات (Spatial Layering):**
    - قمنا بتحديث جدول `addresses` ليدعم حقل كـ `metadata` من نوع JSON، مما يسمح بحفظ بيانات تقنية تختلف من طبقة لأخرى (مثلاً: رقم عداد مياه، أو جهد محول كهرباء).
    - إضافة حقل `type` لتمييز السكن عن المعالم (Landmarks) وعن نقاط الشبكات الخدمية (Utility Nodes).
*   **الأدوار والتحقق (Roles & Verification):**
    - تم إدخال نظام الأدوار: المواطن (`citizen`) يبني قاعدة البيانات عبر تسجيل منزله وبلاغاته، بينما يقوم المساح (`surveyor`) والمهندس (`engineer`) بالتحقق من صحة هذه العناوين.
*   **تجربة المستخدم المكانية (Spatial UX):**
    - تم استخدام **وضع الخريطة المصغرة (Lite Mode)** في فلاتر لعرض مواقع البلاغات في القائمة بكفاءة عالية، مما يقلل من استهلاك الذاكرة والمعالج.
    - **استخراج الإحداثيات (Spatial Fetching):** نظراً لتخزين البيانات بصيغة `GEOMETRY` في Postgres، استخدمنا `selectRaw` مع دوال `ST_X` و `ST_Y` في Laravel لاسترجاع خطوط الطول والعرض كأرقام عشرية جاهزة للاستخدام في التطبيق مباشرة دون معالجة إضافية.
*   **التوثيق بالرقم الوطني:**
    - ربط الحساب بالرقم الوطني وإحداثيات السكن يضمن أن "نعمرها صح" هو سجل جغرافي موحد يمنع التكرار ويدعم التخطيط العمراني المستقبلي.

---

## 🧭 السبرينت 4: لوحة التحكم الإدارية (Planned)
*(سيتم توثيق مهام الـ Dashboard والـ Heatmaps في الخطوة القادمة).*

---

## Sprint 5: Workflow Implementation & Step Validation (تم إنجازه)

### 1. تصحيح تناسق قاعدة البيانات (Database Consistency Correction)
* **المشكلة:** تم ملاحظة أن حقلي `workflow_step` و `workflow_metadata` مستخدمان في النماذج (Models) والمنطق البرمجي (Actions) ولكن لم يتم إضافتهما إلى جدول `reports` في قاعدة البيانات عبر أي ملف ترحيل (Migration).
* **الحل:** تم إنشاء ملف ترحيل جديد يدويًا باستخدام `php artisan make:migration add_workflow_fields_to_reports_table --table=reports`.
* **التنفيذ:** تم تعديل ملف الترحيل الجديد لإضافة:
  - `workflow_step` كحقل `enum` بأنواع حالات سير العمل (`location_selection`, `category_selection`, `description_input`, `image_upload`, `review_submit`) وبقيمة افتراضية `location_selection`.
  - `workflow_metadata` كحقل `json` يسمح بتخزين بيانات إضافية حول سير العمل.
* **الكود المطبق:**
  ```php
    Schema::table('reports', function (Blueprint $table) {
        $table->enum('workflow_step', ['location_selection', 'category_selection', 'description_input', 'image_upload', 'review_submit'])->default('location_selection')->after('status');
        $table->json('workflow_metadata')->nullable()->after('workflow_step');
    });
  ```
* **التحقق:** تم تشغيل `php artisan migrate` بنجاح لتطبيق التغييرات على قاعدة البيانات.
* **الفائدة التعليمية:** التأكد من تطابق هيكل قاعدة البيانات مع الكود البرمجي أمر حيوي لمنع الأخطاء وضمان سلامة البيانات.
