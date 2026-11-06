# دليل تكامل API نعمرها صح — حسب القطاع
**النسخة:** 2.0 | **تاريخ:** 14 مايو 2026

---

## كيفية الربط — 3 مستويات من الخدمة

| المستوى | لمن؟ | شنو يسوون؟ | الوقت | الجهد |
|---------|------|------------|-------|-------|
| 🅰️ | **API** | شركات عندها مطوّرين | المطوّر يكتب كود API | ساعة | متوسط |
| 🅱️ | **Widget** | شركات عندها موقع إلكتروني بس ما عندهم مطوّر | ينسخ `<script>` ويحطه | 5 دقائق | سهل جداً |
| 🅲 | **خدمات مخصصة** | جهات ما عندها مطوّر ولا منصة — أو تريد تقارير جاهزة | **نحن نشتغل نيابة عنهم** | يوم-أسبوع | بدون مجهود من العميل |

> **الفكرة:** كل جهة تقدر تشتغل معانا — من شركة اتصالات كبرى (API) إلى غرفة طوارئ مجتمعية ما عندها موقع (خدمات مخصصة). نغطي السوق كله.

### 🅲 الخدمات المخصصة تشمل:

| الخدمة | الوصف | مثال |
|--------|-------|------|
| **تقرير موقع** 📋 | نرسل تقرير PDF/Excel عن موقع أو منطقة | تقرير حي لمكتب عقار |
| **معالجة بيانات** 📦 | نستلم ملف (Excel, CSV) ونرجعه بعناوين + إحداثيات | 10,000 عميل تأمين |
| **تصدير مخصص** 🗺️ | نجهّز GeoJSON/SHP/KML حسب الطلب | حدود الأحياء لنظام GIS |
| **خريطة باسمك** 🏷️ | خريطة تفاعلية تحمل علامة الجهة التجارية | خريطة للفندق أو البنك |
| **استشارة** 💡 | نحلل المنطقة ونقدم توصيات | أفضل موقع للوحة إعلانية |
| **تطوير مخصص** 🔧 | نبني واجهة/تطبيق بسيط باستخدام API | بوابة طوارئ، منظومة تسجيل مزارعين |

---

## محتويات الدليل

## محتويات الدليل

