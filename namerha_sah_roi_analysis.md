# تحليل العائد على الاستثمار (ROI Analysis)
## مشروع "نعمّرها صح" — Na3merha Sah
### هاكاثون نفير الرقمي 2026 — Athar Nafir

---

## 1. افتراضات السوق (Market Assumptions)

### 1.1 حجم السوق (Market Size)

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">القطاع</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">عدد الشركات المستهدفة</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">متوسط API calls/شهر</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">TAM ($/شهر)</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">ملاحظات</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>محلات السوبرماركت والبقالة</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">50-100 متجر كبير</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">20,000 - 80,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$1,000 - $4,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">أعلى أولوية — يحتاجوا تحديد العنوان للتوصيل</td>
    </tr>
    <tr style="background-color: #d1fae5;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>تطبيقات التوصيل المحلية</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">5-10 تطبيقات (مرسال وغيره)</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">50,000 - 200,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$500 - $2,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">أولوية عالية — عندهم infrastructure جاهز</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>صيدليات كبرى</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">10-30 صيدلية كبرى</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">10,000 - 50,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$200 - $1,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">توصيل أدوية وصفات طبية — يحتاج دقة</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>منظمات إغاثية</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">20-40 منظمة في السودان</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">20,000 - 100,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$400 - $2,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">UNCHR, UNICEF, OCHA, ICRC — TAM كبير</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>شركات البصات والسفرية</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">30-50 شركة</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">5,000 - 20,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$150 - $600</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">GIS analytics + تتبع — مش address API</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>FMCG وعلامات تجارية</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">20-50 شركة توزيع</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">5,000 - 30,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$100 - $600</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">Coca-Cola distributors, water, oils</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>بنوك و FinTech</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">5-15 بنك/شركة</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">10,000 - 100,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$200 - $2,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">Long sales cycle — عام 2-3</td>
    </tr>
  </tbody>
</table>
</div>

<div style="background-color: #fee2e2; border-right: 4px solid #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 15px; margin-top: 15px;">
<strong>ملاحظة مهمة عن السوق السوداني:</strong>
<p>أغلب الشركات (شركات البصات، الشحن الدولي) لا تحتاج address API لأنها تعتمد على نظام "الزبون يجي ياخد من المكتب" — الزبون هو اللي يروح للشركة مش العكس. الفئة الحقيقية المحتاجة هي: <strong>محلات السوبرماركت + تطبيقات التوصيل + صيدليات + منظمات إغاثية</strong>.</p>
</div>

**TAM (Total Addressable Market):** ~$40,000-$100,000/شهر (Sudan only — القطاعات الفعلية المحتاجة)
**SAM (Serviceable Addressable Market):** ~$8,000-$30,000/شهر (initial 3-5 years)
**SOM (Serviceable Obtainable Market):** ~$1,500-$6,000/شهر (year 1 — 3-10 عملاء)

---

## 1.5. تسعير الـ API (Pricing Tiers — مناسب للسوق السوداني)

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">الطبقة</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">حجم الاستخدام</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">السعر/ call</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">Monthly Min</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">ملاحظات</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>Starter (لـ startups)</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">حتى 5K calls/شهر</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$0.01</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$50</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">لـ early-stage startups + محلات صغيرة</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>Growth</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">5K - 30K calls</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$0.008</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$150 - $240</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">لـ growing businesses</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>Scale</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">30K - 100K calls</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$0.006</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$450 - $900</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">لـ established companies</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>Enterprise</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">+100K calls</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$0.005</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$500+</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">volume discount</td>
    </tr>
    <tr style="background-color: #d1fae5;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>NGO / Humanitarian</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">unlimited</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">fixed</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$300 - $800</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">subscription model + data licensing</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>PoC مجاني</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">1,000 calls</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">مجاني</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">—</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">للإثبات + onboarding</td>
    </tr>
  </tbody>
</table>
</div>

**متوسط السعر الفعلي:** ~$0.006-$0.008/call
**ملاحظة:** التسعير معدّل للسوق السوداني — lower price = more clients, lower revenue per client لكن volume higher

---

