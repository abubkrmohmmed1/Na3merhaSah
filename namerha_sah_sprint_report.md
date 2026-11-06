<div dir="rtl" style="font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; padding: 30px; border-radius: 12px; color: #333;">

<div style="text-align: center; margin-bottom: 40px;">
    <img src="file:///c:/namerha_sah/backend/public/images/logo.jpeg" alt="شعار نعمرها صح" style="width: 150px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
    <h1 style="color: #1A284D; font-weight: 800; font-size: 36px; margin-top: 20px; margin-bottom: 5px;">التقرير الشامل: مشروع نعمرها صح</h1>
    <h3 style="color: #4B7DF3; font-weight: 700; margin-top: 0;">من التأسيس المعماري إلى إطلاق لوحة التحكم الشاملة</h3>
</div>

<hr style="border: 0; height: 1px; background: #E5E7EB; margin-bottom: 30px;">

## <span style="color: #FACC6B;">■</span> 1. الرؤية والملخص التنفيذي (Executive Summary)
مشروع **"نعمرها صح"** هو نظام تقني متكامل يهدف إلى رقمنة قطاع الخدمات العامة والبنية التحتية. يعتمد النظام على حلول العنونة الرقمية المتقدمة لربط بلاغات المواطنين بجهات الاختيار بدقة متناهية، مما يسرع من عمليات الصيانة، التقييم الهندي، والإشراف الإداري.

---

## <span style="color: #4B7DF3;">■</span> 2. تسلسل العمل والدورة الإجرائية (Workflow Sequence)
يعمل النظام وفق دورة حياة مغلقة تبدأ من المواطن وتنتهي بإغلاق البلاغ بنجاح:
1. **مرحلة البلاغ:** يقوم المواطن عبر التطبيق بالتقاط صورة للمشكلة وتحديد الموقع الجغرافي الدقيق.
2. **الاستلام والتوجيه:** يصل البلاغ فوراً إلى لوحة تحكم الجهة الحكومية المعنية.
3. **المعاينة الفنية:** يتم تحويل البلاغ لمساح ميداني لزيارة الموقع ورفع تقرير فني.
4. **قرار الحل:** 
   - إذا كان المشكل بسيطاً، يتم الحل فوراً.
   - إذا كان مشروعاً هندسياً، يتم تحويله للقسم الهندسي/المقاول لتقدير التكلفة.
5. **الاعتماد والتنفيذ:** يتم اعتماد الميزانية والمناقصة والبدء في التنفيذ الميداني.
6. **الاستلام النهائي:** يتم التأكد من جودة العمل وإخطار المواطن بالانتهاء عبر التطبيق.

---

## <span style="color: #4B7DF3;">■</span> 3. تطبيق المواطن (Mobile Application - Flutter)
تم تنفيذ واجهات التطبيق بدقة عالية لتناسب احتياجات المستخدم الميدانية:
* **تحديد الموقع الذكي:** واجهة تعتمد على خرائط جوجل مع التقاط آلي للعنوان وكود S2 Token.
* **نماذج البلاغات المبسطة:** اختيار التصنيف (مياه، كهرباء، طرق..) مع وصف المشكلة وإرفاق الصور.
* **تتبع الحالة:** شاشة مخصصة لمتابعة مراحل تقدم البلاغ لحظة بلحظة.

---

## <span style="color: #4B7DF3;">■</span> 4. لوحة تحكم الإدارة (Web Admin Panel)
العقل المركزي لإدارة العمليات، وتتميز بـ:
* **مؤشرات الأداء (KPIs):** عرض إحصائيات حية لعدد البلاغات، معدلات الحل، وحالات التنفيذ.
* **إدارة جغرافية:** خريطة تفاعلية تعرض توزيع المشكلات في المناطق المختلفة.
* **توزيع الأدوار:** نظام صلاحيات للمنسقين والمهندسين والجهات الحكومية.

---

## <span style="color: #4B7DF3;">■</span> 5. معرض الواجهات النهائية (System Interfaces)

