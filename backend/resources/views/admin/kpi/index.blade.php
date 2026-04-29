@extends('layouts.admin')

@section('title', 'مؤشرات الأداء (KPIs)')
@section('page_title', 'مؤشرات الأداء والتقييم')

@section('styles')
<style>
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .kpi-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .kpi-value {
        font-size: 32px;
        font-weight: 800;
        color: #1A284D;
        margin: 10px 0;
    }

    .kpi-label {
        color: #718096;
        font-size: 14px;
        font-weight: 600;
    }

    .stars {
        color: #F6AD55;
        font-size: 20px;
        margin-bottom: 5px;
    }

    .performance-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }

    .perf-item {
        margin-bottom: 20px;
    }

    .perf-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .progress-bar-bg {
        height: 10px;
        background: #EDF2F7;
        border-radius: 5px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: #4B7DF3;
        border-radius: 5px;
    }

    .issue-tag {
        display: inline-block;
        padding: 8px 16px;
        background: #FEE2E2;
        color: #991B1B;
        border-radius: 20px;
        margin: 5px;
        font-size: 13px;
        font-weight: 700;
    }
</style>
@endsection

@section('content')
    <!-- Main Stats -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">إجمالي البلاغات المنفذة</div>
            <div class="kpi-value">{{ $totalResolved }}</div>
            <div style="color: #10B981; font-size: 12px; font-weight: 700;">+12% من الشهر الماضي</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">متوسط رضا المواطن</div>
            <div class="stars">
                @for($i=1; $i<=5; $i++)
                    @if($i <= round($avgRating)) ★ @else ☆ @endif
                @endfor
            </div>
            <div class="kpi-value">{{ number_format($avgRating, 1) }} / 5</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <!-- Detailed Indicators -->
        <div class="performance-card">
            <h3 style="margin-top: 0; margin-bottom: 25px; color: #1A284D;">مؤشرات جودة الخدمة</h3>
            
            @foreach($kpis as $key => $value)
                @php
                    $labels = [
                        'quality' => 'جودة التنفيذ الفني',
                        'time' => 'الالتزام بالجدول الزمني',
                        'behavior' => 'سلوك الفرق الميدانية',
                        'cleanliness' => 'نظافة الموقع بعد العمل',
                    ];
                    $percentage = ($value / 5) * 100;
                @endphp
                <div class="perf-item">
                    <div class="perf-header">
                        <span>{{ $labels[$key] }}</span>
                        <span>{{ number_format($percentage, 0) }}%</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: {{ $percentage }}%; background: {{ $percentage > 80 ? '#10B981' : ($percentage > 50 ? '#4B7DF3' : '#F59E0B') }};"></div>
                    </div>
                </div>
            @endforeach
        </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
        <!-- Categories Distribution -->
        <div class="performance-card">
            <h3 style="margin-top: 0; margin-bottom: 20px; color: #1A284D;">توزيع البلاغات حسب النوع</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                @foreach($categories as $category)
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #F1F4F9; padding-bottom: 10px;">
                        <span style="font-weight: 700; color: #2B3674;">{{ $category['name'] }}</span>
                        <span style="background: #E0E7FF; color: #3730A3; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 800;">{{ $category['total'] }} بلاغ</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Hotspots / Neighborhoods -->
        <div class="performance-card">
            <h3 style="margin-top: 0; margin-bottom: 20px; color: #1A284D;">أكثر المناطق تأثراً (Hotspots)</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                @foreach($locations as $loc)
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #F1F4F9; padding-bottom: 10px;">
                        <span style="font-weight: 700; color: #2B3674;">{{ $loc->neighborhood ?? 'غير محدد' }}</span>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 100px; height: 6px; background: #EDF2F7; border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; background: #EF4444; width: {{ ($loc->total / $totalResolved) * 100 }}%;"></div>
                            </div>
                            <span style="color: #EF4444; font-size: 12px; font-weight: 800;">{{ $loc->total }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
