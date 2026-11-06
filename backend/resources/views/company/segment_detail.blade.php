@extends('company.layout')

@section('title', $segment['name'])
@section('page_title', $segment['icon'] . ' ' . $segment['name'])

@section('content')
<div class="mb-5">
    <a href="{{ route('company.segments') }}" class="text-fluent-textSecondary hover:text-fluent-blue no-underline font-semibold flex items-center gap-2 text-sm transition-colors">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: scaleX(-1);"><path stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        العودة لجميع القطاعات
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Left Column: Details -->
    <div>
        <div class="card" style="border-left: 4px solid #d83b01;">
            <h3 class="text-base font-semibold mb-2 flex items-center gap-2" style="color: #d83b01;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                التحدي (المشكلة)
            </h3>
            <p class="text-fluent-textSecondary text-sm leading-relaxed m-0">{{ $segment['problem'] }}</p>
        </div>
        
        <div class="card" style="border-left: 4px solid #107c10;">
            <h3 class="text-base font-semibold mb-2 flex items-center gap-2" style="color: #107c10;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                الحل التقني
            </h3>
            <p class="text-fluent-textSecondary text-sm leading-relaxed m-0">{{ $segment['solution'] }}</p>
        </div>
        
        <div class="card" style="border-left: 4px solid #0078d4;">
            <h3 class="text-base font-semibold mb-2 flex items-center gap-2" style="color: #0078d4;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                المنتج المقترح
            </h3>
            <p class="font-semibold text-lg text-fluent-text mb-1">{{ $segment['product'] }}</p>
            <p class="text-fluent-textSecondary text-sm m-0">{{ $segment['price'] }}</p>
        </div>
    </div>

    <!-- Right Column: API & Integration -->
    <div>
        <div class="card">
            <h3 class="text-base font-semibold text-fluent-text mb-4 border-b border-fluent-border pb-3">نقاط الوصول الخاصة بالقطاع (Endpoints)</h3>
            <div class="space-y-3">
            @foreach($segment['endpoints'] as $ep)
                <div class="p-3 border border-fluent-border rounded-sm hover:bg-fluent-hover transition-colors">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded-sm text-[11px] font-semibold bg-fluent-blue text-white">{{ $ep['method'] }}</span>
                        <span class="font-semibold text-sm text-fluent-text">{{ $ep['name'] }}</span>
                    </div>
                    <p class="text-xs text-fluent-textSecondary mb-2">{{ $ep['desc'] }}</p>
                    <pre class="bg-fluent-hover p-2 rounded-sm text-[11px] text-fluent-text border border-fluent-border m-0 overflow-x-auto" style="direction: ltr; font-family: 'Consolas', monospace;">{{ $ep['url'] }}</pre>
                </div>
            @endforeach
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-3 border-b border-fluent-border bg-fluent-hover">
                <h3 class="text-sm font-semibold text-fluent-text m-0">تجربة سريعة (cURL)</h3>
            </div>
            <div class="p-4" style="background: #1b1b1b;">
                <pre class="text-[#d4d4d4] text-[12px] m-0 overflow-x-auto whitespace-pre-wrap" style="direction: ltr; font-family: 'Consolas', monospace;">{{ str_replace('YOUR_API_KEY', $apiKey->key, $segment['curl']) }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
