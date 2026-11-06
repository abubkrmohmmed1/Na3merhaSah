@extends('layouts.admin')

@section('title', 'مراجعة التقديمات')
@section('page_title', 'مراجعة التقديمات')

@section('styles')
<style>
    #reviewMap { height: 400px; width: 100%; border-radius: 8px; margin-bottom: 16px; background: #f0f0f0; }
    .icon-btn-blue { background: #DBEAFE; color: #1E40AF; }
    .icon-btn-blue:hover { background: #BFDBFE; }
</style>
@endsection

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div class="icon-wrap" style="background:#DBEAFE;"><span>📋</span></div>
        <div class="num" style="color:#1E40AF;">{{ $totalPending }}</div>
        <div class="lbl">الإجمالي بانتظار المراجعة</div>
    </div>
    <a href="{{ route('admin.submissions.index', ['tab' => 'addresses']) }}" class="stat-card" style="text-decoration:none;display:block;">
        <div class="icon-wrap" style="background:#D1FAE5;"><span>🏠</span></div>
        <div class="num" style="color:#065F46;">{{ $pendingAddresses->count() }}</div>
        <div class="lbl">عناوين جديدة</div>
    </a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'neighborhoods']) }}" class="stat-card" style="text-decoration:none;display:block;">
        <div class="icon-wrap" style="background:#EDE9FE;"><span>🏘️</span></div>
        <div class="num" style="color:#6D28D9;">{{ $pendingNeighborhoods->count() }}</div>
        <div class="lbl">أحياء مقترحة</div>
    </a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'villages']) }}" class="stat-card" style="text-decoration:none;display:block;">
        <div class="icon-wrap" style="background:#FCE7F3;"><span>🌾</span></div>
        <div class="num" style="color:#BE185D;">{{ $pendingVillages->count() }}</div>
        <div class="lbl">قرى مقترحة</div>
    </a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'mobile']) }}" class="stat-card" style="text-decoration:none;display:block;">
        <div class="icon-wrap" style="background:#FEF3C7;"><span>📱</span></div>
        <div class="num" style="color:#92400E;">{{ $mobileSubmissions->count() }}</div>
        <div class="lbl">من التطبيق</div>
    </a>
</div>

<div class="tabs">
    <a href="{{ route('admin.submissions.index', ['tab' => 'addresses']) }}" class="{{ $tab === 'addresses' ? 'active' : '' }}">🏠 عناوين ({{ $pendingAddresses->count() }})</a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'neighborhoods']) }}" class="{{ $tab === 'neighborhoods' ? 'active' : '' }}">🏘️ أحياء ({{ $pendingNeighborhoods->count() }})</a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'villages']) }}" class="{{ $tab === 'villages' ? 'active' : '' }}">🌾 قرى ({{ $pendingVillages->count() }})</a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'mobile']) }}" class="{{ $tab === 'mobile' ? 'active' : '' }}">📱 تطبيق ({{ $mobileSubmissions->count() }})</a>
</div>

