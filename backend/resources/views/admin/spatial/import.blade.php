@extends('layouts.admin')
@section('title', 'استيراد')
@section('page_title', '📥 استيراد نقاط من CSV')

@section('content')
<div class="card" style="max-width: 700px;">
    <form action="{{ route('admin.spatial.import.csv') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>📄 ملف CSV</label>
            <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
            <p style="font-size:11px;color:#6B7280;margin-top:4px;">
                الملف يجب أن يحتوي على أعمدة: <code>name</code>, <code>lat</code>, <code>lng</code>
                (اختياري: <code>layer</code>, <code>description</code>)
            </p>
        </div>

        <div class="form-group">
            <label>طبقة افتراضية (اختياري — تستخدم لكل النقاط لو ما في عمود layer)</label>
            <select name="layer" class="form-control">
                <option value="">استخدم عمود layer من الملف</option>
                @foreach($layers as $l)
                    <option value="{{ $l->name }}">{{ $l->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="create_layer" value="1">
                <span style="font-size:13px;">إنشاء الطبقة تلقائياً لو مش موجودة</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary">📥 استيراد</button>
        <a href="{{ route('admin.spatial.index') }}" class="btn btn-outline" style="margin-right:8px;">إلغاء</a>
    </form>

    <hr style="border:none;border-top:1px solid #E5E7EB;margin:24px 0;">

    <h4 style="margin-bottom:12px;">📋 نموذج ملف CSV</h4>
    <pre style="background:#F3F4F6;padding:12px;border-radius:8px;font-size:11px;direction:ltr;overflow-x:auto;">
name,lat,lng,layer,description
مطعم النيل,15.5871,32.5232,مطاعم,مطعم على النيل
صيدلية الشفاء,15.5900,32.5200,صيدليات,
مدرسة الأمل,15.5850,32.5250,مدارس,
بنك الخرطوم,15.5880,32.5220,بنوك,فرع رئيسي
    </pre>
</div>

<hr style="border:none;border-top:1px solid #E5E7EB;margin:24px 0;">

<!-- City Import Section -->
<div class="card" style="margin-top:24px;">
    <h3 style="margin:0 0 16px;">🏙️ استيراد مدينة كاملة من OpenStreetMap</h3>
    <p style="font-size:13px;color:#6B7280;margin-bottom:16px;">
        استورد جميع الخدمات والمعالم (مطاعم، صيدليات، مستشفيات، مدارس، بنوك، محطات وقود، فنادق، مساجد، وغيرها) لمدينة كاملة.
    </p>
    <form action="{{ route('admin.spatial.import.city') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>اسم المدينة</label>
            <input type="text" name="city" class="form-control" required placeholder="مثال: الخرطوم, ود مدني, القضارف, كسلا, بورتسودان">
        </div>
        <button type="submit" class="btn btn-primary">🏙️ استيراد المدينة</button>
    </form>
</div>

<!-- Google My Maps Import -->
<div class="card" style="margin-top:24px;">
    <h3 style="margin:0 0 16px;">🗺️ استيراد من Google My Maps (KML → CSV)</h3>
    <p style="font-size:13px;color:#6B7280;margin-bottom:12px;">
        1. افتح <a href="https://mymaps.google.com" target="_blank">Google My Maps</a><br>
        2. أنشئ خريطة جديدة ← أضف نقاطك<br>
        3. Export → KML/KMZ<br>
        4. استخدم أداة تحويل KML لـ CSV<br>
        5. ارفع الملف الناتج في القسم أعلاه
    </p>
</div>
@endsection

@section('scripts')
<style>
.card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
</style>
@endsection
