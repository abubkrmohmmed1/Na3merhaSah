<div dir="rtl" style="font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; padding: 30px; border-radius: 12px; color: #333;">

<div style="text-align: center; margin-bottom: 40px;">
    <img src="file:///c:/namerha_sah/logo.jpeg" alt="شعار نعمرها صح" style="width: 150px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <h1 style="color: #1A284D; font-weight: 800; font-size: 36px; margin-top: 20px; margin-bottom: 5px;">ملخص مشروع "نعمّرها صح" (Na3merha Sah)</h1>
    <h3 style="color: #4B7DF3; font-weight: 700; margin-top: 0;">منصة البيانات المكانية والعنونة الرقمية الذكية الأولى في السودان</h3>
    <p style="color: #666; font-size: 14px; margin-top: 8px;">مقدم إلى منظمة "أثر نفير" وجهات الدعم والتمكين — هاكاثون نفير الرقمي 2026 — فريق البنية التحتية 2</p>
</div>

<hr style="border: 0; height: 1px; background: #E5E7EB; margin-bottom: 30px;">

## <span style="color: #FACC6B;">■</span> 1. الرؤية والرسالة

**"نعمّرها صح"** هي منصة بيانات مكانية ذكية (Geospatial Data Platform) تُعيد تعريف مفهوم العنونة في السودان.

السودان يعاني من **"العمى الجغرافي"** — غياب نظام عنونة رسمي يجعل كل عملية توصيل أو توثيق أو تخطيط تعتمد على التخمين. هذا يكلف الاقتصاد ملايين الدولارات سنوياً في شكل وقت ضائع ووقود مبذور وقرارات خاطئة.

**هدفنا:** بناء بديل رقمي للرمز البريدي المفقود — كل موقع في السودان يحصل على كود فريد (Plus Code) يمكن تبادله بسهولة، ويستخدمه أي شخص أو نظام للعثور عليه بدقة.

---

## <span style="color: #FACC6B;">■</span> 2. المشكلة الحقيقية (Why Now?)

### المشكلة ليست "حفرة في الشارع" — المشكلة هي **غياب العنوان الرقمي**.

<div style="background-color: #fff3cd; border-right: 4px solid #FACC6B; padding: 15px; border-radius: 8px; margin: 15px 0;">
<strong>💡 نقطة مهمة:</strong> السودان — مثل كثير من دول أفريقيا والشرق الأوسط — يفتقر لنظام عنونة بريدي رسمي. العنوان الوحيد المتوفر هو رقم الهاتف. هذا يخلق فجوة رقمية تؤثر على كل عملية توصيل أو توثيق أو تخطيط.
</div>

* **خسائر التوصيل (الميل الأخير):** محلات السوبرماركت وتطبيقات التوصيل تخسر أكثر من <strong>30%</strong> من وقت المندوب ووقوده في محاولة الوصول للعناوين التقريبية ("جنب المخبز الفلاني"). فشل التوصيل يعني وقت مندوب مهدور + وقود إضافي + إحباط العميل — وكل توصيل ناجح بعد محاولات متعددة يضاعف التكلفة.

* **أزمة التوثيق البنكي (KYC):** البنوك وشركات التقنية المالية تعتمد على شهادات السكن الورقية صادرة من اللجان الشعبية للأحياء — نظام بطيء (3-7 أيام)، غير رقمي، ومعرض للتلاعب والتزوير. البنك المركزي يفرض KYC صارم لكن لا يوجد نظام عنونة رقمي يدعمه. منصتنا تقدم بديل رقمي يمكن التحقق منه آنياً عبر API.

* **الفجوة التشغيلية الإنسانية:** المنظمات الدولية (OCHA, IOM) عندهم خرائط theory دقيقة (DTM, IPC) — لكن المشكلة في: (1) ديناميكية الحركة السريعة (20K أسرة تنزح في 48 ساعة), (2) عائق الوصول الإنساني (access barriers), (3) انهيار شبكات الاتصالات ميدانياً, (4) عدم تنسيق المخبرين المحليين. منصتنا تحل الفجوة دي بأدوات تحديث ميداني بدون إنترنت + توثيق وصول الإغاثة + تنسيق القاعدة.