1. 🏠 [العقارات](#1-العقارات)
2. 🛡️ [التأمين](#2-التأمين)
3. 📢 [الإعلانات](#3-الإعلانات)
4. 🌾 [الزراعة](#4-الزراعة)
5. 🏨 [السياحة](#5-السياحة)
6. 📡 [الاتصالات](#6-الاتصالات)
7. 🚑 [الطوارئ](#7-الطوارئ)

---

# 1. 🏠 العقارات

## شنو يحتاج القطاع؟
ربط إعلانات العقارات ببطاقة رقمية: الموقع + الحي + الخدمات القريبة.

---

## 🅰️ مع مطوّر — API مباشر

**المطوّر يكتب كود:** API واحد يجيب كل حاجة عن العقار.

```
طلب:     GET /real-estate/property-card?lat=15.5871&lng=32.5232
النتيجة: الحي + المحلية + الولاية + S2 Token + Plus Code + 7 خدمات قريبة
```

### خطوات التكامل من منظور المطوّر:

**الخطوة 1 — يسجّل في `/company/dashboard` وياخذ API Key**
```
Key: nrh_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**الخطوة 2 — يجرب الـ API**
```bash
curl.exe -H "X-API-Key: nrh_xxx" \
  "http://localhost:8000/api/v1/b2b/real-estate/property-card?lat=15.5871&lng=32.5232"
```

**الخطوة 3 — يربطه بصفحة تفاصيل العقار**

**PHP (Laravel/WordPress — الأكثر استخداماً في السودان):**
```php
// في صفحة تفاصيل العقار (property/show.php)
$card = Http::withHeaders([
    'X-API-Key' => 'nrh_xxx'
])->get('https://api.namerha.sd/api/v1/b2b/real-estate/property-card', [
    'lat' => $property->lat,
    'lng' => $property->lng
])->json();
```

```html
<!-- بعد كدا يحط البيانات في HTML -->
<div class="property-card">
  <p>📍 الحي: <?= $card['property']['neighborhood'] ?></p>
  <p>🏛️ <?= $card['property']['locality'] ?></p>
  <p>🔑 Plus Code: <?= $card['property']['plus_code'] ?></p>
  
  <h4>الخدمات ضمن 500 متر:</h4>
  <ul>
    <li>🏫 <?= $card['nearby_services']['schools'] ?> مدارس</li>
    <li>🍔 <?= $card['nearby_services']['restaurants'] ?> مطاعم</li>
    <li>🏥 <?= $card['nearby_services']['pharmacies'] ?> صيدليات</li>
  </ul>
</div>
```

**JavaScript (أي موقع إلكتروني — Shopify, Wix, HTML عادي):**
```javascript
// في صفحة تفاصيل العقار
fetch('https://api.namerha.sd/api/v1/b2b/real-estate/property-card?lat=15.5871&lng=32.5232', {
    headers: { 'X-API-Key': 'nrh_xxx' }
})
.then(r => r.json())
.then(card => {
    document.getElementById('property-card').innerHTML = `
        <p>📍 الحي: ${card.property.neighborhood}</p>
        <p>🏛️ ${card.property.locality}</p>
        <p>🔑 Plus Code: ${card.property.plus_code}</p>
        <p>🏫 ${card.nearby_services.schools} مدارس حول العقار</p>
        <p>🍔 ${card.nearby_services.restaurants} مطاعم قريبة</p>
    `;
});
```

**Python (لمنصة عقارات بايثون — Django, Flask):**
```python
import requests

def get_property_card(lat, lng):
    r = requests.get(
        'https://api.namerha.sd/api/v1/b2b/real-estate/property-card',
        params={'lat': lat, 'lng': lng},
        headers={'X-API-Key': 'nrh_xxx'}
    )
    return r.json()
```

---

## 🅱️ بدون مطوّر — Widget جاهز

**ما عندك مطوّر؟** انسخ الكود دا وحطّه في موقعك الإلكتروني.

```html
<!-- 1. حط div في المكان اللي تظهر فيه الخريطة -->
<div id="namerha-widget" style="height: 400px; border-radius: 12px;"></div>

<!-- 2. حط السكريبت دا في آخر الصفحة -->
<script src="https://api.namerha.sd/company/widget/autocomplete.js"></script>
<script>
NamerhaAddressWidget({
    apiKey: 'nrh_xxx',           // المفتاح بتاعك
    container: 'namerha-widget',  // الـ div اللي حطيته فو抜
    placeholder: 'ابحث عن عقار...',
    onSelect: function(address) {
        // كلم المطور بعد كدا يعمل التكامل الكامل
        alert('تم اختيار: ' + address.address_str);
    }
});
</script>
```

**إيه اللي يظهر؟** حقل بحث + خريطة تفاعلية. الزبون يكتب اسم المنطقة، تظهر النتائج، يختار، ويجيب بطاقة العقار.

**الوقت:** 5 دقائق — افتح الموقع، حط الكود، اشتغل.

### منصات بتدعم Widget بدون برمجة:
| المنصة | الطريقة |
|--------|---------|
| **WordPress** | قمّة (Custom HTML) block وانسخ الكود |
| **Wix** | HTML iframe embed |
| **Shopify** | Custom Liquid section |
| **أي موقع عادي** | أي صفحة HTML |

---

## 🅲 خدمات مخصصة للعقارات (بدون تقنية)

**ما عندك موقع إلكتروني ولا مطوّر؟** نقدم لك:

| الخدمة | شنو نسوي | السعر |
|--------|---------|-------|
| 📋 **تقرير عقار** | نبعت لك PDF لكل عقار: الحي + الخدمات القريبة + Plus Code + خريطة | $2/عقار |
| 📦 **معالجة قائمة عقارات** | استلم ملف Excel بعناوين، نرجعه بإحداثيات + أحياء + تقارير | $100/دفعة (حتى 100 عقار) |
| 🗺️ **خريطة مكاتبك** | نصنع خريطة تفاعلية بفروع مكتبكم وتحطها في موقعكم (حتى لو ما عندكم مطوّر) | $300/مرة |
| 🏷️ **بطاقات عقار رقمية** | نصنع صفحة ويب لكل عقار (رابط مخصص) تقدّمها للزبون | $1/عقار/شهر |

**كيف تشتغل؟** تواصل معانا واتساب/إيميل، نشتغل نيابة عنك، نرسلك النتيجة.

---

## المقارنة: 3 مستويات

| معيار | 🅰️ API مع مطوّر | 🅱️ Widget بدون مطوّر | 🅲 خدمات مخصصة |
|-------|-----------------|---------------------|---------------|
| الوقت | ساعة | 5 دقائق | يوم-أسبوع |
| مناسب لـ | منصات عقارات كبيرة | مكاتب متوسطة | مكاتب صغيرة، أفراد |
| التحكم | كامل | محدود | أنت تطلب ونحن ننفّذ |
| التكلفة | $100/شهر | $100/شهر | حسب الخدمة |

---

# 2. 🛡️ التأمين

## شنو يحتاج القطاع؟
التحقق من عنوان العميل + تقييم المخاطر حسب الموقع.

---

## 🅰️ مع مطوّر — API مباشر

**API 1 — التحقق من العنوان (للمطالبات):**
```
GET /insurance/verify-address?lat=&lng=&claimed_address=
```

**API 2 — تقييم المخاطر (لتسعير البوليصة):**
```
GET /insurance/risk-profile?lat=&lng=
```

### PHP (تكامل مع نظام إدارة المطالبات)
```php
// عند استلام مطالبة جديدة
$claim = Claim::find($id);

$verification = Http::withHeaders([
    'X-API-Key' => 'nrh_xxx'
])->get('https://api.namerha.sd/api/v1/b2b/insurance/verify-address', [
    'lat' => $claim->lat,
    'lng' => $claim->lng,
    'claimed_address' => $claim->address
])->json();

if (!$verification['address_match']) {
    // احتمالية احتيال — رفع للتحقيق
    $claim->flagForReview($verification['suggested_address']);
}

// تسعير المخاطر
$risk = Http::withHeaders([
    'X-API-Key' => 'nrh_xxx'
])->get('https://api.namerha.sd/api/v1/b2b/insurance/risk-profile', [
    'lat' => $applicant->lat,
    'lng' => $applicant->lng
])->json();

// المنطقة اللي فيها مستشفيات قريبة = خطر أقل
if ($risk['hospitals_within_5km'] > 3) {
    $premium *= 0.9; // خصم 10%
}
```

### JavaScript (نظام إدارة ويب)
```javascript
// زر "تحقق من العنوان" في صفحة المطالبة
async function verifyAddress(claimId) {
    const claim = await getClaim(claimId);
    
    const r = await fetch(
        `https://api.namerha.sd/api/v1/b2b/insurance/verify-address?lat=${claim.lat}&lng=${claim.lng}&claimed_address=${claim.address}`,
        { headers: { 'X-API-Key': 'nrh_xxx' } }
    );
    const result = await r.json();
    
    if (result.confidence_score > 80) {
        showSuccess('✅ عنوان موثوق');
    } else {
        showWarning('⚠️ عنوان يحتاج تدقيق');
    }
}
```

### Python (معالجة دفعة — 500 مطالبة في مرة)
```python
import requests, csv

# قراءة المطالبات من CSV
with open('claims.csv') as f:
    claims = list(csv.DictReader(f))

# إرسال دفعة واحدة
r = requests.post(
    'https://api.namerha.sd/api/v1/b2b/batch/geocode',
    json={'points': [{'lat': float(c['lat']), 'lng': float(c['lng'])} for c in claims]},
    headers={'X-API-Key': 'nrh_xxx'}
)

results = r.json()['results']
for res in results:
    print(f"مطالبة {res['index']}: {res['neighborhood']} — {res['locality']}")
```

---

## 🅱️ بدون مطوّر — رابط طوارئ + CSV

**ما عندك مطوّر؟** استخدم:

### 1. رابط تحديد الموقع
ابعت للعميل: `https://api.namerha.sd/locate`

العميل يفتح الرابط ← يضغط على الخريطة ← يجيب عنوانه. المندوب يستقبل الموقع في Dashboard.

### 2. معالجة CSV يدوية
```
1. جهز ملف Excel فيه عمودين: lat, lng
2. ارفعه في `/company/batch-upload`
3. النظام يرجع ملف جديد فيه: الحي + المحلية + الولاية
4. نزّل الملف وشتغل عليه
```

---

## 🅲 خدمات مخصصة للتأمين

| الخدمة | شنو نسوي | السعر |
|--------|---------|-------|
| 📋 **تقرير تحقيق لمطالبة** | نستلم إحداثيات + عنوان مدّعى، نرسل تقرير: "هذا العنوان حقيقي ولا لأ" | $5/تقرير |
| 📦 **معالجة دفعة مطالبات** | استلم ملف Excel (10,000 عميل)، نرجعه بـ: الحي الفعلي + درجة الثقة + تحليل احتيال | $500/دفعة |
| 📊 **خريطة المخاطر** | نصنع خريطة حرارية للمناطق حسب كثافة المخاطر (تساعد في تسعير البوليصات) | $1,000/تقرير |
| 💡 **استشارة تسعير** | نحلل توزيع عملاءك الحاليين ونقدم توصيات لتسعير حسب المنطقة | $2,000 |

---

# 3. 📢 الإعلانات

## شنو يحتاج القطاع؟
حدود الأحياء الدقيقة + إحصائيات كل حي لتخطيط الحملات.

---

## 🅰️ مع مطوّر — API مباشر

**API 1 — تصدير كل الأحياء (GeoJSON):**
```
GET /export?layer=الأحياء  ← يرجع ملف يفتح في QGIS/ArcGIS
```

**API 2 — إحصائيات حي معين:**
```
GET /advertising/neighborhood-stats?neighborhood_id=X
```

### PHP (لوحة تحليل إعلانات)
```php
// جلب إحصائيات كل الأحياء
$neighborhoods = Http::withHeaders([
    'X-API-Key' => 'nrh_xxx'
])->get('https://api.namerha.sd/api/v1/b2b/export?layer=الأحياء')->json();

// لكل حي، جلب الإحصائيات
foreach ($neighborhoods['features'] as $feature) {
    $stats = Http::withHeaders([
        'X-API-Key' => 'nrh_xxx'
    ])->get('https://api.namerha.sd/api/v1/b2b/advertising/neighborhood-stats', [
        'neighborhood_id' => $feature['properties']['id']
    ])->json();
    
    $report[] = [
        'name' => $feature['properties']['name'],
        'total_services' => $stats['total_features'],
        'top_services' => array_slice($stats['services'], 0, 3),
    ];
}
```

### JavaScript (خريطة تفاعلية للإعلانات)
```javascript
// تحميل حدود الأحياء وعرضها على خريطة
fetch('https://api.namerha.sd/api/v1/b2b/export?layer=الأحياء', {
    headers: { 'X-API-Key': 'nrh_xxx' }
})
.then(r => r.json())
.then(data => {
    L.geoJSON(data, {
        style: { color: '#4B7DF3', weight: 1, fillOpacity: 0.1 },
        onEachFeature: (feature, layer) => {
            layer.on('click', async () => {
                const stats = await fetch(
                    `https://api.namerha.sd/api/v1/b2b/advertising/neighborhood-stats?neighborhood_id=${feature.properties.id}`,
                    { headers: { 'X-API-Key': 'nrh_xxx' } }
                ).then(r => r.json());
                
                showStatsPopup(feature.properties.name, stats);
            });
        }
    }).addTo(map);
});
```

---

## 🅱️ بدون مطوّر — تحميل GeoJSON + Excel

**ما عندك مطوّر؟** الطريقة يدوية:

1. **ادخل `/company/dashboard`**
2. **اضغط "تصدير الأحياء"** ← ينزّل ملف `.geojson`
3. **افتح الملف في QGIS** (برنامج مجاني للخرائط)
4. **اختار الحي بالماوس** ← يشوف الإحصائيات

أو أسهل:
1. اطلب من فريق نعمرها صح **"تقرير حي"**
2. نرسل لك PDF يحتوي: خريطة الحي + إحصائيات الخدمات + تحليل النشاط التجاري
3. استخدمه في عرضك للعميل

---

## 🅲 خدمات مخصصة للإعلانات

| الخدمة | شنو نسوي | السعر |
|--------|---------|-------|
| 📊 **تقرير اختيار موقع لوحة** | نحدد أفضل 3 أحياء للوحة إعلانية حسب كثافة الخدمات والحركة التجارية | $500/تقرير |
| 🗺️ **خريطة الحرارة التجارية** | خريطة ملونة تظهر المناطق الأكثر نشاطاً تجارياً في الخرطوم كلها | $2,000/مرة |
| 📋 **قائمة الأحياء المصنفة** | Excel فيه كل الأحياء مرتبة حسب النشاط التجاري (للتخطيط الإعلاني) | $300 |
| 🏷️ **تقرير حي مخصص** | تحليل كامل لأي حي تختاره: كم مطعم، بنك، صيدلية، كثافة تقديرية | $200/حي |

---

# 4. 🌾 الزراعة

## شنو يحتاج القطاع؟
توصيل المساعدات بدقة + تسجيل المزارعين.

---

## 🅰️ مع مطوّر — API مباشر

**API — Batch Geocode:**
```
POST /batch/geocode
Body: {"points": [{"lat": 14.39, "lng": 33.48}, ...]}
Result: الحي + المحلية + الولاية لكل نقطة
```

### PHP (تطبيق توزيع مساعدات)
```php
// المندوب يضغط على الخريطة في الموبايل
$batch = Http::withHeaders([
    'X-API-Key' => 'nrh_xxx'
])->post('https://api.namerha.sd/api/v1/b2b/batch/geocode', [
    'points' => $deliveryPoints // array of {lat, lng}
])->json();