### 1.6. الميزانية ($40,000) — Sudan-optimized

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-bottom: 20px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">البند</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المبلغ</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">النسبة</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">التبرير</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>تطوير المنتج</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$18,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">45%</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">frontend + backend + APIs + Mobile (6 أشهر)</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>تسويق ومبيعات</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$10,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">25%</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">PoC campaigns + سفر + معارض + شراكات</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>البنية التحتية</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$1,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">2.5%</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">DigitalOcean ($588/سنة) + setup costs</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>مساحات عمل</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$2,400</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">6%</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">coworking ($200/شهر × 12)</td>
    </tr>
    <tr style="background-color: #fff;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>مصاريف قانونية</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$1,600</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">4%</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">TREC registration + company setup</td>
    </tr>
    <tr style="background-color: #f8f9fa;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>buffer للطوارئ</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">$7,000</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">17.5%</td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;">مصاريف غير متوقعة + runway إضافي</td>
    </tr>
    <tr style="background-color: #d1fae5;">
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>المجموع</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>$40,000</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;"><b>100%</b></td>
      <td style="padding: 10px 14px; border: 1px solid #ddd;"></td>
    </tr>
  </tbody>
</table>
</div>

<div style="background-color: #fef3c7; border-right: 4px solid #f59e0b; padding: 10px; border-radius: 8px; margin-bottom: 10px;">
<strong>ملاحظة:</strong> البنية التحتية الفعلية = $49/شهر فقط. وفّرنا $3,000+ ونقلناها لـ buffer + runway.
</div>

---

## 2. سيناريوهات الاستثمار والعائد

### السيناريو 1: متشائم (Pessimistic)

<div style="background-color: #fee2e2; border-right: 4px solid #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
<strong>الافتراضات:</strong>
- تأخير 6 أشهر في أول عميل
- churn 30% (عملاء يخرجون)
- سعر API = $0.01 (floor price — cheapest tier)
</div>

| الشهر | عملاء API |.calls | SaaS | Data | الإيراد | التراكم |
|---|---|---|---|---|---|---|
| 1-6 | 0 | 0 | 0 | 0 | $0 | $0 |
| 7-12 | 2 | 30,000 | 1 | 0 | $900 | $900 |
| 13-18 | 3 | 50,000 | 2 | 0 | $1,500 | $2,400 |
| **السنة 1** | - | - | - | - | **$10,000** | - |
| **السنة 2** | 5 | 100,000 | 4 | 1 | $22,000 | $32,000 |

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 10px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المقياس</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">القيمة</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">الاستثمار المطلوب</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$40,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي الإيراد (سنة 1)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$10,000</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي الإيراد (سنة 2)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$32,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">Break-even</td><td style="padding: 10px 14px; border: 1px solid #ddd;">**سنة 4-5**</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">ROI (سنة 2)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">**-35%**</td></tr>
  </tbody>
</table>
</div>

---

### السيناريو 2: محافظ (Conservative)

<div style="background-color: #fef3c7; border-right: 4px solid #f59e0b; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
<strong>الافتراضات:</strong>
- أول عميل بعد 3 أشهر
- churn 20%
- سعر API = $0.008 (average — Sudan market pricing)
- لا data licensing سنة 1
</div>

| الشهر | عملاء API |.calls | SaaS | Data | الإيراد | التراكم |
|---|---|---|---|---|---|---|
| 1-3 | 0 | 0 | 0 | 0 | $0 | $0 |
| 4-6 | 2 | 40,000 | 1 | 0 | $1,000 | $1,000 |
| 7-12 | 4 | 80,000 | 2 | 0 | $2,400 | $5,000 |
| 13-24 | 8 | 200,000 | 4 | 1 | $6,000 | $17,000 |
| **السنة 1** | - | - | - | - | **$15,000** | - |
| **السنة 2** | 12 | 400,000 | 8 | 2 | $45,000 | $62,000 |

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 10px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المقياس</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">القيمة</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">الاستثمار المطلوب</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$40,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي الإيراد (سنة 1)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$15,000</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي الإيراد (سنة 2)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$62,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">Break-even</td><td style="padding: 10px 14px; border: 1px solid #ddd;">**شهر 20-24**</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">ROI (سنة 2)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">**+55%**</td></tr>
  </tbody>
</table>
</div>

---

### السيناريو 3: واقعي (Realistic) — Recommended

<div style="background-color: #d1fae5; border-right: 4px solid #059669; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
<strong>الافتراضات:</strong>
- أول عميل بعد شهرين
- churn 15%
- سعر API = $0.008 (average — Sudan market pricing)
- أول data licensing بعد 6 أشهر
- 2 PoCs ناجحة = 5 عملاء جدد
</div>