* **استحالة التوسع التجاري:** شركات التوزيع (FMCG) تعتمد على ذاكرة السائقين — لا بيانات جغرافية حقيقية عن الأسواق والأحياء والقرى. التوسع يتم بالتخمين وليس بالبيانات.

---

## <span style="color: #FACC6B;">■</span> 3. الحل: محرّك بيانات مكانية + نظام عنونة ذكي

### الركيزة الأولى: محرك البيانات متعدد الطبقات

نظامنا يُبني بيانات مكانية على **3 مستويات إدارية + طبقات تشغيلية**:

#### أ. المستويات الإدارية:
* 🗺️ **18 ولاية** سودانية بحدودها الجغرافية الكاملة
* 📌 **193 محلية إدارية + وحدات إدارية** مسجلة ومرتبطة مكانياً
* 🏡 **أكثر من 4,000 حي وقرية** محددة بدقة جغرافية متناهية

#### ب. الطبقات التشغيلية (POIs):
* مرافق حيوية: مستشفيات، صيدليات، مخابز، مدارس، بنوك، مطاعم
* خدمات مجتمعية: مراكز الشرطة، الإطفاء، محطات الوقود

#### ج. الطبقات المخطط لها (قيد التطوير):
* بنية تحتية: حالة الطرق (معبدة/ترابية/متضررة) — لتصنيف الشوارع ومساعدة شركات اللوجستيات في حساب وقت التوصيل.
* طبقات خاصة بالاستجابة الإنسانية والتدخل الطارئ — لرصد إغلاقات الطرق، بؤر المخاطر، والتحويلات الطارئة.

#### د. الأرقام الحالية في قاعدة البيانات:
* 📍 **8,004+** منشأة حيوية وخدمية مسجلة
* 🌾 **678** قرية ريفية موثقة الإحداثيات
* 🧭 **12,300+** معلم جغرافي إجمالي على مستوى السودان

---

### الركيزة الثانية: نظام العنونة الرقمية البديلة

#### كيف يعمل النظام؟
* **نظام S2 Geometry:** كل موقع يُقسم إلى خلايا رقمية (~3م × 3م)، كل خلية تحصل على ID فريد يُستخدم داخلياً في الـ APIs.
* **الأكواد المختصرة (Plus Codes):** للمستخدم النهائي، نولّد كود قصير مثل `6GCR+2X` — سهل الكتابة والنسخ والمشاركة.

**مثال عملي:** المستخدم يفتح التطبيق، يضغط "عنواني"، يختار موقعه على الخريطة أو يضغط GPS. النظام يولّد له Plus Code للمشاركة + S2 Token للـ APIs + العنوان الحرفي من البيانات المتوفرة.

#### ثلاثة مستويات للخصوصية:

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 10px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المستوى</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">الوصف</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">من يرى العنوان؟</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>خاص (Private)</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">للمستخدم فقط</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">فقط المستخدم عبر حسابه</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>شركات فقط (Business)</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">يظهر عند البحث العكسي</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">APIs عند الطلب (بنوك، توصيل) — لا يظهر في القوائم العامة</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>عام (Public)</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">يظهر للجميع</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">كل مستخدم للتطبيق + خريطة المعالم</td>
    </tr>
  </tbody>
</table>
</div>

---

## <span style="color: #4B7DF3;">■</span> 4. نموذج جمع البيانات (العمل الحر المصغر)

لحل معضلة "لماذا سيستخدم المواطن التطبيق؟"، حوّلنا التطبيق إلى أداة **مهام مصغرة وألعاب (Micro-Gigs)**:

* **حافز المنفعة (النجاة):** المستخدم يختار مهمة في منطقته (مثل: "تأكد من أن صيدلية الحي الفلاني مفتوحة الآن")، يصوّر المنشأة أو يؤكد حالتها.
* **نظام التحفيز الحالي:** مكافآت على شكل **نقاط** غير قابلة للتحويل المباشر — الأوسمة والرتب فقط ("عمدة الحي"، "بطل الحي"، "رائد الخرائط").
  * **ملاحظة:** الجوائز المادية العينية (شحن رصيد، كوبونات) مخطط لها لكنها مرتبطة بشراكات مستقبلية مع شركات الاتصالات — غير مفعلة حالياً.
