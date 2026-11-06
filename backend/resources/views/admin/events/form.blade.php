@extends('layouts.admin')

@section('title', $event ? 'تعديل فعالية' : 'فعالية جديدة')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 400px; border-radius: 12px; border: 2px solid #E5E7EB; margin-top: 8px; z-index: 1; }
    .scope-selector { transition: all 0.3s ease; }
    .hidden { display: none; }
</style>
@endsection

@section('content')
<div class="font-cairo rtl max-w-5xl mx-auto p-4">

    <div class="mb-6 text-right">
        <a href="{{ route('admin.events.index') }}" class="text-brand-blue hover:text-navy transition-colors text-sm flex items-center gap-2 justify-end">
            العودة لقائمة الفعاليات <span>→</span>
        </a>
    </div>

    <div class="card bg-white shadow-sm border border-gray-100 rounded-xl overflow-hidden text-right">
        <div class="bg-navy p-6 text-white">
            <h2 class="text-xl font-bold flex items-center gap-3 justify-end">
                @if($event)
                    <span>✏️</span> تعديل فعالية: {{ $event->title }}
                @else
                    <span>✨</span> إنشاء فعالية جديدة
                @endif
            </h2>
            <p class="text-xs text-gray-300 mt-2 opacity-80">حدد نطاق الفعالية وموقعها الجغرافي بدقة</p>
        </div>

        <form id="eventForm" method="POST" action="{{ $event ? route('admin.events.update', $event->id) : route('admin.events.store') }}" class="p-8">
            @csrf
            @if($event) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                
                {{-- Basic Info Section --}}
                <div class="md:col-span-2 border-b border-gray-100 pb-4">
                    <h3 class="font-bold text-navy mb-4">📝 المعلومات الأساسية</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 text-right">
                            <label class="block text-sm font-bold text-gray-700 mb-2">عنوان الفعالية <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required
                                class="form-control w-full p-3 border border-gray-300 rounded-lg text-right outline-none focus:border-brand-blue" placeholder="مثال: مسابقة تحدي النظافة">
                        </div>
                        <div class="text-right">
                            <label class="block text-sm font-bold text-gray-700 mb-2">نوع الفعالية <span class="text-red-500">*</span></label>
                            <select name="type" class="form-control w-full p-3 border border-gray-300 rounded-lg bg-white">
                                @foreach(['competition'=>'🏆 مسابقة','challenge'=>'⚡ تحدي','community'=>'🤝 مجتمعي','announcement'=>'📢 إعلان'] as $val=>$label)
                                <option value="{{ $val }}" @selected(old('type', $event->type ?? '') == $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-right">
                            <label class="block text-sm font-bold text-gray-700 mb-2">مكافأة النقاط</label>
                            <input type="number" name="reward_points" value="{{ old('reward_points', $event->reward_points ?? 0) }}" min="0"
                                class="form-control w-full p-3 border border-gray-300 rounded-lg">
                        </div>
                        <div class="text-right">
                            <label class="block text-sm font-bold text-gray-700 mb-2">التصنيف المستهدف (Layer)</label>
                            <select name="target_layer" class="form-control w-full p-3 border border-gray-300 rounded-lg bg-white">
                                <option value="" @selected(old('target_layer', $event->target_layer ?? '') == '')>كل التصنيفات (عام)</option>
                                @foreach($mapLayers as $layer)
                                <option value="{{ $layer->name }}" @selected(old('target_layer', $event->target_layer ?? '') == $layer->name)>{{ $layer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-right">
                            <label class="block text-sm font-bold text-gray-700 mb-2">النشاط المطلوب للتحدي (Action Type)</label>
                            <select name="target_action" class="form-control w-full p-3 border border-gray-300 rounded-lg bg-white">
                                <option value="" @selected(old('target_action', $event->target_action ?? '') == '')>كل الأنشطة والمساهمات</option>
                                <option value="add" @selected(old('target_action', $event->target_action ?? '') == 'add')>🆕 إضافة معالم جديدة (نقاط أو مضلعات)</option>
                                <option value="confirm" @selected(old('target_action', $event->target_action ?? '') == 'confirm')>📸 توثيق وتأكيد المعالم الحالية</option>
                                <option value="vote" @selected(old('target_action', $event->target_action ?? '') == 'vote')>⭐ تقييم ومراجعة المعالم</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Scope & Location Section --}}
                <div class="md:col-span-2">
                    <h3 class="font-bold text-navy mb-4">🌍 النطاق الجغرافي والموقع</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="text-right">
                            <label class="block text-sm font-bold text-gray-700 mb-2">نطاق الفعالية <span class="text-red-500">*</span></label>
                            <select id="scope_type" name="scope_type" class="form-control w-full p-3 border border-gray-300 rounded-lg bg-white">
                                <option value="national" @selected(old('scope_type', $event->scope_type ?? 'national') == 'national')>كل السودان</option>
                                <option value="state" @selected(old('scope_type', $event->scope_type ?? '') == 'state')>ولاية محددة</option>
                                <option value="locality" @selected(old('scope_type', $event->scope_type ?? '') == 'locality')>محلية محددة</option>
                                <option value="neighborhood" @selected(old('scope_type', $event->scope_type ?? '') == 'neighborhood')>حي أو مدينة محددة</option>
                            </select>
                        </div>

                        {{-- Dynamic Scope Selectors --}}
                        <div id="state_wrapper" class="text-right hidden">
                            <label class="block text-sm font-bold text-gray-700 mb-2">اختر الولاية</label>
                            <select id="state_select" class="scope_input form-control w-full p-3 border border-gray-300 rounded-lg bg-white">
                                <option value="">إختر...</option>
                                @foreach($states as $state)
                                <option value="{{ $state->id }}" @selected(($event->scope_type ?? '') == 'state' && $event->scope_id == $state->id)>{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="locality_wrapper" class="text-right hidden">
                            <label class="block text-sm font-bold text-gray-700 mb-2">اختر المحلية</label>
                            <select id="locality_select" class="scope_input form-control w-full p-3 border border-gray-300 rounded-lg bg-white">
                                <option value="">اختر الولاية أولاً...</option>
                            </select>
                        </div>

                        <div id="neighborhood_wrapper" class="text-right hidden">
                            <label class="block text-sm font-bold text-gray-700 mb-2">اختر الحي / المنطقة</label>
                            <select id="neighborhood_select" class="scope_input form-control w-full p-3 border border-gray-300 rounded-lg bg-white">
                                <option value="">اختر المحلية أولاً...</option>
                            </select>
                        </div>

                        {{-- Hidden ID input to be sent to server --}}
                        <input type="hidden" id="scope_id" name="scope_id" value="{{ old('scope_id', $event->scope_id ?? '') }}">
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2 justify-end">
                            📍 حدد موقع نقطة الفعالية على الخريطة
                        </label>
                        <p class="text-[10px] text-gray-500 mb-3 text-right">يمكنك النقر على الخريطة لتغيير مكان المؤشر. سيقوم النظام تلقائياً بتحديث الإحداثيات.</p>
                        
                        <div id="map"></div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="text-right">
                                <label class="block text-xs font-bold text-gray-600 mb-1">خط العرض (Lat)</label>
                                <input type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude', $event->latitude ?? '') }}" readonly
                                    class="w-full p-2 bg-gray-100 border border-gray-300 rounded text-center text-xs">
                            </div>
                            <div class="text-right">
                                <label class="block text-xs font-bold text-gray-600 mb-1">خط الطول (Lng)</label>
                                <input type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude', $event->longitude ?? '') }}" readonly
                                    class="w-full p-2 bg-gray-100 border border-gray-300 rounded text-center text-xs">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dates & Timing --}}
                <div class="text-right">
                    <label class="block text-sm font-bold text-gray-700 mb-2">تاريخ البداية <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($event->starts_at) ? \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d\TH:i') : '') }}" required
                        class="form-control w-full p-3 border border-gray-300 rounded-lg">
                </div>

                <div class="text-right">
                    <label class="block text-sm font-bold text-gray-700 mb-2">تاريخ الانتهاء</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($event->ends_at) ? \Carbon\Carbon::parse($event->ends_at)->format('Y-m-d\TH:i') : '') }}"
                        class="form-control w-full p-3 border border-gray-300 rounded-lg">
                </div>

                {{-- Description --}}
                <div class="md:col-span-2 text-right">
                    <label class="block text-sm font-bold text-gray-700 mb-2">وصف الفعالية <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="3" required
                        class="form-control w-full p-3 border border-gray-300 rounded-lg resize-none text-right">{{ old('description', $event->description ?? '') }}</textarea>
                </div>

                <div class="md:col-span-2 bg-brand-bg p-4 rounded-lg flex items-center justify-end gap-3">
                    <div class="text-right">
                        <span class="block text-sm font-bold text-navy">الفعالية نشطة</span>
                        <span class="text-[10px] text-gray-500">سيتم عرض هذه الفعالية فوراً في تطبيق المستخدمين</span>
                    </div>
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event->is_active ?? true)) 
                        class="w-6 h-6 rounded border-gray-300 text-brand-blue">
                </div>
            </div>

            <div class="flex gap-4 mt-8 pt-6 border-t border-gray-100 justify-end">
                <a href="{{ route('admin.events.index') }}" class="btn btn-outline px-8 py-3 bg-gray-50 text-gray-600 hover:bg-gray-100 border-none">
                    إلغاء
                </a>
                <button type="submit" class="btn btn-primary px-10 py-3 shadow-lg shadow-brand-blue/20">
                    💾 {{ $event ? 'حفظ التعديلات' : 'إطلاق الفعالية الآن' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const initialLat = {{ $event->latitude ?? 15.5007 }};
        const initialLng = {{ $event->longitude ?? 32.5599 }};
        
        // Setup Map
        const map = L.map('map').setView([initialLat, initialLng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([initialLat, initialLng], {draggable: true}).addTo(map);

        function updateCoords(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
        }

        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            updateCoords(pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateCoords(e.latlng.lat, e.latlng.lng);
        });

        // Scope Logic
        const scopeType = document.getElementById('scope_type');
        const scopeId = document.getElementById('scope_id');
        
        const wrappers = {
            state: document.getElementById('state_wrapper'),
            locality: document.getElementById('locality_wrapper'),
            neighborhood: document.getElementById('neighborhood_wrapper')
        };

        const selects = {
            state: document.getElementById('state_select'),
            locality: document.getElementById('locality_select'),
            neighborhood: document.getElementById('neighborhood_select')
        };

        function toggleScopeVisibility() {
            const type = scopeType.value;
            // Reset all
            Object.values(wrappers).forEach(w => w.classList.add('hidden'));
            
            if(type === 'state') wrappers.state.classList.remove('hidden');
            if(type === 'locality') { wrappers.state.classList.remove('hidden'); wrappers.locality.classList.remove('hidden'); }
            if(type === 'neighborhood') { wrappers.state.classList.remove('hidden'); wrappers.locality.classList.remove('hidden'); wrappers.neighborhood.classList.remove('hidden'); }
        }

        scopeType.addEventListener('change', () => {
            toggleScopeVisibility();
            if(scopeType.value === 'national') scopeId.value = '';
        });

        selects.state.addEventListener('change', async () => {
            const val = selects.state.value;
            if(!val) return;
            
            if(scopeType.value === 'state') scopeId.value = val;
            
            // Fetch Localities
            const res = await fetch(`{{ route('admin.events.features-by-layer') }}?layer=المحليات&parent_id=${val}`);
            const data = await res.json();
            
            selects.locality.innerHTML = '<option value="">إختر المحلية...</option>' + 
                data.map(f => `<option value="${f.id}" data-lat="${f.lat}" data-lng="${f.lng}">${f.name}</option>`).join('');
            
            // Center map on state
            const stateOpt = selects.state.options[selects.state.selectedIndex];
            // Since we don't have centroids for all states yet in the select, we skip zoom for now or use a default
        });

        selects.locality.addEventListener('change', async () => {
            const val = selects.locality.value;
            if(!val) return;

            if(scopeType.value === 'locality') scopeId.value = val;
            
            const opt = selects.locality.options[selects.locality.selectedIndex];
            if(opt.dataset.lat) {
                const lat = parseFloat(opt.dataset.lat);
                const lng = parseFloat(opt.dataset.lng);
                map.setView([lat, lng], 14);
                marker.setLatLng([lat, lng]);
                updateCoords(lat, lng);
            }

            // Fetch Neighborhoods
            const res = await fetch(`{{ route('admin.events.features-by-layer') }}?layer=الأحياء&parent_id=${val}`);
            const data = await res.json();
            
            selects.neighborhood.innerHTML = '<option value="">إختر الحي/المنطقة...</option>' + 
                data.map(f => `<option value="${f.id}" data-lat="${f.lat}" data-lng="${f.lng}">${f.name}</option>`).join('');
        });

        selects.neighborhood.addEventListener('change', () => {
            const val = selects.neighborhood.value;
            if(!val) return;
            
            if(scopeType.value === 'neighborhood') scopeId.value = val;

            const opt = selects.neighborhood.options[selects.neighborhood.selectedIndex];
            if(opt.dataset.lat) {
                const lat = parseFloat(opt.dataset.lat);
                const lng = parseFloat(opt.dataset.lng);
                map.setView([lat, lng], 16);
                marker.setLatLng([lat, lng]);
                updateCoords(lat, lng);
            }
        });

        // Initialize visibility on load
        toggleScopeVisibility();
    });
</script>
@endsection
