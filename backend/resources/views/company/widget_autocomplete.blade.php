@extends('company.layout')

@section('title', 'ويدجت البحث التلقائي')
@section('page_title', '🧩 ويدجت البحث التلقائي (Autocomplete Widget)')

@section('content')
<div class="grid grid-cols-1 gap-6">
    <div class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-fluent-border flex items-center gap-3" style="background: #faf9f8;">
            <svg width="24" height="24" fill="none" stroke="#0078d4" viewBox="0 0 24 24"><path stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            <div>
                <h2 class="text-base font-semibold text-fluent-text m-0">كود التضمين المباشر (Embed Code)</h2>
                <p class="text-sm text-fluent-textSecondary m-0 mt-1">انسخ هذا الكود وألصقه في نظامك الخاص ليظهر حقل بحث العناوين التلقائي مباشرة، بدون بناء أي واجهات معقدة.</p>
            </div>
        </div>
        
        <div class="p-4" style="background: #1b1b1b;">
            <pre class="text-[#d4d4d4] text-[13px] m-0 overflow-x-auto whitespace-pre-wrap" style="direction: ltr; font-family: 'Consolas', monospace;">
&lt;div id="namerha-address-widget"&gt;&lt;/div&gt;
&lt;script src="{{ $apiBase }}/company/widget/autocomplete.js"&gt;&lt;/script&gt;
&lt;script&gt;
    NamerhaAddressWidget({
        apiKey: '{{ $apiKey->key }}',
        container: 'namerha-address-widget',
        placeholder: 'ابحث عن عنوان أو منطقة...',
        language: 'ar' // Options: 'ar', 'en'
    });
&lt;/script&gt;</pre>
        </div>
    </div>

    <div class="card">
        <h3 class="text-base font-semibold text-fluent-text mb-4 border-b border-fluent-border pb-3">الخصائص المتاحة (Configuration Options)</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right" style="border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #edebe9;">
                        <th class="py-3 px-4 font-semibold text-fluent-textSecondary w-1/4">الخاصية (Option)</th>
                        <th class="py-3 px-4 font-semibold text-fluent-textSecondary w-1/4">النوع (Type)</th>
                        <th class="py-3 px-4 font-semibold text-fluent-textSecondary w-1/2">الوصف (Description)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-fluent-hover border-b border-fluent-border transition-colors">
                        <td class="py-3 px-4"><code class="bg-fluent-hover px-2 py-1 rounded-sm border border-fluent-border text-fluent-text" style="font-family: monospace;">apiKey</code></td>
                        <td class="py-3 px-4 text-fluent-blue font-mono">string</td>
                        <td class="py-3 px-4 text-fluent-text">مفتاح الـ API الخاص بمنشأتك (مطلوب).</td>
                    </tr>
                    <tr class="hover:bg-fluent-hover border-b border-fluent-border transition-colors">
                        <td class="py-3 px-4"><code class="bg-fluent-hover px-2 py-1 rounded-sm border border-fluent-border text-fluent-text" style="font-family: monospace;">container</code></td>
                        <td class="py-3 px-4 text-fluent-blue font-mono">string</td>
                        <td class="py-3 px-4 text-fluent-text">مُعرّف الـ ID الخاص بـ <code>&lt;div&gt;</code> الذي سيتم عرض الويدجت بداخله.</td>
                    </tr>
                    <tr class="hover:bg-fluent-hover border-b border-fluent-border transition-colors">
                        <td class="py-3 px-4"><code class="bg-fluent-hover px-2 py-1 rounded-sm border border-fluent-border text-fluent-text" style="font-family: monospace;">placeholder</code></td>
                        <td class="py-3 px-4 text-fluent-blue font-mono">string</td>
                        <td class="py-3 px-4 text-fluent-text">النص الافتتاحي الذي يظهر داخل مربع البحث (اختياري).</td>
                    </tr>
                    <tr class="hover:bg-fluent-hover border-b border-fluent-border transition-colors">
                        <td class="py-3 px-4"><code class="bg-fluent-hover px-2 py-1 rounded-sm border border-fluent-border text-fluent-text" style="font-family: monospace;">language</code></td>
                        <td class="py-3 px-4 text-fluent-blue font-mono">enum ('ar', 'en')</td>
                        <td class="py-3 px-4 text-fluent-text">لغة واجهة الويدجت واتجاه الكتابة (RTL / LTR).</td>
                    </tr>
                    <tr class="hover:bg-fluent-hover border-b border-fluent-border transition-colors">
                        <td class="py-3 px-4"><code class="bg-fluent-hover px-2 py-1 rounded-sm border border-fluent-border text-fluent-text" style="font-family: monospace;">onSelect</code></td>
                        <td class="py-3 px-4 text-fluent-blue font-mono">function(item)</td>
                        <td class="py-3 px-4 text-fluent-text">دالة رد اتصال (Callback) يتم تنفيذها عند اختيار المستخدم لعنوان. تُرجع الـ <code>S2 Token</code> والإحداثيات الجغرافية.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