@if($tab === 'addresses')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>المستخدم</th><th>الاسم</th><th>العنوان</th><th>النوع</th><th>الموقع</th><th>التاريخ</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingAddresses as $sub)
                <tr>
                    <td>
                        <div class="fw-700 text-sm">{{ $sub->user->name ?? 'مستخدم' }}</div>
                        <div class="text-xs text-gray">{{ $sub->user->phone ?? '' }}</div>
                    </td>
                    <td class="fw-700">{{ $sub->name }}</td>
                    <td class="text-sm text-gray">{{ $sub->address_str }}</td>
                    <td><span class="badge {{ $sub->type === 'personal' ? 'badge-blue' : 'badge-yellow' }}">{{ $sub->type === 'personal' ? 'شخصي' : 'خدمة' }}</span></td>
                    <td class="text-xs text-gray" style="direction:ltr;">{{ $sub->lat }}, {{ $sub->lng }}</td>
                    <td class="text-xs text-gray">{{ $sub->created_at->diffForHumans() }}</td>
                    <td>
                        <div class="action-group">
                            <button type="button" onclick="openReviewMap({{ $sub->lat ?? 15.5 }}, {{ $sub->lng ?? 32.5 }}, '{{ addslashes($sub->name) }}')" class="icon-btn icon-btn-blue" title="مراجعة على الخريطة">🔍</button>
                            <form action="{{ route('admin.submissions.address.approve', $sub->id) }}" method="POST">@csrf
                                <button class="icon-btn icon-btn-green" title="موافقة">✓</button>
                            </form>
                            <button onclick="openRejectModal('address', '{{ $sub->id }}', '{{ addslashes($sub->name) }}')" class="icon-btn icon-btn-red" title="رفض">✕</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty-state">لا توجد عناوين بانتظار المراجعة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@elseif($tab === 'neighborhoods')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>المستخدم</th><th>الاسم</th><th>الوصف</th><th>الموقع</th><th>التاريخ</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($pendingNeighborhoods as $prop)
                <tr>
                    <td>
                        <div class="fw-700 text-sm">{{ $prop->user->name ?? 'مستخدم' }}</div>
                        <div class="text-xs text-gray">{{ $prop->user->phone ?? '' }}</div>
                    </td>
                    <td class="fw-700">{{ $prop->name }}</td>
                    <td class="text-sm text-gray">{{ Str::limit($prop->description ?? '—', 50) }}</td>
                    <td class="text-xs text-gray" style="direction:ltr;">{{ $prop->lat }}, {{ $prop->lng }}</td>
                    <td class="text-xs text-gray">{{ $prop->created_at->diffForHumans() }}</td>
                    <td>
                        <div class="action-group">
                            <button type="button" onclick="openReviewMap({{ $prop->lat ?? 15.5 }}, {{ $prop->lng ?? 32.5 }}, '{{ addslashes($prop->name) }}')" class="icon-btn icon-btn-blue" title="مراجعة على الخريطة">🔍</button>
                            <form action="{{ route('admin.submissions.proposal.approve', $prop->id) }}" method="POST">@csrf
                                <button class="icon-btn icon-btn-green" title="موافقة">✓</button>
                            </form>
                            <button onclick="openRejectModal('proposal', '{{ $prop->id }}', '{{ addslashes($prop->name) }}')" class="icon-btn icon-btn-red" title="رفض">✕</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty-state">لا توجد أحياء بانتظار المراجعة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@elseif($tab === 'villages')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>المستخدم</th><th>الاسم</th><th>المحلية</th><th>التاريخ</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($pendingVillages as $prop)
                <tr>
                    <td>
                        <div class="fw-700 text-sm">{{ $prop->user->name ?? 'مستخدم' }}</div>
                        <div class="text-xs text-gray">{{ $prop->user->phone ?? '' }}</div>
                    </td>
                    <td class="fw-700">{{ $prop->name }}</td>
                    <td class="text-sm text-gray">{{ $prop->locality_name ?? '—' }}</td>
                    <td class="text-xs text-gray">{{ $prop->created_at->diffForHumans() }}</td>
                    <td>
                        <div class="action-group">
                            <button type="button" onclick="openReviewMap({{ $prop->lat ?? 15.5 }}, {{ $prop->lng ?? 32.5 }}, '{{ addslashes($prop->name) }}')" class="icon-btn icon-btn-blue" title="مراجعة على الخريطة">🔍</button>
                            <form action="{{ route('admin.submissions.proposal.approve', $prop->id) }}" method="POST">@csrf
                                <button class="icon-btn icon-btn-green" title="موافقة">✓</button>
                            </form>
                            <button onclick="openRejectModal('proposal', '{{ $prop->id }}', '{{ addslashes($prop->name) }}')" class="icon-btn icon-btn-red" title="رفض">✕</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="empty-state">لا توجد قرى بانتظار المراجعة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@elseif($tab === 'mobile')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>الاسم</th><th>التصنيف</th><th>الإحداثيات</th><th>المصدر</th><th>التاريخ</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($mobileSubmissions as $feature)
                <tr>
                    <td class="fw-700">{{ $feature->properties['name'] ?? '—' }}</td>
                    <td><span class="badge badge-amber">{{ $feature->mapLayer->name ?? '—' }}</span></td>
                    <td class="text-xs text-gray" style="direction:ltr;">
                        @php $coords = DB::selectOne("SELECT ST_X(ST_Centroid(geom)) as lng, ST_Y(ST_Centroid(geom)) as lat FROM geo_features WHERE id = ?", [$feature->id]); @endphp
                        {{ $coords ? number_format($coords->lat, 4).', '.number_format($coords->lng, 4) : '—' }}
                    </td>
                    <td class="text-xs text-gray">تطبيق</td>
                    <td class="text-xs text-gray">{{ $feature->created_at->diffForHumans() }}</td>
                    <td>
                        <div class="action-group">
                            <button type="button" onclick="openReviewMap({{ $coords->lat ?? 15.5 }}, {{ $coords->lng ?? 32.5 }}, '{{ addslashes($feature->properties['name'] ?? '') }}')" class="icon-btn icon-btn-blue" title="مراجعة على الخريطة">🔍</button>
                            <form action="{{ route('admin.submissions.mobile.approve', $feature->id) }}" method="POST">@csrf
                                <button class="icon-btn icon-btn-green" title="موافقة">✓</button>
                            </form>
                            <button onclick="openRejectModal('mobile', '{{ $feature->id }}', '{{ addslashes($feature->properties['name'] ?? '') }}')" class="icon-btn icon-btn-red" title="رفض">✕</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty-state">لا توجد تقديمات من التطبيق</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Reject Modal -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal-box">
        <h3>رفض: <span id="rejectItemName"></span></h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="form-group">
                <label>سبب الرفض</label>
                <textarea id="rejectReason" name="reason" class="form-control" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-danger">رفض</button>
                <button type="button" class="btn btn-outline" onclick="closeRejectModal()">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<!-- Review Map Modal -->