// لكل نقطة توصيل، يظهر الحي والمحلية للمندوب
foreach ($batch['results'] as $point) {
    echo "وصل إلى: {$point['neighborhood']} - {$point['locality']}";
}
```

### JavaScript (تسجيل مزارع — PWA للريف)
```javascript
// المزارع يضغط على موقع أرضه في الخريطة
map.on('click', async function(e) {
    const r = await fetch(
        `https://api.namerha.sd/api/v1/b2b/addresses/reverse?lat=${e.latlng.lat}&lng=${e.latlng.lng}`,
        { headers: { 'X-API-Key': 'nrh_xxx' } }
    );
    const address = await r.json();
    
    // يسجل المزارع مع عنوانه الدقيق
    await registerFarmer({
        name: farmerName,
        lat: e.latlng.lat,
        lng: e.latlng.lng,
        village: address.data?.neighborhood || 'قرية غير مسجلة'
    });
});
```

---

## 🅱️ بدون مطوّر — CSV + رابط

1. **جهّز ملف Excel** فيه أسماء القرى
2. **ارسله لـ فريق نعمرها صح** (WhatsApp/Email)
3. **نرجعلك الملف** مع إحداثيات كل قرية + المحلية + الولاية

أو:
- ابعت رابط `go.namerha.sd/farm` للمزارعين
- المزارع يضغط على الأرض ← يسجل نفسه
- أنت تستقبل المزارعين المسجلين في Dashboard

---

## 🅲 خدمات مخصصة للزراعة

| الخدمة | شنو نسوي | السعر |
|--------|---------|-------|
| 📦 **معالجة قرى** | استلم Excel بأسماء القرى، نرجعه بإحداثيات دقيقة لكل قرية | $300/دفعة |
| 🗺️ **خريطة توزيع** | نصنع خريطة توضح توزيع القرى المستهدفة للمساعدات | $500/خريطة |
| 📋 **تقرير منطقة** | تحليل كامل لمنطقة زراعية: القرى المحيطة، المسافات، الخدمات | $400/تقرير |
| 🔧 **نظام تسجيل مزارعين** | نبني لك واجهة بسيطة (رابط) المزارع يسجل فيها نفسه + أرضه | $1,000/مرة |

---

# 5. 🏨 السياحة

## شنو يحتاج القطاع؟
إظهار الخدمات القريبة من الفندق للسائح.

---

## 🅰️ مع مطوّر — API مباشر

**API — بطاقة موقع (لأي إحداثيات):**
```
GET /real-estate/property-card?lat=&lng=
```

**API — خدمات قريبة:**
```
GET /spatial/radius?lat=&lng=&radius=500&limit=20
```

### PHP (صفحة الفندق)
```php
// في صفحة الفندق
$location = Http::withHeaders([
    'X-API-Key' => 'nrh_xxx'
])->get('https://api.namerha.sd/api/v1/b2b/real-estate/property-card', [
    'lat' => $hotel->lat,
    'lng' => $hotel->lng,
])->json();

