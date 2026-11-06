@extends('layouts.admin')
@section('title', 'إدارة المعالم الجغرافية')
@section('page_title', 'المخزون الجغرافي — إدارة المعالم')

@section('styles')
<style>
    #reviewMap { height: 400px; width: 100%; border-radius: 8px; margin-bottom: 16px; background: #f0f0f0; }
    .icon-btn-blue { background: #DBEAFE; color: #1E40AF; }
    .icon-btn-blue:hover { background: #BFDBFE; }
</style>
@endsection

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <form action="{{ route('admin.spatial.index') }}" method="GET">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; align-items:end;">
            <div class="form-group" style="margin:0;">
                <label>🔍 بحث (الاسم أو Plus Code)</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="ابحث هنا...">
            </div>
            <div class="form-group" style="margin:0;">
                <label>📦 التصنيف</label>
                <select name="layer_id" class="form-control" onchange="this.form.submit()">
                    <option value="">كل الخدمات</option>
                    @foreach($serviceLayers as $layer)
                        <option value="{{ $layer->id }}" {{ request('layer_id') == $layer->id ? 'selected' : '' }}>{{ $layer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label>📌 الحالة</label>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">كل الحالات</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ قيد التدقيق</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>✅ موثق</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>📦 مؤرشف</option>
                </select>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary" style="flex:1;">تصفية</button>
                <a href="{{ route('admin.spatial.index') }}" class="btn btn-outline" style="flex:1;text-align:center;">إعادة ضبط</a>
            </div>
        </div>

        <!-- Hierarchy Filters -->
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:16px; margin-top:16px; padding-top:16px; border-top:1px solid #E5E7EB;">
            <div class="form-group" style="margin:0;">
                <label class="text-xs" style="color:#9CA3AF;">1. الولاية</label>
                <select name="state_id" class="form-control" onchange="this.form.submit()">
                    <option value="">كل الولايات</option>
                    @foreach($states as $state)
                        <option value="{{ $state->id }}" {{ request('state_id') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="text-xs" style="color:#9CA3AF;">2. المحلية</label>
                <select name="locality_id" class="form-control" onchange="this.form.submit()" {{ $localities->isEmpty() ? 'disabled' : '' }}>
                    <option value="">كل المحليات</option>
                    @foreach($localities as $loc)
                        <option value="{{ $loc->id }}" {{ request('locality_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="text-xs" style="color:#9CA3AF;">3. الحي</label>
                <select name="neighbourhood_id" class="form-control" onchange="this.form.submit()" {{ $neighbourhoods->isEmpty() ? 'disabled' : '' }}>
                    <option value="">كل الأحياء</option>
                    @foreach($neighbourhoods as $hood)
                        <option value="{{ $hood->id }}" {{ request('neighbourhood_id') == $hood->id ? 'selected' : '' }}>{{ $hood->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="margin-top:12px;">
            <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="unknown_only" value="1" {{ request('unknown_only') ? 'checked' : '' }} onchange="this.form.submit()" style="width:16px;height:16px;">
                <span style="font-size:13px;font-weight:600;color:#6B7280;">🔍 عرض الأسماء المجهولة فقط (تحتاج تدقيق)</span>
            </label>
        </div>
    </form>
</div>

<!-- Features Table -->
<div class="card">
    <div class="flex-between mb-4">
        <div class="card-header" style="margin:0;">📍 المعالم ({{ $features->total() }})</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>الاسم</th><th>الطبقة</th><th>الموقع</th><th>الثقة & التحقق</th><th>الحالة</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($features as $feature)
                <tr>
                    <td>
                        <div class="flex-column">
                            <span class="fw-700 {{ in_array(($feature->properties['name'] ?? ''), ['Unknown', 'مجهول', '']) ? 'text-red-500' : '' }}" style="color:{{ in_array(($feature->properties['name'] ?? ''), ['Unknown', 'مجهول', '']) ? '#EF4444' : 'inherit' }};">
                                {{ $feature->properties['name'] ?? 'غير محدد' }}
                            </span>
                            @if(isset($feature->properties['source']))
                                <span class="text-xs text-gray-500">المصدر: {{ $feature->properties['source'] }}</span>
                            @endif
                        </div>
                    </td>
                    <td><span class="badge badge-blue">{{ $feature->mapLayer->name }}</span></td>
                    <td class="text-xs" style="direction:ltr;font-family:monospace;">
                        @php
                            $coords = DB::selectOne("SELECT ST_X(ST_Centroid(geom)) as lng, ST_Y(ST_Centroid(geom)) as lat FROM geo_features WHERE id = ?", [$feature->id]);
                        @endphp
                        @if($coords)
                            {{ number_format($coords->lat, 4) }}, {{ number_format($coords->lng, 4) }}
                        @else
                            {{ number_format($feature->latitude, 4) }}, {{ number_format($feature->longitude, 4) }}
                        @endif
                        @if($feature->plus_code)
                            <br><span style="color:#4B7DF3;">{{ $feature->plus_code }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex-column" style="gap:4px;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span class="badge {{ $feature->confidence_score >= 80 ? 'badge-green' : ($feature->confidence_score >= 50 ? 'badge-yellow' : 'badge-red') }}" style="font-size:10px;">
                                    {{ $feature->confidence_score }}% ثقة
                                </span>
                                @if($feature->properties['external_verified_osm'] ?? false)
                                    <span title="موثق من OSM دولياً" style="font-size:14px;">🌍</span>
                                @endif
                            </div>
                            @if($feature->verification_level)
                                <span class="text-xs font-semibold" style="color:#6B7280;">مستوى: {{ $feature->verification_level }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($feature->status == 'verified')
                            <span class="badge badge-green">موثق</span>
                        @elseif($feature->status == 'pending')
                            <span class="badge badge-yellow">قيد التدقيق</span>
                        @else
                            <span class="badge" style="background:#F3F4F6;color:#6B7280;">مؤرشف</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-group">
                            @php
                                $satelliteUrl = isset($feature->properties['satellite_image']) ? asset('storage/' . $feature->properties['satellite_image']) : null;
                            @endphp
                            <button type="button" 
                                onclick="openReviewMap({{ $coords ? $coords->lat : ($feature->latitude ?? 15.5) }}, {{ $coords ? $coords->lng : ($feature->longitude ?? 32.5) }}, '{{ addslashes($feature->properties['name'] ?? 'غير محدد') }}', '{{ $satelliteUrl }}')" 
                                class="icon-btn {{ $satelliteUrl ? 'icon-btn-green' : 'icon-btn-blue' }}" 
                                title="مراجعة (قمر صناعي + خريطة)">
                                {{ $satelliteUrl ? '🛰️' : '🔍' }}
                            </button>
                            @if($feature->status !== 'verified')
                            <form action="{{ route('admin.spatial.verify', $feature->id) }}" method="POST">@csrf
                                <button class="icon-btn icon-btn-green" title="توثيق">✓</button>
                            </form>
                            @endif
                            <a href="{{ route('admin.spatial.edit', $feature->id) }}" class="icon-btn" style="color:#2563EB;background:#EFF6FF;" title="تعديل">✎</a>
                            <form action="{{ route('admin.spatial.destroy', $feature->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">@csrf @method('DELETE')
                                <button class="icon-btn icon-btn-red" title="حذف">✕</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="empty-state">لا توجد معالم تطابق بحثك</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($features, 'links'))
    <div style="padding:16px 0 0; border-top:1px solid #F3F4F6;">
        {{ $features->links() }}
    </div>
    @endif
</div>

<!-- Review Map Modal -->
<div id="reviewMapModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 900px; width: 95%;">
        <div class="flex-between mb-4">
            <h3 style="margin:0;">مراجعة الموقع: <span id="reviewItemName" style="color: #4B7DF3;"></span></h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="closeReviewMap()">إغلاق</button>
        </div>
        
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:16px;">
            <div class="flex-column">
                <label class="text-xs font-bold mb-2">🛰️ لقطة القمر الصناعي (لحظة الرفع):</label>
                <div id="satellitePreview" style="width:100%; height:400px; border-radius:8px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                    <img id="satelliteImg" src="" style="width:100%; height:100%; object-fit:cover; display:none;">
                    <span id="noSatelliteText" class="text-gray-400 text-xs">لا توجد صورة قمر صناعي حالياً</span>
                </div>
            </div>
            <div class="flex-column">
                <label class="text-xs font-bold mb-2">🗺️ الخريطة التفاعلية (Google Hybrid):</label>
                <div id="reviewMap" style="height:400px;"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}"></script>
<script>
let reviewMapInstance = null;
let reviewMarker = null;

function openReviewMap(lat, lng, name, satelliteUrl) {
    document.getElementById('reviewItemName').textContent = name;
    
    // Handle Satellite Image
    const satelliteImg = document.getElementById('satelliteImg');
    const noSatelliteText = document.getElementById('noSatelliteText');
    if (satelliteUrl && satelliteUrl !== '') {
        satelliteImg.src = satelliteUrl;
        satelliteImg.style.display = 'block';
        noSatelliteText.style.display = 'none';
    } else {
        satelliteImg.style.display = 'none';
        noSatelliteText.style.display = 'block';
    }

    document.getElementById('reviewMapModal').classList.add('show');
    
    setTimeout(() => {
        const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
        
        if (!reviewMapInstance) {
            reviewMapInstance = new google.maps.Map(document.getElementById("reviewMap"), {
                center: position,
                zoom: 18,
                mapTypeId: 'hybrid',
                tilt: 45
            });
        } else {
            reviewMapInstance.setCenter(position);
            reviewMapInstance.setZoom(18);
        }

        if (reviewMarker) {
            reviewMarker.setPosition(position);
        } else {
            reviewMarker = new google.maps.Marker({
                position: position,
                map: reviewMapInstance,
                animation: google.maps.Animation.DROP
            });
        }
        
        google.maps.event.trigger(reviewMapInstance, 'resize');
    }, 200);
}

function closeReviewMap() {
    document.getElementById('reviewMapModal').classList.remove('show');
}
</script>
@endsection
