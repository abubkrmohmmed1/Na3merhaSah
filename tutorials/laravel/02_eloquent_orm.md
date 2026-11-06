# 🛡️ الدرس الثاني (الموسع): قواعد البيانات المتقدمة ونظام Eloquent ORM وحياة النماذج في Laravel

في هذا الدرس سنتعمق في محرك قواعد البيانات الخارق لـ Laravel: **Eloquent ORM**. سنتعلم بالتفصيل كيف ننشئ الجداول، وكيف نربط بينها بعلاقات برمجية متقدمة، وكيف نتحكم في البيانات أثناء دورة حياتها ونحمي قاعدة البيانات من الأخطاء والانهيارات البرمجية.

---

## 1. الهجرات المتقدمة والتحكم بالهيكل (Advanced Migrations)
الهجرات في Laravel تتيح لنا بناء وتعديل الجداول باستخدام كود PHP نظيف وسهل القراءة.

### 💡 تطبيق عملي موسع من مشروعك:
افتح ملف هجرة جدول مساهمات المستخدمين [2026_05_14_063311_create_user_actions_table.php](file:///c:/namerha_sah/backend/database/migrations/2026_05_14_063311_create_user_actions_table.php):
```php
Schema::create('user_actions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->uuid('geo_feature_id')->nullable();
    $table->string('action_type', 50);
    $table->integer('points')->default(0);
    $table->string('status', 20)->default('pending');
    $table->timestamps();
});
```
**تحليل قواعد قاعدة البيانات البرمجية المطبقة هنا:**
* **`foreignId('user_id')->constrained()->cascadeOnDelete()`**:
  هذا السطر البسيط يقوم بأعمال جبارة:
  1. ينشئ عمود مفتاح خارجي (Foreign Key) يربط جدول المساهمات بجدول المستخدمين `users`.
  2. يقوم بعمل فحص تلقائي لضمان وجود المستخدم الفعلي.
  3. `cascadeOnDelete()`: تعني أنه في حال حذف حساب مستخدم، سيقوم النظام تلقائياً وبأمان بحذف جميع مساهماته ونقاطه المرتبطة لمنع وجود بيانات يتيمة ومعلقة تسبب بطء قاعدة البيانات!
* **`uuid('geo_feature_id')->nullable()`**:
  لأن المعالم الجغرافية تستخدم معرّفات فريدة (UUID) بدلاً من الأرقام، قمنا بتعريف حقل المفتاح الخارجي كـ `uuid` ليتطابق مع نوع الحقل الأصلي تماماً، وجعلناه `nullable` لأن هناك عمليات (مثل تسجيل الدخول أو المهام العامة) قد لا ترتبط بمعلم جغرافي محدد.

---

## 2. العلاقات المعقدة بين النماذج (Eloquent Relationships)
يوفر Eloquent طرقاً برمجية غاية في الجمال والذكاء لربط الجداول ببعضها البعض.

### 💡 علاقة التحديات والفعاليات في مشروعك:
افتح نموذج المساهمات [UserAction.php](file:///c:/namerha_sah/backend/app/Domains/Community/Models/UserAction.php) (السطر 20 تقريباً):

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function geoFeature(): BelongsTo
{
    return $this->belongsTo(GeoFeature.class, 'geo_feature_id');
}
```

* **كيف نستخدم هذه العلاقات في الكود؟**
  عندما نقوم بجلب المساهمة من قاعدة البيانات، نستطيع جلب اسم المستخدم واسم القرية أو المعلم الجغرافي المرتبط بـ **سطر واحد فقط** ودون كتابة جمل `JOIN` معقدة:
  ```php
  $action = UserAction::find(1);
  $userName = $action->user->name; // جلب اسم المستخدم المرتبط تلقائياً!
  $featureName = $action->geoFeature?->properties['name']; // جلب اسم المعلم بأمان!
  ```

---

## 3. دورة حياة النموذج وأحداث الحفظ (Model Events & Lifecycle Hooks)
عند التعامل مع الكائنات في Eloquent، تمر عملية الحفظ بسلسلة من الأحداث التلقائية:
`retrieved` -> `creating` -> `created` -> `updating` -> `updated` -> `saving` -> `saved`

### 🔍 تحليل حل المشكلة البرمجية الحساسة لـ `GeoFeature`:
تذكر الخطأ الكارثي الذي كان ينهار بسببه النظام عندما حاول المستخدم إضافة مضلع أو نقطة:
`column "latitude" of relation "geo_features" does not exist`

**لماذا حدث هذا الخطأ؟**
لأننا قمنا بتمرير الإحداثيات (`latitude` و `longitude`) داخل مصفوفة إنشاء الكائن `GeoFeature::create([...])`. وبشكل افتراضي، يحاول Eloquent كتابة جميع الحقول الممررة داخل جملة استعلام الـ SQL `INSERT INTO`. وحيث أن جدول `geo_features` في قاعدة البيانات الحقيقية **لا يحتوي** على أعمدة باسم `latitude` أو `longitude` (لأنها مخزنة داخل الحقل الجغرافي المعقد `geom` فقط)، تنهار قاعدة البيانات PostgreSQL وترفض الاستعلام!

**كيف قمنا بحل هذه الكارثة برمجياً؟**
افتح نموذج [GeoFeature.php](file:///c:/namerha_sah/backend/app/Domains/Spatial/Models/GeoFeature.php) وانظر لدالة `booted` (السطر 34 تقريباً):
```php
protected static function booted()
{
    static::saving(function ($feature) {
        // 1. نقرأ الإحداثيات الوهمية المساعدة
        $lat = $feature->getAttribute('latitude');
        $lng = $feature->getAttribute('longitude');
        
        // 2. نقوم ببناء الحقل الجغرافي الحقيقي
        if ($lat && $lng) {
            $feature->geom = DB::raw("ST_GeomFromText('POINT($lng $lat)', 4326)");
        }

        // 3. الحل العبقري: نقوم بحذف الحقول الوهمية من مصفوفة السمات تماماً!
        unset($feature->attributes['latitude']);
        unset($feature->attributes['longitude']);
        unset($feature->attributes['geometry_wkt']);
    });
}
```
* **آلية الحل:**
  بمجرد استدعاء دالة الحفظ، وتحديداً في حدث `saving` الذي يسبق كتابة الاستعلام، نقوم ببناء حقل `geom` الجغرافي المخصص لـ PostGIS، ثم نقوم بعمل `unset` لحذف حقول `latitude` و `longitude` من مصفوفة سمات الكود تماماً.
  بهذه الطريقة، عندما يرسل Laravel استعلام الـ SQL لقاعدة البيانات، يرسله نظيفاً وخالياً تماماً من الأعمدة الوهمية، مما يمنع الانهيار نهائياً ويحمي النظام بنسبة 100%!

---

## ✏️ تدريب عملي موسع:
افتح نموذج [GeoFeature.php](file:///c:/namerha_sah/backend/app/Domains/Spatial/Models/GeoFeature.php).
اكتب تقريراً برمجياً مفصلاً يشرح:
1. العلاقات الثلاث المحددة داخل الكود ونوع كل منها والجدول المرتبط بها.
2. وضح وظيفة الدالة المساعدة `getLatitudeAttribute` و `getLongitudeAttribute` (التي توفر خاصية القراءة السهلة كـ Virtual Attributes).
3. اشرح سيناريو تفصيلياً لكيفية تخزين مضلع (Polygon) بدلاً من نقطة (Point) في حدث الـ `saving`.
