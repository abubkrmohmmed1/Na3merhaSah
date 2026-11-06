# 🛡️ الدرس الثالث (الموسع): التصميم القائم على النطاقات (DDD) وهندسة الخدمات المتقدمة في "نعمّرها صح"

في هذا الدرس الأخير والمتقدم من مسار Laravel، سنكشف أسرار **المعمارية النظيفة (Clean Architecture)** وهندسة الكود الاحترافية المطبقة في مشروعك. سنتعلم بالتفصيل هندسة **Domain-Driven Design (DDD)**، وكيف نستخدم كائنات نقل البيانات (DTOs)، وكيف نعزل منطق العمل المعقد داخل الخدمات (Services)، وكيف نبني واجهات تحكم إدارية تفاعلية ومبهرة بمحرك Blade.

---

## 1. فلسفة معمارية DDD وهيكل مجلد `app/Domains`
في المشاريع البرمجية الضخمة، كتابة الكود بالطريقة التقليدية لـ Laravel تؤدي إلى تضخم المجلدات وضياع الملفات وصعوبة التعديل. 
لذلك، يقسم مشروعك الأكواد إلى **نطاقات عمل مستقلة (Domains)**. كل نطاق يمثل جزءاً كاملاً من النظام يحتوي على نماذجه وعلاقاته وقواعد عمله الخاصة:

* **المجلد الأساسي:** `backend/app/Domains/`

### 💡 النطاقات الأربعة الرئيسية لمشروعك بالتفصيل:
1. **🔒 نطاق المصادقة والمستخدمين (`Auth`):**
   * *الوظيفة:* مصادقة المستخدمين، التحقق من كلمات المرور، وإصدار وتدقيق رموز التوثيق (Tokens).
2. **🌍 نطاق البيانات والخرائط (`Spatial`):**
   * *الوظيفة:* إدارة المعالم الهندسية الجغرافية، حساب المساحات، رسم المضلعات، والتحويل بين نظم الإحداثيات المختلفة.
   * *موديلات هامة:* `GeoFeature` و `MapLayer` و `BoundaryProposal`.
3. **🏡 نطاق العنونة الجغرافية (`Addressing`):**
   * *الوظيفة:* إدارة العناوين السكنية والتجارية، توليد الرموز البريدية ومطابقتها مكانياً بالولايات والمحليات.
   * *موديلات هامة:* `Address` و `AddressSubmission`.
4. **🤝 نطاق المجتمع والمساهمات والتفاعل (`Community`):**
   * *الوظيفة:* إدارة تفاعل المستخدمين والمستكشفين، تتبع تقدم المهام والأهداف اليومية (Quests)، منح أوسمة التميز (Achievements)، ونظام النقاط ولوحة المتصدرين وتتويج الفائزين.
   * *موديلات هامة:* `UserAction` و `Quest` و `Achievement`.

---

## 2. كائنات نقل البيانات (DTOs - Data Transfer Objects)
كائنات الـ DTO هي كلاسات برمجية ننشئها لنقل البيانات بين طبقات التطبيق المختلفة (مثلاً من الـ Request إلى الـ Service) بطريقة آمنة ومحددة الأنواع تماماً لمنع تمرير أي بيانات ملوثة أو غير متوافقة.