* **نظام الأوسمة والرتب:** يعزز الانتماء والمنافسة بين الشباب في توثيق مناطقهم.

---

## <span style="color: #FACC6B;">■</span> 5. واجهات المرافق التجارية وشركات التوصيل (قيد التطوير)

نخطط لبناء **واجهات خاصة بالشركات (B2B Portals)** لتوفير بيانات موثقة ومحدثة:

* **بروفايلات المرافق التجارية:** كل مخبز، صيدلية، مستشفى، مطعم يحصل على صفحة خاصة به تتضمن:
  * معلومات الاتصال (رقم الهاتف، العنوان، ساعات العمل).
  * تقييمات ومراجعات المستخدمين.
  * حالة التشغيل الحالية (مفتوح/مغلق).
  * صور المرفق.

* **بروفايلات شركات التوصيل:** شركات التوصيل يمكنها:
  * تأكيد سجلها التجاري عبر التطبيق.
  * الوصول لبيانات العملاء المعنيين (عناوين مشفرة).
  * إدارة طلبات التوصيل والتتبع.

* **نظام التقييمات المجتمعي:** أي مستخدم يمكنه تقييم مرفق أو الإبلاغ عن معلومة خاطئة — هذا يضمن جودة البيانات باستمرار.

---

## <span style="color: #FACC6B;">■</span> 6. القيمة التجارية ونموذج الإيرادات (B2B SaaS / DaaS)

هذه **ليست مجرد خريطة** — بل هي **مركز بيانات جغرافي API-first** تبيعه للشركات:

* **Pay-per-API Call:** رسوم على كل عملية reverse geocode أو توليد Plus Code أو إثبات سكن (Starter $0.01, Growth $0.008, Scale $0.006, Enterprise $0.005 — avg $0.006-$0.008). التسعير معدّل للسوق السوداني.
* **اشتراكات SaaS:** dashboards تحليلية للخرائط الحرارية والكثافة — لشركات العقارات وشركات السلع الاستهلاكية.
* **Data Licensing (DaaS):** بيع حقائب جغرافية (GeoJSON) + أدوات التحقق الميداني للمنظمات الدولية (UNCHR, UNICEF, OCHA, IOM). ملاحظة: هذه المنظمات عندهم خرائط theory جيدة — لكن يحتاجوا أدوات ميدانية لتحديث البيانات بدون إنترنت، وتوثيق وصول الإغاثة، وتنسيق المخبرين المحليين.

---

## <span style="color: #4B7DF3;">■</span> 7. جاهزية المشروع (Technical Readiness)

<div style="display: flex; gap: 20px; margin-bottom: 20px;">
    <div style="flex: 1; border: 1px solid #E5E7EB; border-radius: 8px; padding: 15px; background-color: #fff;">
        <h4 style="color: #27AE60; margin-top: 0;">✅ جاهز الآن:</h4>
        <ul style="padding-right: 20px;">
            <li>محرك <b>S2 Geometry</b> يولّد الإحداثيات الشبكية تلقائياً لكل معلم.</li>
            <li>قاعدة بيانات <b>PostGIS</b> مهيأة للبحث المكاني بأداء عالٍ.</li>
            <li>تطبيق <b>Flutter</b> للمواطن (Offline-First) يعمل بدون إنترنت.</li>
            <li>لوحة تحكم إدارية <b>(Laravel)</b> تعرض التقارير الجغرافية والخرائط.</li>
            <li>توثيق <b>APIs</b> كامل (Swagger/OpenAPI) جاهز لدمج فوري.</li>
            <li>تحويل واجهة الموبايل لتدعم نظام "تأكيد النقاط والمهمات".</li>
            <li>بناء <b>B2B Gateway</b> وواجهات الـ API للشركات.</li>
            <li><b>Scripts الاستيراد</b> جاهزة لجلب بيانات OSM والبيانات المفتوحة.</li>
        </ul>
    </div>
    <div style="flex: 1; border: 1px solid #E5E7EB; border-radius: 8px; padding: 15px; background-color: #fff;">
        <h4 style="color: #E67E22; margin-top: 0;">🚧 قيد التطوير:</h4>
        <ul style="padding-right: 20px;">
            <li>واجهة تسجيل العنوان البريدي للأفراد والمنازل (مع خصوصية وموثوقية).</li>
            <li>نظام المكافآت المادية العينية (شراكات اتصالات مطلوبة).</li>
            <li>بروفايلات المرافق التجارية (تقييمات + معلومات الاتصال).</li>
            <li>بروفايلات شركات التوصيل الموثقة.</li>
            <li>طبقة حالة الطرق (معبدة/ترابية/متضررة).</li>
            <li>طبقة الاستجابة الإنسانية والتدخل الطارئ.</li>
        </ul>
    </div>
