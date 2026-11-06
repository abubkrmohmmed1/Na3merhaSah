@extends('layouts.admin')

@section('title', 'الفعاليات والأنشطة')

@section('content')
<div class="font-cairo rtl p-6">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-navy">🎯 إدارة الفعاليات والأنشطة</h1>
            <p class="text-sm text-gray-500 mt-1">إنشاء مسابقات وتحديات وإعلانات للمستخدمين داخل التطبيق</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.events.boundary-challenges') }}" class="btn bg-indigo-600 text-white flex items-center gap-2">
                🗺️ تحديات الحدود
                @if($pendingCount > 0) 
                    <span class="bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px]">{{ $pendingCount }}</span> 
                @endif
            </a>
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                + فعالية جديدة
            </a>
        </div>
    </div>

    {{-- Events Grid --}}
    @if($events->isEmpty())
    <div class="card flex flex-col items-center justify-center py-16">
        <div class="text-5xl mb-4">🎪</div>
        <h3 class="text-lg font-bold text-navy">لا توجد فعاليات بعد</h3>
        <p class="text-sm text-gray-500 mb-4">ابدأ بإنشاء أول فعالية لمستخدمي التطبيق!</p>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">+ إنشاء فعالية</a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($events as $event)
        @php
            $typeColors = [
                'competition'  => ['bg'=>'bg-amber-100','text'=>'text-amber-800','label'=>'🏆 مسابقة'],
                'challenge'    => ['bg'=>'bg-purple-100','text'=>'text-purple-800','label'=>'⚡ تحدي'],
                'community'    => ['bg'=>'bg-emerald-100','text'=>'text-emerald-800','label'=>'🤝 مجتمعي'],
                'announcement' => ['bg'=>'bg-blue-100','text'=>'text-blue-800','label'=>'📢 إعلان'],
            ];
            $tc = $typeColors[$event->type] ?? $typeColors['announcement'];
            $isExpired = $event->ends_at && \Carbon\Carbon::parse($event->ends_at)->isPast();
        @endphp
        <div class="card p-0 overflow-hidden border-t-4 {{ $event->is_active && !$isExpired ? 'border-brand-blue' : 'border-gray-400' }}">
            @if($event->image_url)
            <div class="h-32 bg-center bg-cover bg-gray-100" style="background-image: url('{{ $event->image_url }}')"></div>
            @endif
            <div class="p-4">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $tc['bg'] }} {{ $tc['text'] }}">{{ $tc['label'] }}</span>
                    @if(!$event->is_active)
                        <span class="text-[10px] text-gray-400">⏸ موقوفة</span>
                    @elseif($isExpired)
                        <span class="text-[10px] text-red-600 font-bold">⌛ منتهية</span>
                    @else
                        <span class="text-[10px] text-green-600 font-bold">✅ نشطة</span>
                    @endif
                </div>
                <h3 class="text-sm font-bold text-navy mb-2">{{ $event->title }}</h3>
                <p class="text-xs text-gray-500 mb-4 line-clamp-2 h-8">{{ $event->description }}</p>
                <div class="flex flex-wrap gap-3 text-[11px] text-gray-500 mb-4">
                    @if($event->reward_points > 0)
                    <span class="flex items-center gap-1">🎯 {{ $event->reward_points }} نقطة</span>
                    @endif
                    <span class="flex items-center gap-1">📅 {{ \Carbon\Carbon::parse($event->starts_at)->format('Y/m/d') }}</span>
                    @if($event->latitude && $event->longitude)
                    <span class="flex items-center gap-1 text-brand-blue font-bold">📍 مثبتة على الخريطة</span>
                    @endif
                </div>
                <div class="flex gap-2 pt-3 border-t border-gray-100">
                    <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-sm btn-primary">✏️ تعديل</a>
                    @if(empty($event->is_resolved) && $event->type !== 'announcement')
                    <a href="{{ route('admin.events.resolve.form', $event->id) }}" class="btn btn-sm bg-yellow-500 text-navy font-black">🏆 تتويج</a>
                    @elseif(!empty($event->is_resolved))
                    <span class="text-xs bg-gray-100 text-gray-500 font-bold px-2.5 py-1.5 rounded-lg flex items-center">🏆 منتهية</span>
                    @endif
                    <form method="POST" action="{{ route('admin.events.destroy', $event->id) }}" onsubmit="return confirm('حذف هذه الفعالية؟')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm bg-red-500 text-white border-none">🗑️</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