### 💡 تطبيق حي ومذهل من مشروعك:
افتح ملف كائن المقترحات [BoundaryProposalData.php](file:///c:/namerha_sah/backend/app/Domains/Spatial/DTOs/BoundaryProposalData.php):
```php
class BoundaryProposalData
{
    public function __construct(
        public readonly string $name,
        public readonly string $proposalType,
        public readonly string $localityId,
        public readonly float $lat,
        public readonly float $lng,
        public readonly ?string $description = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            proposalType: $request->input('proposal_type'),
            localityId: $request->input('locality_id'),
            lat: (float) $request->input('latitude'),
            lng: (float) $request->input('longitude'),
            description: $request->input('description')
        );
    }
}
```
* **ما هي الفوائد العظيمة لهذا الكود؟**
  1. **`readonly`**: حماية خارقة؛ بمجرد إنشاء الكائن وتعبئة البيانات داخله، تصبح البيانات غير قابلة للتعديل أو العبث بها نهائياً!
  2. **`fromRequest`**: دالة مصنعية ذكية تأخذ الطلب الخام من الموبايل وتصفيه وتحوله لكائن DTO محمي وصارم الأنواع ليتم تمريره للخدمات بأمان مطلق.

---

## 3. طبقة الخدمات (Services Layer) وعزل منطق الأعمال الحساس
الخدمات هي المحرك الفعلي للمشروع؛ حيث تحتوي على المنطق الحسابي المعقد للعمليات والتطبيقات.

### 💡 دراسة تفصيلية لخدمة المهام اليومية [QuestService.php](file:///c:/namerha_sah/backend/app/Domains/Community/Services/QuestService.php):
افتح الملف وشاهد كيف يراقب السيرفر نشاط المستخدم ويمنحه المكافآت تلقائياً:
* عندما يقوم مستخدم بإضافة مكان أو رسم حدود، يتم استدعاء الخدمة لمراقبة التقدم:
  ```php
  public function checkProgress(string $userId, string $actionType)
  ```
* تقوم الخدمة تلقائياً بالبحث عن المهام النشطة للمستكشف ومطابقتها:
  ```php
  $userQuest = UserQuest::where('user_id', $userId)
      ->where('status', 'progressing')
      ->first();
  ```
* تزيد الخدمة العداد وتتحقق مما إذا كان المستكشف قد حقق الهدف بالكامل:
  ```php
  if ($userQuest->current_progress >= $quest->goal_amount) {
      $userQuest->update(['status' => 'completed']);
      // منح المستخدم نقاط المكافأة الإضافية فوراً!
      $user->increment('points', $quest->reward_points);
  }
  ```
  هذا البناء الذكي يضمن فصل منطق تتبع التحديات تماماً عن واجهات التطبيق ويجعله يعمل بسلاسة متناهية في الخلفية.

---

## 4. محرك القوالب المتقدم Blade ولوحات التحكم الفاخرة

يتيح لك محرك Blade بناء واجهات مستخدم مذهلة للوحة التحكم مع ميزات التخطيط وإعادة استخدام المكونات:
* **`@extends('layouts.admin')`**: تعني وراثة الهيكل والتصميم الخارجي الموحد للوحة التحكم (البار العلوي والقائمة الجانبية).
* **`@section('content')`**: تحدد الجزء البرمجي المخصص لهذه الصفحة ليتم حقنه داخل التصميم العام.
* **`@foreach` & `@selected`**: دوال تكرار وتحقق ذكية لكتابة واجهات تفاعلية نظيفة للغاية.

### 💡 التطبيق المثير للجدل (تحديث الفعاليات المتنوعة):
تذكر الحقول الذكية التي قمنا بإنشائها لتخصيص التحديات الجغرافية في [form.blade.php](file:///c:/namerha_sah/backend/resources/views/admin/events/form.blade.php):
```blade
<select name="target_layer" class="form-control w-full p-3 border border-gray-300 rounded-lg bg-white">
    <option value="" @selected(old('target_layer', $event->target_layer ?? '') == '')>كل التصنيفات (عام)</option>
    @foreach($mapLayers as $layer)
    <option value="{{ $layer->name }}" @selected(old('target_layer', $event->target_layer ?? '') == $layer->name)>{{ $layer->name }}</option>
    @endforeach
</select>
```
* **آلية العمل:** يقوم الكود بالتكرار التلقائي على كل الطبقات المتاحة في قاعدة البيانات عبر `@foreach`. وبفضل التعليمة الذكية `@selected`، إذا كنا نقوم بتعديل فعالية قائمة، سيقوم النظام تلقائياً بتحديد الطبقة النشطة حالياً وعرضها للمشرف ليوفر عليه وقت الاختيار والبحث!

---

## ✏️ تدريب عملي موسع:
افتح ملف [TerritoryService.php](file:///c:/namerha_sah/backend/app/Domains/Spatial/Services/TerritoryService.php).
اكتب شرحاً تفصيلياً يوضح:
1. ما هو الهدف الحسابي من هذه الخدمة.
2. اشرح دالة `calculateLeaderboard` وما هي العمليات والترتيبات الحاصلة داخلها لجلب المتصدرين على مستوى الولاية أو المحلية.
