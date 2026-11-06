@extends('layouts.admin')
@section('title', 'الرئيسية')
@section('page_title', 'لوحة التحكم')

@section('content')
@php
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;

$totalFeatures = GeoFeature::count();
$verified = GeoFeature::whereIn('status', ['verified', 'active'])->count();
$pending = GeoFeature::where('status', 'pending')->count();
$layersCount = MapLayer::count();
$usersCount = \App\Domains\Auth\Models\User::count();
@endphp

<div class="stat-grid">
    <div class="stat-card">
        <div class="icon-wrap" style="background: #DBEAFE;"><span>🗺️</span></div>
        <div class="num" style="color: #1E40AF;">{{ number_format($totalFeatures) }}</div>
        <div class="lbl">إجمالي المعالم</div>
    </div>
    <div class="stat-card">
        <div class="icon-wrap" style="background: #D1FAE5;"><span>✅</span></div>
        <div class="num" style="color: #065F46;">{{ number_format($verified) }}</div>
        <div class="lbl">موثقة</div>
    </div>
    <div class="stat-card">
        <div class="icon-wrap" style="background: #FEF3C7;"><span>⏳</span></div>
        <div class="num" style="color: #92400E;">{{ number_format($pending) }}</div>
        <div class="lbl">بانتظار المراجعة</div>
    </div>
    <div class="stat-card">
        <div class="icon-wrap" style="background: #EDE9FE;"><span>📦</span></div>
        <div class="num" style="color: #6D28D9;">{{ $layersCount }}</div>
        <div class="lbl">طبقة جغرافية</div>
    </div>
    <div class="stat-card">
        <div class="icon-wrap" style="background: #FCE7F3;"><span>👥</span></div>
        <div class="num" style="color: #BE185D;">{{ $usersCount }}</div>
        <div class="lbl">مستخدم</div>
    </div>
</div>

<div class="flex gap-4" style="flex-wrap: wrap;">
    <div class="card" style="flex: 2; min-width: 300px;">
        <div class="card-header">🔗 روابط سريعة</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 10px;">
            <a href="{{ route('admin.map') }}" class="btn btn-primary" style="justify-content:center;">🗺️ عرض الخريطة</a>
            <a href="{{ route('admin.submissions.index', ['tab' => 'mobile']) }}" class="btn btn-success" style="justify-content:center;">📋 مراجعة التقديمات</a>
            <a href="{{ route('admin.kpi') }}" class="btn btn-outline" style="justify-content:center;">📈 مؤشرات الأداء</a>
            <a href="{{ route('admin.spatial.index') }}" class="btn btn-outline" style="justify-content:center;">📍 إدارة المعالم</a>
        </div>
    </div>
    <div class="card" style="flex: 1; min-width: 200px;">
        <div class="card-header">📊 حالة المعالم</div>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <div>
                <div class="flex-between text-sm"><span>موثقة</span><span>{{ number_format($verified / max($totalFeatures, 1) * 100, 1) }}%</span></div>
                <div style="height: 6px; background: #E5E7EB; border-radius: 3px; margin-top: 4px;">
                    <div style="height: 100%; width: {{ $verified / max($totalFeatures, 1) * 100 }}%; background: #10B981; border-radius: 3px;"></div>
                </div>
            </div>
            <div>
                <div class="flex-between text-sm"><span>بانتظار المراجعة</span><span>{{ number_format($pending / max($totalFeatures, 1) * 100, 1) }}%</span></div>
                <div style="height: 6px; background: #E5E7EB; border-radius: 3px; margin-top: 4px;">
                    <div style="height: 100%; width: {{ $pending / max($totalFeatures, 1) * 100 }}%; background: #F59E0B; border-radius: 3px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