$nearby = Http::withHeaders([
    'X-API-Key' => 'nrh_xxx'
])->get('https://api.namerha.sd/api/v1/b2b/spatial/radius', [
    'lat' => $hotel->lat,
    'lng' => $hotel->lng,
    'radius' => 500,
    'limit' => 10,
])->json();
```

### JavaScript (Widget الفندق)
```javascript
// <script> يتحط في صفحة الفندق
fetch('https://api.namerha.sd/api/v1/b2b/real-estate/property-card?lat=15.5871&lng=32.5232', {
    headers: { 'X-API-Key': 'nrh_xxx' }
})
.then(r => r.json())
.then(card => {
    document.getElementById('hotel-info').innerHTML = `
        <div style="background:#f0f9ff;padding:16px;border-radius:12px;border:1px solid #bae6fd;">
            <h3>📍 ${card.property.neighborhood}</h3>
            <p>${card.property.locality}, ${card.property.state}</p>
            <hr>
            <p>⬅️ ضمن 500 متر:</p>
            <p>🍔 ${card.nearby_services.restaurants} مطعم | 🏫 ${card.nearby_services.schools} مدارس | 🏥 ${card.nearby_services.pharmacies} صيدليات</p>
        </div>
    `;
});
```

---

## 🅱️ بدون مطوّر — Widget جاهز

انسخ دا وحطه في صفحة الفندق (WordPress, Wix, أي حاجة):

```html
<div id="hotel-widget" style="background:#f8f9fa;padding:20px;border-radius:12px;font-family:sans-serif;direction:rtl;">
    <div id="hotel-data">جارٍ تحميل معلومات الموقع...</div>
