@extends('company.layout')
@section('title', $company->name)
@section('page_title', 'مرحباً '.$company->name)

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    <div class="card p-4 flex flex-col justify-center items-start border-l-4" style="border-left-color: #0078d4;">
        <div class="text-3xl font-semibold text-fluent-blue">{{ number_format($data['states'] ?? 0) }}</div>
        <div class="text-xs font-semibold text-fluent-textSecondary mt-1">ولاية</div>
    </div>
    <div class="card p-4 flex flex-col justify-center items-start border-l-4" style="border-left-color: #107c10;">
        <div class="text-3xl font-semibold" style="color:#107c10;">{{ number_format($data['localities'] ?? 0) }}</div>
        <div class="text-xs font-semibold text-fluent-textSecondary mt-1">محلية</div>
    </div>
    <div class="card p-4 flex flex-col justify-center items-start border-l-4" style="border-left-color: #d83b01;">
        <div class="text-3xl font-semibold" style="color:#d83b01;">{{ number_format($data['neighborhoods'] ?? 0) }}</div>
        <div class="text-xs font-semibold text-fluent-textSecondary mt-1">حي</div>
    </div>
    <div class="card p-4 flex flex-col justify-center items-start border-l-4" style="border-left-color: #5c2d91;">
        <div class="text-3xl font-semibold" style="color:#5c2d91;">{{ number_format($data['totalFeatures'] ?? 0) }}</div>
        <div class="text-xs font-semibold text-fluent-textSecondary mt-1">معلم جغرافي</div>
    </div>
    <div class="card p-4 flex flex-col justify-center items-start border-l-4" style="border-left-color: #a4262c;">
        <div class="text-3xl font-semibold" style="color:#a4262c;">{{ $data['activeKeys'] ?? 0 }}</div>
        <div class="text-xs font-semibold text-fluent-textSecondary mt-1">مفاتيح نشطة</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Layers -->
    <div class="card">
        <h3 class="text-base font-semibold text-fluent-text mb-4 flex items-center gap-2">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            أكبر 10 طبقات بيانات
        </h3>
        <div class="space-y-3">
            @foreach(collect($data['layers'] ?? [])->take(10) as $layer)
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-fluent-text w-32 truncate">{{ $layer->name }}</span>
                <div class="flex-1 bg-fluent-border rounded-sm h-1.5">
                    @php $max = collect($data['layers'])->max('feature_count'); @endphp
                    <div class="h-1.5 rounded-sm bg-fluent-blue" style="width:{{ $layer->feature_count / max($max,1) * 100 }}%;"></div>
                </div>
                <span class="text-xs font-semibold text-fluent-textSecondary w-16 text-left" style="direction: ltr;">{{ number_format($layer->feature_count) }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- API Keys -->
    <div class="card">
        <h3 class="text-base font-semibold text-fluent-text mb-4 flex items-center gap-2">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            مفاتيح API
        </h3>
        <div class="space-y-2">
            @forelse($apiKey->company->apiKeys as $k)
            <div class="flex items-center justify-between p-3 border border-fluent-border rounded-sm hover:bg-fluent-hover transition-colors">
                <div>
                    <span class="font-semibold text-sm text-fluent-text block">{{ $k->name }}</span>
                    <span class="text-xs text-fluent-textSecondary">{{ $k->last_used_at ? 'آخر استخدام: ' . $k->last_used_at->diffForHumans() : 'لم يستخدم بعد' }}</span>
                </div>
                @if($k->revoked_at)
                    <span class="px-2 py-0.5 rounded-sm text-xs font-semibold" style="background:#fde7e9; color:#a4262c;">ملغي</span>
                @else
                    <span class="px-2 py-0.5 rounded-sm text-xs font-semibold" style="background:#dff6dd; color:#107c10;">نشط</span>
                @endif
            </div>
            @empty
            <p class="text-center text-fluent-textSecondary py-4 text-sm">لا توجد مفاتيح</p>
            @endforelse
        </div>
    </div>
</div>

<!-- API Sandbox -->
<div class="card">
    <h3 class="text-base font-semibold text-fluent-text mb-4 flex items-center gap-2">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        صندوق اختبار الـ API
    </h3>
    <form onsubmit="event.preventDefault(); testApi();">
        <div class="flex gap-3 mb-4">
            <select id="ep" class="form-control" style="max-width: 400px; padding: 6px 12px;">
                <option value="/api/v1/b2b/spatial/states">الولايات (States)</option>
                <option value="/api/v1/b2b/spatial/localities">المحليات (Localities)</option>
                <option value="/api/v1/b2b/spatial/radius?lat=15.5871&lng=32.5232&radius=1000">بحث دائري ضمن 1 كم</option>
                <option value="/api/v1/b2b/addresses/reverse?lat=15.5871&lng=32.5232">ترميز جغرافي عكسي</option>
                <option value="/api/v1/b2b/export?layer=الأحياء">تصدير GeoJSON للأحياء</option>
            </select>
            <button type="submit" class="btn btn-primary">إرسال الطلب</button>
        </div>
        <pre id="res" class="p-4 rounded-sm text-xs overflow-x-auto min-h-[100px] font-mono" style="background: #1b1b1b; color: #d4d4d4; direction: ltr; border: 1px solid #323130;">انقر "إرسال الطلب" لعرض النتيجة الفورية...</pre>
    </form>
</div>

<script>
async function testApi() {
    const el = document.getElementById('res');
    el.textContent = 'جارٍ الاتصال بالخادم...';
    try {
        const r = await fetch(document.getElementById('ep').value, { headers: { 'X-API-Key': '{{ $apiKey->key }}' } });
        el.textContent = JSON.stringify(await r.json(), null, 2);
    } catch(e) { el.textContent = 'خطأ في الاتصال: ' + e.message; }
}
</script>
<!-- Recent Orders -->
<div class="card">
    <h3 class="text-base font-semibold text-fluent-text mb-4">طلبات التوثيق الأخيرة</h3>
    <div class="table-wrap">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-fluent-border">
                    <th class="py-2 text-right">رقم الطلب</th>
                    <th class="py-2 text-right">الحالة</th>
                    <th class="py-2 text-right">الإجمالي</th>
                    <th class="py-2 text-right">التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($apiKey->company->verificationOrders()->latest()->take(5)->get() as $order)
                <tr>
                    <td class="py-2">{{ $order->reference_no }}</td>
                    <td class="py-2">
                        <span class="badge {{ $order->status == 'completed' ? 'badge-green' : 'badge-yellow' }}">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td class="py-2">{{ number_format($order->total_price) }} {{ $order->currency }}</td>
                    <td class="py-2">{{ $order->created_at->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-4 text-fluent-textSecondary">لا توجد طلبات بعد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
