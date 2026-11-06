# نعمرها صح (Namerha Sah) — ويكيبيديا الخرائط

![CI](https://github.com/abubkrmohmmed1/Na3merhaSah/actions/workflows/ci.yml/badge.svg)
![شعار نعمرها صح](logo.jpeg)

**"نعمرها صح"** هو منصة رقمية متكاملة لرفع كفاءة الاستجابة للمشاكل الخدمية والبنية التحتية. يربط النظام بين المواطنين (عبر تطبيق موبايل) والجهات التشغيلية والتنفيذية (عبر لوحة تحكم إدارية).

## ✨ المميزات الرئيسية
* **تطبيق المواطن (Flutter):** إمكانية رفع البلاغات، تحديد الموقع بدقة، ورفع الصور مع إمكانية العمل لاحقاً دون اتصال (Offline-first architecture).
* **لوحة التحكم المركزية (Laravel):** إدارة البلاغات، تحويل المهام، الاعتمادات المالية، ومراقبة مؤشرات الأداء (KPIs).
* **البنية المكانية (GIS):** نظام متقدم يعتمد على `S2 Geometry` و `PostGIS` للفهرسة الجغرافية السريعة.

---

## 🛠 التقنيات المستخدمة

| الطبقة | التقنية |
|--------|---------|
| الواجهة الخلفية | **Laravel 12**, PHP 8.2, Laravel Sanctum |
| الواجهة الأمامية | **Flutter** مع Dart ^3.10.1 |
| قاعدة البيانات | **PostgreSQL 16** + PostGIS 3.4 |
| الخرائط | `google_maps_flutter`, S2 Geometry |
| الحالة (State) | `flutter_bloc` ^9.1.0 |
| CI/CD | **GitHub Actions** (`.github/workflows/ci.yml`) |

---

## 🚀 تشغيل المشروع (Backend)

1. **تثبيت الحزم:**
   ```bash
   composer install
   npm install
   ```

2. **إعداد قاعدة البيانات:**
   قم بنسخ ملف البيئة وضبط بيانات قاعدة البيانات PostgreSQL:
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   ```

3. **تشغيل السيرفر:**
   ```bash
   php artisan serve
   ```

---

## 📚 توثيق واجهات برمجة التطبيقات (API Documentation)
تم توثيق كافة الـ APIs باستخدام **Swagger (OpenAPI)** لتسهيل عملية التكامل والربط للجهات والمطورين.

بعد تشغيل السيرفر، يمكنك الوصول لصفحة التوثيق التفاعلية عبر الرابط:
**`http://localhost:8000/api/documentation`**

*ملاحظة: يمكنك إعادة توليد التوثيق بعد أي تعديل عبر الأمر:*
```bash
php artisan l5-swagger:generate
```

---

## 👥 فريق العمل
تم تطوير هذا النظام بواسطة **فريق البنية التحتية 2**.
