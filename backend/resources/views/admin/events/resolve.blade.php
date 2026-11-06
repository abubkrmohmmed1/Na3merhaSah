@extends('layouts.admin')

@section('title', 'تتويج وتوزيع جوائز الفعالية')
@section('page_title', '🏆 تتويج الفائزين وتوزيع الجوائز')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Event Details Card -->
    <div class="bg-gradient-to-r from-navy to-navy-dark text-white p-6 rounded-2xl shadow-xl relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10 translate-x-12 -translate-y-12">
            <span class="text-9xl">🏆</span>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="bg-yellow-500/20 text-yellow-400 text-xs font-bold px-3 py-1 rounded-full border border-yellow-500/30">
                    {{ match($event->type) {
                        'competition' => 'مسابقة كبرى',
                        'challenge' => 'تحدي حدود',
                        'community' => 'عمل مجتمعي',
                        'announcement' => 'فعالية عامة',
                        default => $event->type
                    } }}
                </span>
                <h2 class="text-2xl font-black mt-2 text-yellow-400">{{ $event->title }}</h2>
                <p class="text-sm text-gray-300 mt-1 max-w-xl">{{ $event->description }}</p>
                <div class="flex gap-4 mt-4 text-xs text-gray-300">
                    <span>📅 تاريخ البدء: <strong>{{ \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d H:i') }}</strong></span>
                    @if($event->ends_at)
                    <span>📅 تاريخ الانتهاء: <strong>{{ \Carbon\Carbon::parse($event->ends_at)->format('Y-m-d H:i') }}</strong></span>
                    @endif
                </div>
            </div>
            <div class="text-left md:text-right bg-white/10 p-4 rounded-xl border border-white/10">
                <div class="text-xs text-gray-300">مكافأة الفعالية الإضافية</div>
                <div class="text-3xl font-black text-yellow-400 mt-1">{{ number_format($event->reward_points) }} <span class="text-sm font-normal">نقطة</span></div>
            </div>
        </div>
    </div>

    <!-- Leaderboard & Participants -->
    <div class="card">
        <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-black text-navy">📊 لوحة متصدري الفعالية</h3>
                <p class="text-xs text-gray-500">الترتيب التنافسي المباشر للمستخدمين الأكثر مساهمة خلال فترة الفعالية</p>
            </div>
            <span class="badge badge-green font-bold">نشط وتنافسي</span>
        </div>

        @if($leaderboard->isEmpty())
            <div class="empty-state">
                <span class="text-4xl block mb-2">😢</span>
                <p class="font-bold text-gray-700">لا توجد مساهمات مقبولة حتى الآن</p>
                <p class="text-xs text-gray-400 mt-1">يجب على المستخدمين المشاركة وأن يقوم المشرف بقبول مساهماتهم أولاً لتظهر هنا.</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>المركز</th>
                            <th>المشارك</th>
                            <th>البريد الإلكتروني</th>
                            <th class="text-center">المساهمات المعتمدة</th>
                            <th class="text-center">النقاط المكتسبة</th>
                            <th class="text-center">المكافأة الإضافية المتوقعة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard as $index => $row)
                            <tr class="{{ $index == 0 ? 'bg-yellow-500/5 font-semibold' : '' }}">
                                <td>
                                    @if($index == 0)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-500 text-white font-bold text-xs">🥇</span>
                                    @elseif($index == 1)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-400 text-white font-bold text-xs">🥈</span>
                                    @elseif($index == 2)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-700 text-white font-bold text-xs">🥉</span>
                                    @else
                                        <span class="text-gray-500 text-xs font-bold mr-2">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center font-bold text-xs">
                                            {{ mb_substr($row->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-navy font-bold">{{ $row->name }}</div>
                                            @if($index == 0)
                                                <span class="text-[10px] bg-yellow-500 text-white px-2 py-0.5 rounded font-black">البطل المتصدر 👑</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-gray-500 text-xs">{{ $row->email }}</td>
                                <td class="text-center font-bold text-navy">{{ $row->contributions_count }}</td>
                                <td class="text-center text-green-600 font-bold">+{{ number_format($row->base_points) }} XP</td>
                                <td class="text-center text-yellow-600 font-black">
                                    @if($index == 0)
                                        +{{ number_format($event->reward_points) }} XP (100%)
                                    @elseif($index == 1)
                                        +{{ number_format($event->reward_points * 0.5) }} XP (50%)
                                    @elseif($index == 2)
                                        +{{ number_format($event->reward_points * 0.25) }} XP (25%)
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Resolution Action Section -->
            <div class="mt-6 p-4 rounded-xl bg-yellow-50 border border-yellow-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="space-y-1">
                    <h4 class="font-bold text-yellow-800 flex items-center gap-2">
                        <span>⚠️</span> تأكيد إقفال وتتويج الفائزين
                    </h4>
                    <p class="text-xs text-yellow-700">
                        سيؤدي هذا الإجراء إلى إنهاء الفعالية نهائياً، وتوزيع النقاط الإضافية آلياً للفائزين بالمراتب الثلاثة الأولى، ونشر إعلان منبثق فوري بملء شاشة التطبيق لإشهار الفائزين.
                    </p>
                </div>
                <form action="{{ route('admin.events.resolve', $event->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary bg-yellow-500 hover:bg-yellow-600 border-none shadow-md whitespace-nowrap text-navy font-black">
                        🏆 تتويج الفائزين وتوزيع الجوائز الآن
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