### 💻 شاشات لوحة تحكم الإدارة (Admin Dashboard)
<div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 30px;">
    <div style="width: 100%; border: 2px solid #E5E7EB; border-radius: 12px; overflow: hidden; background-color: #fff; text-align: center; padding: 10px;">
        <span style="color: #6B7280; font-size: 14px; font-weight: bold;">اللوحة الرئيسية (الإحصائيات والخرائط)</span><br>
        <img src="file:///C:/Users/abubakr/Pictures/screenshots/Screenshot 2026-04-24 190009.png" alt="اللوحة الرئيسية" style="max-width: 100%; height: auto; border-radius: 8px; margin-top: 10px;">
    </div>
    <div style="width: 100%; border: 2px solid #E5E7EB; border-radius: 12px; overflow: hidden; background-color: #fff; text-align: center; padding: 10px;">
        <span style="color: #6B7280; font-size: 14px; font-weight: bold;">عرض الشكاوى والبلاغات</span><br>
        <img src="file:///C:/Users/abubakr/Pictures/Screenshots/Screenshot 2026-04-24 190041.png" alt="عرض الشكاوى" style="max-width: 100%; height: auto; border-radius: 8px; margin-top: 10px;">
    </div>
    <div style="display: flex; gap: 20px; justify-content: space-between;">
        <div style="flex: 1; border: 2px solid #E5E7EB; border-radius: 12px; overflow: hidden; background-color: #fff; text-align: center; padding: 10px;">
            <span style="color: #6B7280; font-size: 14px; font-weight: bold;">تفاصيل تقرير المساح</span><br>
            <img src="file:///C:/Users/abubakr/Pictures/Screenshots/Screenshot 2026-04-24 190143.png" alt="تقرير المساح" style="max-width: 100%; height: auto; border-radius: 8px; margin-top: 10px;">
        </div>
        <div style="flex: 1; border: 2px solid #E5E7EB; border-radius: 12px; overflow: hidden; background-color: #fff; text-align: center; padding: 10px;">
            <span style="color: #6B7280; font-size: 14px; font-weight: bold;">إدارة الجهات الحكومية</span><br>
            <img src="file:///C:/Users/abubakr/Pictures/Screenshots/Screenshot 2026-04-24 190227.png" alt="الجهات الحكومية" style="max-width: 100%; height: auto; border-radius: 8px; margin-top: 10px;">
        </div>
    </div>
    <div style="width: 100%; border: 2px solid #E5E7EB; border-radius: 12px; overflow: hidden; background-color: #fff; text-align: center; padding: 10px;">
        <span style="color: #6B7280; font-size: 14px; font-weight: bold;">إدارة المستخدمين</span><br>
        <img src="file:///C:/Users/abubakr/Pictures/Screenshots/Screenshot 2026-04-24 190309.png" alt="إدارة المستخدمين" style="max-width: 100%; height: auto; border-radius: 8px; margin-top: 10px;">
    </div>
</div>

### 📱 تطبيق الموبايل (المواطن)
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
    <div style="border: 2px solid #E5E7EB; border-radius: 12px; padding: 10px; background: #fff; text-align: center;">
        <span style="font-weight: bold; color: #6B7280;">الشاشة الرئيسية</span><br>
        <img src="file:///c:/namerha_sah/design/Screenshot_٢٠٢٦٠٤٢٥_١٢٤٠٣٣.png" style="max-width: 100%; border-radius: 8px;">
    </div>
    <div style="border: 2px solid #E5E7EB; border-radius: 12px; padding: 10px; background: #fff; text-align: center;">
        <span style="font-weight: bold; color: #6B7280;">تحديد موقع البلاغ</span><br>
        <img src="file:///c:/namerha_sah/design/Screenshot_٢٠٢٦٠٤٢٥_١٢٣٣٥٥.png" style="max-width: 100%; border-radius: 8px;">
    </div>
    <div style="border: 2px solid #E5E7EB; border-radius: 12px; padding: 10px; background: #fff; text-align: center;">
        <span style="font-weight: bold; color: #6B7280;">نموذج إرسال البلاغ</span><br>
        <img src="file:///c:/namerha_sah/design/Screenshot_٢٠٢٦٠٤٢٥_١٢٤٦٣٠.png" style="max-width: 100%; border-radius: 8px;">
    </div>
    <div style="border: 2px solid #E5E7EB; border-radius: 12px; padding: 10px; background: #fff; text-align: center;">
        <span style="font-weight: bold; color: #6B7280;">تتبع حالة البلاغات</span><br>
        <img src="file:///c:/namerha_sah/design/Screenshot_٢٠٢٦٠٤٢٥_١٢٤٠١٩.png" style="max-width: 100%; border-radius: 8px;">
    </div>
</div>

---

## <span style="color: #FACC6B;">■</span> 6. الخطوات القادمة
1. تفعيل نظام الإشعارات الفورية للمواطنين.
2. تصدير تقارير الأداء الشهرية بصيغة PDF.
3. دمج التحليلات التنبؤية للتنبؤ بالأعطال قبل وقوعها.

<br>
<div style="background-color: #1A284D; color: white; padding: 20px; border-radius: 8px; text-align: center; margin-top: 30px; border-bottom: 4px solid #FACC6B;">
    <h3 style="margin: 0; color: white;">مشروع "نعمرها صح"</h3>
    <span style="font-size: 16px; font-weight: 700; color: #FACC6B;">فريق البنية التحتية 2</span><br>
    <span style="font-size: 12px; opacity: 0.8;">وثيقة تقييم الإنجاز - أبريل 2026</span>
</div>

</div>