</div>

<script>
fetch('https://api.namerha.sd/api/v1/b2b/real-estate/property-card?lat=15.5871&lng=32.5232', {
    headers: { 'X-API-Key': 'nrh_xxx' }
})
.then(r => r.json())
.then(card => {
    document.getElementById('hotel-data').innerHTML = `
        <h3>📍 ${card.property.neighborhood}</h3>
        <p>⬅️ ضمن 500 متر: ${card.nearby_services.restaurants} مطاعم | ${card.nearby_services.pharmacies} صيدليات</p>
        <p style="color:#6b7280;font-size:12px;">🔑 ${card.property.plus_code}</p>
    `;
});
</script>
```

**الوقت:** دقيقتين — نسخ ولصق.

---

## 🅲 خدمات مخصصة للسياحة

| الخدمة | شنو نسوي | السعر |
|--------|---------|-------|
| 🏷️ **صفحة موقع الفندق** | نصنع صفحة ويب بسيطة للفندق تظهر الموقع + الخدمات القريبة + خريطة | $200/مرة |
| 📋 **تقرير جاذبية موقع** | نحلل المنطقة حول الفندق ونقدم تقرير تسويقي: عدد المطاعم، المعالم، الأسواق | $150/تقرير |
| 🗺️ **خريطة سياحية** | نصنع خريطة سياحية للمنطقة (فنادق، مطاعم، متاحف) | $500/خريطة |

---

# 6. 📡 الاتصالات

## شنو يحتاج القطاع؟
حدود إدارية دقيقة لتحليل التغطية وتخطيط الأبراج.

---

## 🅰️ مع مطوّر — API مباشر

**API — تصدير 3 مستويات:**
```
GET /export?layer=الولايات    → حدود 19 ولاية
GET /export?layer=المحليات   → حدود 193 محلية
GET /export?layer=الأحياء    → حدود 2,112 حي
```

### Python (تحليل فجوات التغطية)
```python
import geopandas as gpd
import requests