| الشهر | عملاء API |.calls | SaaS | Data | الإيراد | التراكم |
|---|---|---|---|---|---|---|
| 1-2 | 0 | 0 | 0 | 0 | $0 | $0 |
| 3-6 | 3 | 60,000 | 2 | 0 | $1,200 | $1,200 |
| 7-12 | 6 | 150,000 | 4 | 1 | $4,200 | $9,500 |
| 13-18 | 10 | 280,000 | 6 | 2 | $9,500 | $28,000 |
| 19-24 | 15 | 450,000 | 10 | 3 | $18,000 | $65,000 |
| **السنة 1** | - | - | - | - | **$22,000** | - |
| **السنة 2** | 22 | 750,000 | 16 | 5 | $90,000 | $155,000 |

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 10px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المقياس</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">القيمة</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">الاستثمار المطلوب</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$40,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي الإيراد (سنة 1)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$22,000</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي الإيراد (سنة 2)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$155,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي الإيراد (سنة 3)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$250,000+</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">Break-even</td><td style="padding: 10px 14px; border: 1px solid #ddd;">**شهر 13-15**</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">ROI (سنة 2)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">**+287%**</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">ROI (سنة 3)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">**+525%**</td></tr>
  </tbody>
</table>
</div>

---

### السيناريو 4: متفائل (Optimistic)

<div style="background-color: #dbeafe; border-right: 4px solid #2563eb; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
<strong>الافتراضات:</strong>
- أول عميل خلال شهر
- churn 10% فقط
- شراكات كبيرة (MTN, UNCHR signed)
- regional expansion بعد شهر 12
- Data licensing = $15,000-$50,000/project
- سعر API = $0.008 average
</div>

| الشهر | عملاء API |.calls | SaaS | Data | الإيراد | التراكم |
|---|---|---|---|---|---|---|
| 1-3 | 4 | 100,000 | 3 | 1 | $3,200 | $3,200 |
| 4-6 | 7 | 200,000 | 5 | 2 | $6,500 | $9,700 |
| 7-12 | 12 | 400,000 | 8 | 3 | $15,000 | $36,000 |
| 13-18 | 18 | 650,000 | 12 | 5 | $28,000 | $90,000 |
| 19-24 | 25 | 1,000,000 | 18 | 8 | $50,000 | $180,000 |
| **السنة 1** | - | - | - | - | **$28,000** | - |
| **السنة 2** | 35+ | 1,500,000 | 25 | 12 | $140,000 | $230,000 |

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 10px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المقياس</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">القيمة</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">الاستثمار المطلوب</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$40,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي الإيراد (سنة 1)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$28,000</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي الإيراد (سنة 2)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$230,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إجمالي الإيراد (سنة 3)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$350,000+</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">Break-even</td><td style="padding: 10px 14px; border: 1px solid #ddd;">**شهر 8-10**</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">ROI (سنة 2)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">**+475%**</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">ROI (سنة 3)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">**+775%**</td></tr>
  </tbody>
</table>
</div>

---

## 3. مقارنة السيناريوهات

<div style="overflow-x: auto;">
<table style="width: 100%; border-collapse: collapse; text-align: right; margin-top: 20px;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المقياس</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">متشائم</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">محافظ</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">واقعي</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">متفائل</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">الاستثمار</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$40,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$40,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$40,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$40,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إيراد سنة 1</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$10,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$15,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$22,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$28,000</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إيراد سنة 2</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$32,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$62,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$155,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$230,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إيراد سنة 3</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$52,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$100,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$250,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$350,000</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">Break-even</td><td style="padding: 10px 14px; border: 1px solid #ddd;">شهر 48+</td><td style="padding: 10px 14px; border: 1px solid #ddd;">شهر 20-24</td><td style="padding: 10px 14px; border: 1px solid #ddd;">شهر 13-15</td><td style="padding: 10px 14px; border: 1px solid #ddd;">شهر 8-10</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">ROI (سنة 2)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">-20%</td><td style="padding: 10px 14px; border: 1px solid #ddd;">+55%</td><td style="padding: 10px 14px; border: 1px solid #ddd;">+287%</td><td style="padding: 10px 14px; border: 1px solid #ddd;">+475%</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">احتمال النجاح</td><td style="padding: 10px 14px; border: 1px solid #ddd;">15%</td><td style="padding: 10px 14px; border: 1px solid #ddd;">40%</td><td style="padding: 10px 14px; border: 1px solid #ddd;">35%</td><td style="padding: 10px 14px; border: 1px solid #ddd;">10%</td></tr>
  </tbody>
</table>
</div>

---

## 4. تحليل نقطة التعادل (Break-even Analysis)

### 4.1 التكاليف الشهرية الثابتة

