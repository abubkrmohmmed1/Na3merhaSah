# 💙 الدرس الثاني (الموسع): إدارة الحالة بنمط BLoC وهندسة حقن الاعتماديات في Flutter

في هذا الدرس سنتعلم المفاهيم الهندسية الأكثر رقياً وقوة في بيئة عمل Flutter: **إدارة الحالة (State Management)** باستخدام نمط **BLoC (Business Logic Component)** وكيف نستخدم كاشف الخدمات **GetIt** للتحكم في حقن الاعتماديات (Dependency Injection) وتسهيل صيانة واختبار التطبيق.

---

## 1. لماذا نحتاج لإدارة الحالة وفصل كود المنطق عن الواجهات؟
عندما نكتب كود الموبايل، من السهل جداً ارتكاب خطأ كتابة دوال الاتصال بالسيرفر وجلب البيانات داخل ملف الشاشة نفسه. 

**لماذا يعتبر هذا التصميم البرمجي سيئاً؟**
1. **صعوبة الاختبار (Un-testable):** لا يمكننا اختبار صحة الاتصال بالشبكة ومعالجة الأخطاء دون تشغيل شاشة الهاتف يدوياً في كل مرة.
2. **صعوبة التعديل:** إذا قررنا تغيير الرابط أو طريقة جلب البيانات، سنضطر لتعديل عشرات الشاشات يدوياً.
3. **بطء الأداء:** وضع المنطق المعقد داخل دالة الـ `build` يجبر الهاتف على إعادة رسم الواجهات مرات غير ضرورية مما يسبب بطء التطبيق وتشنجه (Lag).

**الحل العبقري:** فصل منطق العمل تماماً عن الواجهات؛ الشاشة تهتم فقط بالرسم والعرض (UI Layer)، والـ BLoC يهتم بطلب البيانات ومعالجتها (Logic Layer).

---

## 2. بنية نمط BLoC بالتفصيل (الأحداث، الحالات، والمترجم)

يعمل نمط BLoC مثل دائرة كهربائية مغلقة ومحمية:

```mermaid
graph LR
    UI[الواجهة - UI Screen] -- يرسل حدث Event --> BLoC[المترجم - BLoC]
    BLoC -- يتصل بالشبكة --> API[الـ API والسيرفر]
    API -- يرجع البيانات --> BLoC
    BLoC -- يطلق حالة State جديدة --> UI
```

### أ. الأحداث (Events):
هي الأفعال والمدخلات التي يرسلها المستخدم من الشاشة للـ Bloc.
* **مثال من مشروعك:**
  ```dart
  class SubmitPlaceEvent extends PlaceEvent {
    final PlaceInputData data;
    const SubmitPlaceEvent(this.data);
  }
  ```

### ب. الحالات (States):
هي الأشكال والألوان والبيانات المتغيرة التي يجب على الواجهة عرضها للمستخدم استجابةً للأحداث.
* **مثال من مشروعك:**
  ```dart
  abstract class PlaceState {}
  class PlaceInitialState extends PlaceState {}
  class PlaceLoadingState extends PlaceState {} // تعرض دائرة التحميل ⏳
  class PlaceSuccessState extends PlaceState {   // تعرض نجاح العملية وتمرر البيانات ✅
    final String message;
    PlaceSuccessState(this.message);
  }
  class PlaceErrorState extends PlaceState {     // تعرض رسالة الخطأ للمستخدم ⚠️
    final String error;
    PlaceErrorState(this.error);
  }
  ```

### ج. الـ Bloc (العقل المدبر):
هو الكلاس الذي يجمع الأحداث ويحولها لحالات.
* **الكود الفعلي لـ Bloc البلاغات والمساهمات في مشروعك:**
  ```dart
  class PlaceBloc extends Bloc<PlaceEvent, PlaceState> {
    final ApiClient api;
    
    PlaceBloc(this.api) : super(PlaceInitialState()) {
      on<SubmitPlaceEvent>((event, emit) async {
        emit(PlaceLoadingState()); // أطلق حالة التحميل فوراً!
        try {
          // الاتصال بالـ API وسحب البيانات
          await api.post('/mobile/places', data: event.data.toJson());
          emit(PlaceSuccessState('تمت الإضافة بنجاح!')); // أطلق حالة النجاح!
        } catch (e) {
          emit(PlaceErrorState('فشل الاتصال بالخادم')); // أطلق حالة الخطأ!
        }
      });
    }
  }
  ```

---

## 3. التعامل مع الـ Bloc في الواجهات (BlocBuilder & BlocListener)

يوفر Flutter ويدجت ذكية للتصنت على الـ Bloc وتحديث الشاشات تلقائياً:

1. **`BlocBuilder`:** يُستخدم لإعادة رسم واجهة محددة عندما تتغير الحالة (مثل عرض زر "حفظ" أو دائرة تحميل).
   ```dart
   BlocBuilder<PlaceBloc, PlaceState>(
     builder: (context, state) {
       if (state is PlaceLoadingState) {
         return CircularProgressIndicator(); // عرض مؤشر تحميل
       }
       return ElevatedButton(onPressed: _submit, child: Text('حفظ'));
     }
   );
   ```
2. **`BlocListener`:** يُستخدم للقيام بعمليات تحدث لمرة واحدة ولا تحتاج لإعادة رسم الشاشة (مثل الانتقال لشاشة النجاح، أو إظهار نافذة منبثقة تنبيهية `SnackBar`).
   ```dart
   BlocListener<PlaceBloc, PlaceState>(
     listener: (context, state) {
       if (state is PlaceSuccessState) {
         Navigator.pushReplacementNamed(context, '/success'); // انتقال للشاشة التالية!
       }
       if (state is PlaceErrorState) {
         ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(state.error)));
       }
     }
   );
   ```

---

## 4. حقن الاعتماديات بنظام GetIt ودوره في جودة المشروع
عندما يحتاج الـ `PlaceBloc` لكلاس الاتصال بالشبكة `ApiClient` للعمل، لا نقوم بإنشاء كائن `ApiClient` داخله يدوياً. هذا يسمى **الارتباط الوثيق (Tight Coupling)** وهو سيئ جداً. 

بدلاً من ذلك، نستخدم **GetIt** لحقن الاعتماديات تلقائياً:
* **التسجيل المركزي (في ملف تهيئة التطبيق):**
  ```dart
  final sl = GetIt.instance;
  sl.registerLazySingleton<ApiClient>(() => ApiClient());
  sl.registerFactory<PlaceBloc>(() => PlaceBloc(sl<ApiClient>()));
  ```
* **الاستدعاء الآمن في أي شاشة برمجية:**
  ```dart
  final placeBloc = di.sl<PlaceBloc>();
  ```
  يقوم نظام GetIt بالبحث عن الـ `PlaceBloc` المطلوب، ويجد أنه يتطلب `ApiClient` للعمل، فيقوم بجلب نسخة الـ `ApiClient` المسجلة تلقائياً وتمريرها له وحقنها برفق! هذا يجعل الكود مرناً، نظيفاً، وسهلاً جداً للتعديل والصيانة.

---

## ✏️ تدريب عملي موسع:
افتح المجلد [frontend/lib/core/di/](file:///c:/namerha_sah/frontend/lib/core/di) أو ملف التبعيات الرئيسي لتطبيقك.
1. اكتب قائمة بجميع الخدمات والكلاسات والـ Blocs المسجلة في حاوي التبعيات `GetIt`.
2. اشرح الفرق الجوهري البرمجي بين `registerLazySingleton` و `registerFactory` ومتى نستخدم كل منهما.
