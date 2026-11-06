@extends('layouts.admin')

@section('title', 'تحديات رسم الحدود')

@section('content')
<div class="font-cairo rtl p-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-navy flex items-center gap-2">🗺️ تحديات رسم الحدود</h1>
            <p class="text-sm text-gray-500 mt-1">حدود الأحياء والقرى المُرسلة بواسطة المستخدمين — تحتاج مراجعة ومصادقة</p>
        </div>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline flex items-center gap-2">
            ← العودة للفعاليات
        </a>
    </div>

    @if($challenges->isEmpty())
    <div class="card flex flex-col items-center justify-center py-20 bg-white border border-gray-100 rounded-xl">
        <div class="text-6xl mb-4 opacity-20">✅</div>
        <h3 class="text-xl font-bold text-navy">لا توجد تحديات معلقة</h3>
        <p class="text-sm text-gray-500">تمت مراجعة جميع تحديات الحدود المرسلة من المستخدمين</p>
    </div>
    @else
    <div class="card bg-white shadow-sm border border-gray-100 rounded-xl overflow-hidden">
        <div class="table-wrap">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">اسم الحي/القرية</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">الطبقة</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">الوصف</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">تاريخ الإرسال</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($challenges as $ch)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-navy text-sm">{{ $ch->name }}</div>
                            <div class="text-[10px] text-gray-400 font-mono mt-1">{{ Str::limit($ch->id, 16) }}</div>
                        </td>
                        <td class="p-4">
                            <span class="badge badge-blue">{{ $ch->layer_name }}</span>
                        </td>
                        <td class="p-4 text-xs text-gray-500 max-w-xs truncate">
                            {{ $ch->description ?? 'بدون وصف' }}
                        </td>
                        <td class="p-4 text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($ch->created_at)->diffForHumans() }}
                        </td>
                        <td class="p-4">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Preview --}}
                                <a href="/admin/map?highlight={{ $ch->id }}" target="_blank"
                                    class="icon-btn bg-gray-100 text-navy hover:bg-gray-200" title="عرض على الخريطة">
                                    🗺️
                                </a>
                                {{-- Approve --}}
                                <form method="POST" action="{{ route('admin.events.boundary-challenges.approve', $ch->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success py-2 font-bold" title="قبول التحدي">
                                        ✅ قبول
                                    </button>
                                </form>
                                {{-- Reject --}}
                                <form method="POST" action="{{ route('admin.events.boundary-challenges.reject', $ch->id) }}" onsubmit="return confirm('هل أنت متأكد من رفض هذا التحدي؟')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm bg-red-100 text-red-700 hover:bg-red-200 py-2 font-bold" title="رفض التحدي">
                                        ❌ رفض
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