<div id="reviewMapModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 600px; width: 90%;">
        <h3>مراجعة الموقع: <span id="reviewItemName" style="color: #4F46E5;"></span></h3>
        <div id="reviewMap"></div>
        <div class="modal-actions" style="margin-top: 16px;">
            <button type="button" class="btn btn-outline" onclick="closeReviewMap()">إغلاق الخريطة</button>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}"></script>
<script>
function openRejectModal(type, id, name) {
    document.getElementById('rejectItemName').textContent = name;
    const form = document.getElementById('rejectForm');
    const routes = { address: '/admin/submissions/address/', proposal: '/admin/submissions/proposal/', mobile: '/admin/submissions/mobile/' };
    form.action = (routes[type] || routes.proposal) + id + '/reject';
    document.getElementById('rejectModal').classList.add('show');
}
function closeRejectModal() { document.getElementById('rejectModal').classList.remove('show'); }

let reviewMapInstance = null;
let reviewMarker = null;

function openReviewMap(lat, lng, name) {
    document.getElementById('reviewItemName').textContent = name;
    document.getElementById('reviewMapModal').classList.add('show');
    
    setTimeout(() => {
        const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
        
        if (!reviewMapInstance) {
            reviewMapInstance = new google.maps.Map(document.getElementById("reviewMap"), {
                center: position,
                zoom: 15,
                mapTypeId: 'hybrid', // This shows satellite with street names
                mapTypeControl: true,
                streetViewControl: false,
                fullscreenControl: true
            });
        } else {
            reviewMapInstance.setCenter(position);
        }

        if (reviewMarker) {
            reviewMarker.setPosition(position);
            reviewMarker.setTitle(name);
        } else {
            reviewMarker = new google.maps.Marker({
                position: position,
                map: reviewMapInstance,
                title: name,
                animation: google.maps.Animation.DROP
            });
        }
        
        // Trigger a resize event to ensure the map renders correctly in the modal
        google.maps.event.trigger(reviewMapInstance, 'resize');
        reviewMapInstance.setCenter(position);
        
    }, 200);
}

function closeReviewMap() {
    document.getElementById('reviewMapModal').classList.remove('show');
}
</script>
@endsection
