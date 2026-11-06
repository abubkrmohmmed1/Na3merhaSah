@extends('layouts.admin')
@section('title', 'مؤشرات الأداء')
@section('page_title', 'لوحة مؤشرات الأداء (KPIs)')

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div class="icon-wrap" style="background: #1A284D;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;"><span style="color:white;">🗺️</span></div>
        <div class="num" style="font-size:28px;font-weight:800;color:#1A284D;">{{ number_format($totalFeatures) }}</div>
        <div class="lbl" style="font-size:12px;color:#6B7280;margin-top:4px;font-weight:600;">إجمالي المعالم الجغرافية</div>
    </div>
    <div class="stat-card">
        <div class="icon-wrap" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;background:#D1FAE5;"><span>✅</span></div>
        <div class="num" style="font-size:28px;font-weight:800;color:#065F46;">{{ number_format($verifiedFeatures) }}</div>
        <div class="lbl" style="font-size:12px;color:#6B7280;margin-top:4px;font-weight:600;">موثقة</div>
    </div>
    <div class="stat-card">
        <div class="icon-wrap" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;background:#FEF3C7;"><span>⏳</span></div>
        <div class="num" style="font-size:28px;font-weight:800;color:#92400E;">{{ number_format($pendingFeatures) }}</div>
        <div class="lbl" style="font-size:12px;color:#6B7280;margin-top:4px;font-weight:600;">قيد التدقيق</div>
    </div>
    <div class="stat-card">
        <div class="icon-wrap" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;background:#FEE2E2;"><span>🔍</span></div>
        <div class="num" style="font-size:28px;font-weight:800;color:#991B1B;">{{ number_format($archivedFeatures) }}</div>
        <div class="lbl" style="font-size:12px;color:#6B7280;margin-top:4px;font-weight:600;">مؤرشف</div>
    </div>
    <div class="stat-card">
        <div class="icon-wrap" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;background:#EDE9FE;"><span>❓</span></div>
        <div class="num" style="font-size:28px;font-weight:800;color:#6D28D9;">{{ number_format($unknownCount) }}</div>
        <div class="lbl" style="font-size:12px;color:#6B7280;margin-top:4px;font-weight:600;">أسماء مجهولة</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-header" style="font-size:16px;font-weight:700;color:#1F2937;margin-bottom:16px;">📦 توزيع الطبقات</div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>الطبقة</th><th>العدد</th><th>الإجمالي</th></tr></thead>
                <tbody>
                    @php $maxLayer = $byLayer->first()?->geo_features_count ?? 1; @endphp
                    @foreach($byLayer as $layer)
                    <tr>
                        <td class="fw-700">{{ $layer->name }}</td>
                        <td>{{ number_format($layer->geo_features_count) }}</td>
                        <td style="width:40%;">
                            <div style="height:6px;background:#E5E7EB;border-radius:3px;">
                                <div style="height:100%;width:{{ $layer->geo_features_count / max($maxLayer,1) * 100 }}%;background:#4B7DF3;border-radius:3px;"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="font-size:16px;font-weight:700;color:#1F2937;margin-bottom:16px;">📈 النشاط اليومي (آخر 7 أيام)</div>
        @if(count($dailyStats) > 0)
            @php $maxDaily = max(array_column($dailyStats, 'count')); @endphp
            <div style="display:flex;align-items:end;gap:8px;height:160px;padding-top:20px;">
                @foreach($dailyStats as $day)
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
                        <span style="font-size:11px;font-weight:700;color:#6B7280;">{{ $day['count'] }}</span>
                        <div style="width:100%;background:#DBEAFE;border-radius:4px 4px 0 0;transition:height 0.3s;height:{{ $day['count'] / max($maxDaily,1) * 130 }}px;"></div>
                        <span style="font-size:9px;color:#9CA3AF;">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding:48px 20px;text-align:center;color:#9CA3AF;">لا توجد بيانات للنشاط اليومي</div>
        @endif
    </div>
</div>

<div class="card" style="margin-top:20px;">
    <div class="card-header" style="font-size:16px;font-weight:700;color:#1F2937;margin-bottom:16px;">📋 حالات المعالم حسب الطبقة</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>الطبقة</th><th>موثقة ✅</th><th>بانتظار ⏳</th><th>مؤرشفة 📦</th><th>الإجمالي</th></tr></thead>
            <tbody>
                @foreach($byLayer as $layer)
                <tr>
                    <td class="fw-700">{{ $layer->name }}</td>
                    <td>{{ number_format($layer->verified_count) }}</td>
                    <td>{{ number_format($layer->pending_count) }}</td>
                    <td>{{ number_format($layer->geo_features_count - $layer->verified_count - $layer->pending_count) }}</td>
                    <td class="fw-700">{{ number_format($layer->geo_features_count) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