</div>

---

## <span style="color: #FACC6B;">■</span> 8. استراتيجية الدخول للسوق والقطاعات المستهدفة

**السوق المستهدف ليست شركة واحدة — بل منظومة كاملة.**

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 8px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 8px 12px; border: 1px solid #ddd;">القطاع</th>
      <th style="padding: 8px 12px; border: 1px solid #ddd;">القيمة المقدمة</th>
      <th style="padding: 8px 12px; border: 1px solid #ddd;">نموذج الإيرادات</th>
      <th style="padding: 8px 1px; border: 1px solid #ddd;">الأولوية</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;">
      <td style="padding: 8px 12px; border: 1px solid #ddd;"><b>محلات السوبرماركت والبقالة</b></td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">يقلل فشل التوصيل + يوفر وقت السائق + يحسن تجربة العميل</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">Pay-per-API</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">⭐⭐⭐ عالية جداً</td>
    </tr>
    <tr style="background-color: #d1fae5;">
      <td style="padding: 8px 12px; border: 1px solid #ddd;"><b>تطبيقات التوصيل المحلية</b></td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">تحديد موقع العميل بدقة + تحسين وقت التوصيل + تقليل الفشل</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">Pay-per-API + اشتراك</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">⭐⭐⭐ عالية</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 8px 12px; border: 1px solid #ddd;"><b>صيدليات كبرى</b></td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">توصيل أدوية وصفات طبية بدقة + تتبع الطلبات</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">Pay-per-API + اشتراك</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">⭐⭐⭐ عالية</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 8px 12px; border: 1px solid #ddd;"><b>المنظمات الإنسانية والإغاثية</b></td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">خرائط حية للاحتياجات + تتبع التوزيع الجغرافي + إدارة المستفيدين</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">اشتراك شهري + Data Licensing</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">⭐⭐⭐ عالية</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 8px 12px; border: 1px solid #ddd;"><b>شركات البصات والسفرية</b></td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">تحليل جغرافي للعملاء + فتح فروع جديدة + تتبع الشحنات — مش address API</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">SaaS + Data Analytics</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">⭐⭐ متوسطة (GIS)</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 8px 12px; border: 1px solid #ddd;"><b>شركات التوزيع (FMCG)</b></td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">تحليل السوق + تحسين التوزيع + تحديد مواقع جديدة</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">اشتراك SaaS</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">⭐⭐ متوسطة</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 8px 12px; border: 1px solid #ddd;"><b>بنوك و FinTech</b></td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">توثيق سكن آني (KYC) + التحقق من الهوية</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">Pay-per-API</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">⭐ منخفضة (دورة بيع طويلة)</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 8px 12px; border: 1px solid #ddd;"><b>شركات التسويق والحملات</b></td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">استهداف جغرافي دقيق للحملات + قياس الأثر</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">Pay-per-campaign</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">⭐ منخفضة</td>
    </tr>
  </tbody>
</table>
</div>

<br>

**استراتيجية البداية:**

1. **البيانات جاهزة من اليوم الأول:** نستورد بيانات مفتوحة (OpenStreetMap) كي لا تبدأ بخريطة فارغة — العميل يرى خدمات ومناطق فور الاشتراك.

