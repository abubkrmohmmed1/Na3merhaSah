@extends('company.layout')

@section('title', 'منشآتي')
@section('page_title', 'منشآتي (My Locations)')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Add Location Form -->
    <div class="card">
        <h3 class="text-base font-semibold text-fluent-text mb-4 border-b border-fluent-border pb-3">➕ إضافة موقع جديد</h3>
        <form action="{{ route('company.add-location') }}" method="POST" id="addLocationForm">
            @csrf
            <div class="form-group">
                <label>اسم المنشأة / الفرع</label>
                <input type="text" name="name" class="form-control" required placeholder="مثال: فرع الخرطوم الرئيسي">
            </div>
            <div class="form-group">
                <label>التصنيف</label>
                <select name="layer" class="form-control" required>
                    <option value="">-- اختر التصنيف --</option>
                    @foreach($layers as $l)
                        <option value="{{ $l->name }}">{{ $l->name }}</option>
                    @endforeach
                    <option value="فروع الشركات">فروع الشركات</option>
                    <option value="مستودعات">مستودعات</option>
                    <option value="مراكز توزيع">مراكز توزيع</option>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="form-group mb-0">
                    <label>خط العرض (Latitude)</label>
                    <input type="text" name="lat" id="lat" class="form-control bg-fluent-hover" readonly value="15.5871" style="direction:ltr;">
                </div>
                <div class="form-group mb-0">
                    <label>خط الطول (Longitude)</label>
                    <input type="text" name="lng" id="lng" class="form-control bg-fluent-hover" readonly value="32.5232" style="direction:ltr;">
                </div>
            </div>
            
            <div class="mb-5">
                <label class="block text-sm font-semibold text-fluent-text mb-2">تحديد الموقع على الخريطة</label>
                <div id="mini-map" style="height:220px; border:1px solid #8a8886; border-radius:2px;"></div>
                <p class="text-xs text-fluent-textSecondary mt-2">قم بسحب الدبوس أو النقر على الخريطة لتحديث الإحداثيات بدقة.</p>
            </div>
            
            <button type="submit" class="btn btn-primary w-full justify-center py-2">حفظ كموقع مؤكد</button>
        </form>
    </div>

    <!-- Existing Locations -->
    <div class="card">
        <div class="flex justify-between items-center mb-4 border-b border-fluent-border pb-3">
            <h3 class="text-base font-semibold text-fluent-text m-0">📍 المواقع المؤكدة ({{ $locations->count() }})</h3>
        </div>
        
        <div class="space-y-3">
        @forelse($locations as $loc)
            <div class="p-3 border border-fluent-border rounded-sm hover:bg-fluent-hover transition-colors flex justify-between items-center">
                <div>
                    <strong class="text-sm text-fluent-text">{{ $loc->properties['name'] ?? '' }}</strong>
                    <span class="text-xs text-fluent-textSecondary block mt-1">{{ $loc->mapLayer->name ?? '' }}</span>
                </div>
                <div class="flex items-center gap-1 bg-green-50 text-green-700 px-2 py-1 rounded-sm border border-green-200">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-xs font-semibold">موثّق</span>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-fluent-textSecondary">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="mx-auto mb-4 opacity-50"><path stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <p class="font-semibold mb-1 text-fluent-text">لم تقم بإضافة أي موقع بعد</p>
                <p class="text-xs">أضف موقع منشأتك ليظهر مباشرة كموقع مؤكد وموثق في المنصة.</p>
            </div>
        @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('mini-map').setView([15.5871, 32.5232], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map);

    setTimeout(function() {
        map.invalidateSize();
    }, 200);

    let marker = L.marker([15.5871, 32.5232], {draggable: true}).addTo(map);

    marker.on('dragend', function() {
        const pos = marker.getLatLng();
        document.getElementById('lat').value = pos.lat.toFixed(6);
        document.getElementById('lng').value = pos.lng.toFixed(6);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('lat').value = e.latlng.lat.toFixed(6);
        document.getElementById('lng').value = e.latlng.lng.toFixed(6);
    });

    map.on('moveend', function() {
        document.getElementById('lat').value = marker.getLatLng().lat.toFixed(6);
        document.getElementById('lng').value = marker.getLatLng().lng.toFixed(6);
    });
});
</script>
@endsection