headers = {'X-API-Key': 'nrh_xxx'}

# تحميل حدود المحليات
r = requests.get(
    'https://api.namerha.sd/api/v1/b2b/export?layer=المحليات',
    headers=headers
)
localities = gpd.GeoDataFrame.from_features(r.json())

# تحميل مواقع الأبراج الحالية (من قاعدة بيانات الشركة)
towers = gpd.read_file('existing_towers.shp')

# تحليل: أي محلية ما فيها برج؟
for _, locality in localities.iterrows():
    towers_in = towers[towers.within(locality.geometry)]
    if len(towers_in) == 0:
        print(f"🚨 فجوة تغطية: {locality['name']} — لا يوجد برج")
```

### JavaScript (خريطة تخطيط الأبراج)
```javascript
// تحميل حدود المحليات على خريطة Leaflet
fetch('https://api.namerha.sd/api/v1/b2b/export?layer=المحليات', {
    headers: { 'X-API-Key': 'nrh_xxx' }
})
.then(r => r.json())
.then(data => {
    L.geoJSON(data, {
        style: function(feature) {
            return {
                color: '#06B6D4',
                weight: 2,
                fillOpacity: 0.05
            };
        },
        onEachFeature: function(feature, layer) {
            layer.bindPopup(`<b>${feature.properties.name}</b>`);
        }
    }).addTo(map);
});
```

---

## 🅱️ بدون مطوّر — تحميل GeoJSON + QGIS

1. **ادخل `/company/dashboard`**
2. **حمّل 3 ملفات:** الولايات + المحليات + الأحياء
3. **افتحها في QGIS** (تنزيل مجاني من qgis.org)
4. **ركّب طبقة الأبراج الحالية** فوقها
5. **شوف الفجوات بالعين** — الأماكن اللي ما فيها أبراج

**بديل:** اطلب من فريقنا **"تقرير تغطية"** — نرسل لك خريطة كاملة مع تحليل الفجوات.

---

## 🅲 خدمات مخصصة للاتصالات

| الخدمة | شنو نسوي | السعر |
|--------|---------|-------|
| 📊 **تقرير تغطية** | نحلل منطقتك ونقدم تقرير: أين الفجوات؟ وين الأفضل تركيب الأبراج؟ | $5,000/تقرير |
| 🗺️ **خرائط مخصصة** | نجهّز لك كل الحدود الإدارية (ولاية/محلية/حي) بالتنسيق اللي تريده (GeoJSON, SHP, KML, PDF) | $3,000/مرة |
| 💡 **استشارة تخطيط** | فريقنا يحلل بياناتك الحالية (مواقع الأبراج) ويقدم خطة توسع مبنية على بيانات دقيقة | $8,000 |

---

# 7. 🚑 الطوارئ

## شنو يحتاج القطاع؟
تحديد موقع المبلغ بدقة + أقرب موارد الطوارئ.

---

## 🅰️ مع مطوّر — API مباشر

**API — تحديد موقع:**
```
GET /addresses/reverse?lat=&lng=
```

**API — أقرب موارد:**
```
GET /insurance/risk-profile?lat=&lng=
```

### JavaScript (رابط طوارئ — يفتح في المتصفح)
```javascript
// صفحة HTML بسيطة: go.namerha.sd/loc
function shareLocation() {
    navigator.geolocation.getCurrentPosition(async (pos) => {
        const { latitude: lat, longitude: lng } = pos.coords;
        
        // 1. تحديد العنوان
        const addr = await fetch(
            `https://api.namerha.sd/api/v1/b2b/addresses/reverse?lat=${lat}&lng=${lng}`,
            { headers: { 'X-API-Key': 'nrh_xxx' } }
        ).then(r => r.json());
        
        // 2. أقرب مستشفى
        const risk = await fetch(
            `https://api.namerha.sd/api/v1/b2b/insurance/risk-profile?lat=${lat}&lng=${lng}`,
            { headers: { 'X-API-Key': 'nrh_xxx' } }
        ).then(r => r.json());
        
        // 3. عرض النتيجة
        document.getElementById('result').innerHTML = `
            📍 الموقع: ${addr.data?.neighborhood || 'غير معروف'}
            🚑 أقرب مستشفى: ${risk.nearest_hospital.name} (${risk.nearest_hospital.distance_meters}م)
            🚓 أقرب شرطة: ${risk.nearest_police_station.name} (${risk.nearest_police_station.distance_meters}م)
        `;
        
        // 4. إرسال للإسعاف
        sendToDispatch({ lat, lng, address: addr, risk });
    });
}
```

### PHP (نظام إدارة غرفة الطوارئ)
```php
// استلام بلاغ من واتساب
$message = "فيضان في شارع النيل";

