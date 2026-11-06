@extends('layouts.admin')
@section('title', 'تعديل معلم جغرافي')
@section('page_title', 'تعديل: '.($feature->properties['name'] ?? 'Unknown'))

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 350px; border-radius: 12px; border: 1px solid #E5E7EB; }
</style>
@endsection

@section('content')
<div style="max-width:900px; margin:0 auto;">
    <div class="card">
        <form action="{{ route('admin.spatial.update', $feature->id) }}" method="POST">
            @csrf
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
                <!-- Basic Info -->
                <div>
                    <div class="form-group">
                        <label>الاسم</label>
                        <input type="text" name="name" value="{{ old('name', $feature->properties['name'] ?? '') }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>الطبقة</label>
                        <input type="text" value="{{ $feature->mapLayer->name }}" class="form-control" style="background:#F3F4F6;color:#6B7280;" disabled>
                    </div>
                    <div class="form-group">
                        <label>الحالة</label>
                        <select name="status" class="form-control">
                            <option value="pending" {{ $feature->status == 'pending' ? 'selected' : '' }}>⏳ قيد التدقيق</option>
                            <option value="verified" {{ $feature->status == 'verified' ? 'selected' : '' }}>✅ موثق</option>
                            <option value="archived" {{ $feature->status == 'archived' ? 'selected' : '' }}>📦 مؤرشف</option>
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="text-xs text-gray">خط العرض</label>
                            <input type="text" name="latitude" id="lat" value="{{ $feature->latitude }}" class="form-control text-sm" readonly>
                        </div>
                        <div class="form-group">
                            <label class="text-xs text-gray">خط الطول</label>
                            <input type="text" name="longitude" id="lng" value="{{ $feature->longitude }}" class="form-control text-sm" readonly>
                        </div>
                    </div>
                </div>

                <!-- Map -->
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:#374151; margin-bottom:6px;">🗺️ تعديل الموقع (اسحب العلامة)</label>
                    <div id="map"></div>
                    <p class="text-xs text-gray" style="margin-top:6px;">حرّك العلامة على الخريطة لتحديث الإحداثيات</p>
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:24px; padding-top:20px; border-top:1px solid #E5E7EB;">
                <button type="submit" class="btn btn-primary">💾 حفظ التغييرات</button>
                <a href="{{ route('admin.spatial.index') }}" class="btn btn-outline">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var initialLat = {{ $feature->latitude }};
    var initialLng = {{ $feature->longitude }};
    var map = L.map('map').setView([initialLat, initialLng], 16);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CARTO'
    }).addTo(map);
    var marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
    marker.on('dragend', function() {
        var pos = marker.getLatLng();
        document.getElementById('lat').value = pos.lat.toFixed(7);
        document.getElementById('lng').value = pos.lng.toFixed(7);
    });
});
</script>
@endsection