2. **إثبات القيمة أولاً:** نقدم PoC مجاني لشركات التوصيل والمنظمات الإنسانية — إذا أثبتنا توفير 30% من التكاليف، البيع يأتي تلقائياً.

3. **كل استخدام يولّد بيانات:** أنماط الطلب، الفجوات في الخدمات، الكثافة السكانية — هذه البيانات مجمعة تصبح منتجاً منفصلاً قابلاً للبيع.

4. **جوائز حقيقية:** شراكات مع MTN و Zain لتحويل نقاط المستخدم إلى رصيد اتصالات — يربط التطبيق بواقعه اليومي.

5. **التوسع قطاع بقطاع:** نبدأ where friction is lowest (delivery + humanitarian) — البنوك هدف طويل الأمد.

---

## <span style="color: #4B7DF3;">■</span> 9. البيانات المستقبلية: ما الذي يجمعه النظام؟

**النظام لا يقدم فقط خريطة — بل يجمع بيانات تتحول لمنتج.**

<div style="background-color: #e8f4f8; border-right: 4px solid #4B7DF3; padding: 15px; border-radius: 8px; margin: 15px 0;">
<strong>الفكرة:</strong> كل استخدام للنظام (توصيل، إغاثة، صحة، تسويق) يولّد بيانات جديدة. هذه البيانات مجمعة (aggregated) تصبح منتجاً قابلاً للبيع والتحليل.
</div>

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 10px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">مصدر البيانات</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">البيانات المجمعة</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">القيمة</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>محلات السوبرماركت</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">أنماط الطلب على المنتجات، المناطق ذات الكثافة الشرائية العالية، أوقات الذروة</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">تحليل السوق + استراتيجية التوسع</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>صيدليات كبرى</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">توزيع الطلب على الأدوية، الأنواع الأكثر طلباً، الكثافة السكانية المحتاجة لصيدليات</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">تحليل صحي + فرص franchise</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>تطبيقات التوصيل</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">حجم الطلب في كل حي، المنتجات الأكثر طلباً، أوقات الذروة، أسباب فشل التوصيل</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">تحليل السوق + تحديد مواقع التوسع</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>المنظمات الإنسانية</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">توزيع الاحتياجات الإنسانية، الكثافة السكانية المتغيرة، حالة البنية التحتية</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">تخطيط الاستجابة + خرائط الأوبئة</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>القطاع الصحي</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">أنماط انتشار الأمراض، المسافات إلى المرافق الصحية، الطلب على أدوية معينة</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">تحليل وبائي + تخصيص الموارد</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>التقييمات المجتمعية</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">حالة المرافق (مفتوح/مغلق)، جودة الخدمات، الفجوات في كل حي</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">خرائط الفجوات (Gap Analysis)</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>العناوين المسجلة</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">التوزيع السكاني، الكثافة في كل منطقة، اتجاهات النمو الحضري</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">التنمية الحضرية + تخطيط البنية التحتية</td>
    </tr>
  </tbody>
</table>
</div>

<br>

<strong>تحليلات مخطط لها:</strong>
* <b>خرائط الحرارة (Heatmaps):</b> الكثافة السكانية، حجم الطلب، جودة الخدمات.
* <b>تحليل الفجوات (Gap Analysis):</b> أين لا توجد مستشفيات ضمن 5 كم؟ أين الكثافة عالية + المرافق قليلة؟
* <b>التنبوء (Prediction):</b> اتجاهات النمو السكاني، الطلب المستقبلي على الخدمات، مواقع التوسع المثلى.

---

## <span style="color: #FACC6B;">■</span> 11. ماذا نحتاج منكم؟