// رد تلقائي برابط تحديد الموقع
sendWhatsApp($phone, "📍 أرسل موقعك: https://go.namerha.sd/loc");

// بعد ما يضغط المبلغ على الرابط
$location = Http::withHeaders([
    'X-API-Key' => 'nrh_xxx'
])->get('https://api.namerha.sd/api/v1/b2b/addresses/reverse', [
    'lat' => $incoming->lat,
    'lng' => $incoming->lng,
])->json();

$risk = Http::withHeaders([
    'X-API-Key' => 'nrh_xxx'
])->get('https://api.namerha.sd/api/v1/b2b/insurance/risk-profile', [
    'lat' => $incoming->lat,
    'lng' => $incoming->lng,
])->json();

// توجيه أقرب سيارة إسعاف
dispatchNearestAmbulance($risk['nearest_hospital']['name']);
```

---

## 🅱️ بدون مطوّر — رابط جاهز

**ما عندك مطوّر؟** استخدم الرابط الجاهز:

```
https://go.namerha.sd/loc
```

1. ابعت الرابط للمبلغ عبر واتساب
2. المبلغ يفتح الرابط ← يضغط على "أرسل موقعي"
3. أنت تستقبل الموقع في Dashboard الطوارئ

**بديل:** اتصل بنا نرسل لك **"بوابة طوارئ"** جاهزة — فقط حط رابط واتساب واشتغل.

---

## 🅲 خدمات مخصصة للطوارئ

| الخدمة | شنو نسوي | السعر |
|--------|---------|-------|
| 🔗 **رابط طوارئ مخصص** | نصنع رابط خاص بغرفتكم (go.namerha.sd/your-org) يفتح خريطة ويرسل الموقع تلقائياً | مجاني للجهات الرسمية |
| 📋 **تقرير استجابة** | نحلل أوقات الاستجابة حسب المناطق ونقدم توصيات لتحسين التغطية | $500/تقرير |
| 🗺️ **خريطة غرف الطوارئ** | نصنع خريطة بمواقع كل غرف الطوارئ في المنطقة مع بيانات الاتصال | $300/خريطة |

---

# جدول المسارات — كل قطاع (3 مستويات)

| القطاع | 🅰️ API (مطوّر) | 🅱️ Widget (بدون مطوّر) | 🅲 خدمات مخصصة (بدون منصة) |
|--------|----------------|------------------------|--------------------------|
| 🏠 العقارات | `property-card` + PHP/JS | `<script>` في 5 دقائق | PDF عقار، Excel قائمة، خريطة مكاتب |
| 🛡️ التأمين | `verify-address` + `risk-profile` | رابط تحديد موقع | CSV مطالبات، خريطة مخاطر |
| 📢 الإعلانات | `export` + `neighborhood-stats` | تحميل GeoJSON يدوي | تقرير موقع لوحة، خريطة حرارة |
| 🌾 الزراعة | `batch/geocode` + `export` | CSV يدوي + رابط | معالجة قرى، نظام تسجيل مزارعين |
| 🏨 السياحة | `property-card` + `radius` | `<script>` في دقيقتين | صفحة فندق، تقرير جاذبية |
| 📡 الاتصالات | `export` (3 levels) | تحميل يدوي | تقرير تغطية، خرائط مخصصة |
| 🚑 الطوارئ | `reverse` + `risk-profile` | رابط go.namerha.sd/loc | رابط مخصص، تقرير استجابة |

---

## الخلاصة — من نستهدف؟

| المستوى | من؟ | كيف نربح؟ |
|---------|-----|-----------|
| 🅰️ **API** | شركات عندها فريق تقني (اتصالات، بنوك، منصات عقارات) | اشتراك شهري ($500-$50k) |
| 🅱️ **Widget** | شركات عندها موقع إلكتروني (مكاتب عقارات، فنادق) | اشتراك شهري ($100-$200) |
| 🅲 **خدمات مخصصة** | أي جهة ما عندها تقنية — وكل القطاعات (الكل محتاج) | رسوم خدمة ($150-$8,000) |

> **الجميل:** الخدمات المخصصة تفتح لنا سوق **الجهات الصغيرة** (مكاتب عقار بـ 5 موظفين، غرف طوارئ مجتمعية، جمعيات زراعية) — اللي ما عندهم ميزانية لتوظيف مطوّرين. هذا سوق أكبر من APIs و Widgets مجتمعين.

**ابدأ:** جرّب API أو Widget في `/company/login`، أو تواصل معانا لخدمة مخصصة.
