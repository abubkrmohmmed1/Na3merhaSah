@extends('company.layout')

@section('title', 'حالات الاستخدام')
@section('page_title', 'حالات الاستخدام (Use Cases)')

@section('content')
<div class="card" style="margin-bottom: 24px; border-left: 4px solid #0078d4;">
    <h2 style="margin-top: 0; font-weight: 600; font-size: 18px; color: #323130;">اختر قطاعك لترى الحل المناسب</h2>
    <p style="color: #605e5c; margin-bottom: 0;">لكل قطاع مشكلة محددة وحل مباشر من واجهة برمجة تطبيقات (API) نعمرها صح. اختر القطاع المناسب لمجالك لمعرفة كيفية الدمج والتكامل.</p>
</div>

@php
$slugs = ['real_estate','insurance','advertising','agriculture','tourism','telecom','emergency'];
@endphp

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
    @foreach($segments as $key => $segment)
        @php $slug = $slugs[$loop->index] ?? $key; @endphp
        <a href="{{ route('company.segment-detail', $slug) }}" style="text-decoration: none; display: block;">
            <div class="card" style="margin-bottom: 0; border: 1px solid transparent; border-top: 3px solid {{ $segment['color'] }}; transition: all 0.1s; display: flex; flex-direction: column; height: 100%;"
                 onmouseover="this.style.boxShadow='0 3.2px 7.2px 0 rgba(0,0,0,0.132), 0 0.6px 1.8px 0 rgba(0,0,0,0.108)'; this.style.backgroundColor='#f3f2f1';"
                 onmouseout="this.style.boxShadow='0 1.6px 3.6px 0 rgba(0,0,0,0.132), 0 0.3px 0.9px 0 rgba(0,0,0,0.108)'; this.style.backgroundColor='#ffffff';">
                
                <div style="font-size: 32px; margin-bottom: 12px; color: {{ $segment['color'] }};">{{ $segment['icon'] }}</div>
                <h3 style="margin: 0 0 8px; color: #323130; font-size: 16px; font-weight: 600;">{{ $segment['name'] }}</h3>
                <p style="color: #605e5c; font-size: 13px; margin: 0; flex: 1;">تعرف على كيفية حل التحديات المكانية المحددة بقطاعك خطوة بخطوة.</p>
                
                <div style="margin-top: 16px; color: #0078d4; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    استكشف الحل
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </div>
            </div>
        </a>
    @endforeach
</div>
@endsection