1. <strong>تسهيل الشراكات التجريبية (PoC):</strong> نربط مع محلات السوبرماركت وتطبيقات التوصيل المحلية (مرسال وغيره) والصيدليات الكبرى والمنظمات الإنسانية لتجربة دمج الـ API وإثبات القيمة الفورية. التركيز على قطاعات سريعة التبني (سوبرماركت + تطبيقات توصيل + humanitarian) وليس القطاعات البطيئة (بنوك، شحن).
2. <strong>التمويل ($40,000 Pre-Seed):</strong> لتغطية تطوير المنتج + تسويق + مساحات عمل + مصاريف قانونية + buffer للطوارئ.
3. <strong>الإرشاد الريادي:</strong> صياغة العقود التجارية للـ Enterprise وتطوير استراتيجية التوسع على المستوى الإقليمي (السودان اولاً ثم شرق أفريقيا).
4. <strong>تسهيل شراكات التحفيز:</strong> ربطنا مع شركات الاتصالات (MTN, Zain) لتوفير جوائز عينية للمستخدمين (شحن رصيد) — هذا يحول نظام النقاط من مجرد لعبة إلى قيمة حقيقية.

<br>

### الاستثمار المطلوب ($40,000 Pre-Seed):

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 10px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">البند</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المبلغ</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">النسبة</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">تطوير المنتج (frontend + backend + APIs + Mobile)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$18,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">45%</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">تسويق ومبيعات (PoC campaigns + سفر + معارض)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$10,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">25%</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">buffer الطوارئ + runway إضافي</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$7,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">17.5%</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">مساحات عمل + مصاريف قانونية + infrastructure</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$5,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">12.5%</td></tr>
  </tbody>
</table>
</div>

<div style="background-color: #d1fae5; padding: 10px; border-radius: 8px; margin-top: 10px;">
<strong>ملاحظة:</strong> البنية التحتية السحابية (DigitalOcean) = $49/شهر فقط — ليست العائق. التوفير من استثمار infrastructure ينقل لتطوير المنتج والتسويق.
</div>

---

## <span style="color: #FACC6B;">■</span> 12. الأرقام على الأرض (Database-Verified)

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 10px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المقياس</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">العدد</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">الولايات المسجلة</td><td style="padding: 10px 14px; border: 1px solid #ddd;">18</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">المحليات الإدارية</td><td style="padding: 10px 14px; border: 1px solid #ddd;">193</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">الأحياء والقرى</td><td style="padding: 10px 14px; border: 1px solid #ddd;">4,000+</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">المنشآت والخدمات</td><td style="padding: 10px 14px; border: 1px solid #ddd;">8,004</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي المعالم</td><td style="padding: 10px 14px; border: 1px solid #ddd;">12,300+</td></tr>
  </tbody>
</table>
</div>

---

## <span style="color: #4B7DF3;">■</span> 13. المخاطر والتحديات

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 10px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المخاطر</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">خطة التخفيف</th>
    </tr>
  </thead>
  <tbody>
     <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">صعوبة جذب المستخدمين للتطبيق</td><td style="padding: 10px 14px; border: 1px solid #ddd;">نظام مكافآت جذاب + شراكات اتصالات</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">جودة البيانات في المناطق النائية</td><td style="padding: 10px 14px; border: 1px solid #ddd;">استيراد OSM + مهام مجتمعية + تحقق تدريجي (3 levels)</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">المنافسة (Google Maps, etc.)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">التركيز على العنونة + البيانات المحلية + APIs للـ B2B</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">مقاومة التغيير من السوبرماركت وتطبيقات التوصيل والصيدليات</td><td style="padding: 10px 14px; border: 1px solid #ddd;">PoC مجاني + إثبات توفير 30% من التكاليف</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">خصوصية بيانات المستخدمين</td><td style="padding: 10px 14px; border: 1px solid #ddd;">Hashing للبيانات الحساسة + مستويات خصوصية متعددة + تشفير HTTPS</td></tr>
  </tbody>
</table>
</div>

<br>
<div style="background-color: #1A284D; color: white; padding: 20px; border-radius: 8px; text-align: center; margin-top: 30px; border-bottom: 4px solid #FACC6B;">
    <h3 style="margin: 0; color: white;">معاً.. نعمّرها صح، نبتكر لنرمم, نتواصل لنعمر 🗺️✨</h3>
    <span style="font-size: 14px; opacity: 0.8; color: #A3AED0;">فريق البنية التحتية 2 — هاكاثون نفير الرقمي 2026</span>
</div>

</div>