| البند | التكلفة الشهرية |
|---|---|
| DigitalOcean (2GB RAM server) | $12 |
| Domain + SSL + emails | $17 |
| Maps API (Mapbox) | $20 |
| **الإجمالي** | **$49/شهر** |

<div style="background-color: #d1fae5; border-right: 4px solid #059669; padding: 10px; border-radius: 8px; margin-bottom: 10px; margin-top: 10px;">
<strong>ملاحظة:</strong> البنية التحتية الفعلية = $49/شهر = $588/سنة (مش $4,000). التوفير ($3,412) ينقل لـ marketing runway.
</div>

### 4.2 حجم الإيراد المطلوب (Break-even)

| سعر API (avg) | الإيراد المطلوب/شهر | عملاء (بمتوسط $300/client) |
|---|---|
| $0.006 | $49 | < 1 عميل (feasible!) |
| $0.008 | $49 | < 1 عميل |
| $0.01 | $49 | < 1 عميل |

**الاستنتاج:** بـ 2-3 عملاء نشطين (كل عميل ~$200-400/شهر)، النظام يغطي تكاليفه الثابتة ($49/شهر) + رواتب الفريق. نقطة التعادل واقعية جداً.

---

## 5. معايير النجاح (KPIs)

<div style="background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin-top: 20px;">
<table style="width: 100%; border-collapse: collapse;">
  <thead>
    <tr style="background-color: #1A284D; color: white;">
      <th style="padding: 10px 14px; border: 1px solid #ddd;">المقياس</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">هدف سنة 1</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">هدف سنة 2</th>
      <th style="padding: 10px 14px; border: 1px solid #ddd;">هدف سنة 3</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">عملاء API (B2B)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">5-8</td><td style="padding: 10px 14px; border: 1px solid #ddd;">15-20</td><td style="padding: 10px 14px; border: 1px solid #ddd;">30-50</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">إيراد شهري</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$2,500-$6,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$10,000-$20,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$25,000-$50,000</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">MRR (Monthly Recurring)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$1,500-$2,500</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$5,000-$10,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$12,000-$25,000</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">Churn Rate</td><td style="padding: 10px 14px; border: 1px solid #ddd;">< 20%</td><td style="padding: 10px 14px; border: 1px solid #ddd;">< 15%</td><td style="padding: 10px 14px; border: 1px solid #ddd;">< 10%</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">CAC (Customer Acquisition)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$500-$1,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$300-$600</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$200-$400</td></tr>
    <tr style="background-color: #f8f9fa;"><td style="padding: 10px 14px; border: 1px solid #ddd;">LTV (Lifetime Value)</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$5,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$15,000</td><td style="padding: 10px 14px; border: 1px solid #ddd;">$30,000+</td></tr>
    <tr style="background-color: #fff;"><td style="padding: 10px 14px; border: 1px solid #ddd;">LTV:CAC Ratio</td><td style="padding: 10px 14px; border: 1px solid #ddd;">> 5:1</td><td style="padding: 10px 14px; border: 1px solid #ddd;">> 10:1</td><td style="padding: 10px 14px; border: 1px solid #ddd;">> 15:1</td></tr>
  </tbody>
</table>
</div>

---

## 6. المخاطر والتحديات

| المخاطر | التأثير | الاحتمال | خطة التخفيف |
|---|---|---|---|
| تأخير أول عميل | 🔴🔴 | 30% | PoC مجاني + التركيز على 1 sector |
| Churn عالي | 🔴🔴🔴 | 25% | العقد السنوي + API lock-in |
| Competition (Google) | 🔴🔴 | 15% | focus على B2B + local data |
| Infrastructure costs | 🔴 | 20% | AWS reserved instances + optimization |
| Regulatory (بنوك) | 🔴🔴 | 35% | طويل الأمد — ليس year 1 focus |

---

## 7. الخلاصة

<div style="background-color: #1A284D; color: white; padding: 20px; border-radius: 8px; margin-top: 20px;">
<div style="font-size: 16px;">
<strong>التوصية:</strong> السيناريو الواقعي (Realistic) هو الأساس للتخطيط.
<br><br>
<strong>الاستثمار المطلوب:</strong> $40,000
<br><br>
<strong>العائد المتوقع (سنة 2):</strong> $155,000
<br><br>
<strong>ROI:</strong> +287%
<br><br>
<strong>Break-even:</strong> شهر 13-15
<br><br>
<strong>السؤال للمستثمر:</strong> هل تقبل السيناريو الواقعي؟ إذا الإجابة نعم، المشروع يستحق الاستثمار.
</div>
</div>