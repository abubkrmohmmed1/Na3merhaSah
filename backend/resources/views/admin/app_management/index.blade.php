@extends('layouts.admin')

@section('title', 'إدارة تحديثات وإعلانات التطبيق')

@section('content')
<div class="font-cairo rtl p-6">

    {{-- Success and Error Alert messages --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6 font-bold flex items-center gap-2">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-navy">⚙️ إدارة التحديثات وإعلانات التطبيق</h1>
            <p class="text-sm text-gray-500 mt-1">التحكم في إصدارات تطبيق الموبايل وإرسال التنبيهات المنبثقة الفورية للمستخدمين</p>
        </div>
    </div>

    {{-- Grid Layout for CRUD Panels --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

        {{-- 1. App Versions Panel --}}
        <div class="card bg-white p-6 shadow rounded-xl">
            <h2 class="text-lg font-bold text-navy mb-4 flex items-center gap-2">
                <span>📱 إدارة إصدارات التطبيق (App Versions)</span>
            </h2>

            {{-- Form to Add New Version --}}
            <form action="{{ route('admin.app.version.store') }}" method="POST" class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-100">
                @csrf
                <h3 class="text-sm font-bold text-navy mb-3">➕ تسجيل إصدار جديد</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">المنصة (Platform)</label>
                        <select name="platform" class="w-full text-xs p-2 border border-gray-300 rounded" required>
                            <option value="all">الكل (Android & iOS)</option>
                            <option value="android">أندرويد (Android)</option>
                            <option value="ios">آيفون (iOS)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">كود الإصدار (Version Code)</label>
                        <input type="number" name="version_code" class="w-full text-xs p-2 border border-gray-300 rounded" placeholder="مثال: 10" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">اسم الإصدار (Version Name)</label>
                        <input type="text" name="version_name" class="w-full text-xs p-2 border border-gray-300 rounded" placeholder="مثال: 1.1.0" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">رابط التحميل / المتجر (Store URL)</label>
                        <input type="url" name="download_url" class="w-full text-xs p-2 border border-gray-300 rounded" placeholder="https://..." required>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-xs font-bold text-gray-700 mb-1">ملاحظات الإصدار والتحديثات (Release Notes)</label>
                    <textarea name="release_notes" rows="2" class="w-full text-xs p-2 border border-gray-300 rounded" placeholder="أدخل تفاصيل التحديث..."></textarea>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-red-600">
                        <input type="checkbox" name="is_mandatory" value="1" class="rounded border-gray-300">
                        تحديث إجباري (يمنع استخدام التطبيق بدون تحديث)
                    </label>
                    <button type="submit" class="btn btn-primary text-xs px-4 py-2 bg-navy text-white rounded">تسجيل الإصدار</button>
                </div>
            </form>

            {{-- Versions List Table --}}
            <h3 class="text-sm font-bold text-navy mb-3">📋 الإصدارات الحالية</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 font-bold border-b border-gray-200">
                            <th class="p-2">المنصة</th>
                            <th class="p-2">كود الإصدار</th>
                            <th class="p-2">اسم الإصدار</th>
                            <th class="p-2">نوع التحديث</th>
                            <th class="p-2">الحالة</th>
                            <th class="p-2">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versions as $version)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-2">
                                @if($version->platform == 'android') 🤖 Android
                                @elseif($version->platform == 'ios') 🍏 iOS
                                @else 📱 الكل @endif
                            </td>
                            <td class="p-2 font-bold">{{ $version->version_code }}</td>
                            <td class="p-2">{{ $version->version_name }}</td>
                            <td class="p-2">
                                @if($version->is_mandatory)
                                    <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 font-bold">إلزامى ⛔</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold">اختياري 🟢</span>
                                @endif
                            </td>
                            <td class="p-2">
                                @if($version->is_active)
                                    <span class="text-green-600 font-bold">نشط</span>
                                @else
                                    <span class="text-gray-400">موقوف</span>
                                @endif
                            </td>
                            <td class="p-2 flex gap-2">
                                <form action="{{ route('admin.app.version.toggle', $version->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn text-[10px] px-2 py-1 bg-yellow-500 text-white rounded">
                                        {{ $version->is_active ? 'تعطيل' : 'تفعيل' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.app.version.delete', $version->id) }}" method="POST" onsubmit="return confirm('حذف هذا الإصدار؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn text-[10px] px-2 py-1 bg-red-600 text-white rounded">حذف</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-400">لا توجد إصدارات مسجلة بعد.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 2. Announcements & Overlay Messages Panel --}}
        <div class="card bg-white p-6 shadow rounded-xl">
            <h2 class="text-lg font-bold text-navy mb-4 flex items-center gap-2">
                <span>📢 التنبيهات والإعلانات المنبثقة (System Announcements)</span>
            </h2>

            {{-- Form to Publish New Announcement --}}
            <form action="{{ route('admin.app.announcement.store') }}" method="POST" class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-100">
                @csrf
                <h3 class="text-sm font-bold text-navy mb-3">➕ نشر إعلان منبثق جديد</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">عنوان التنبيه (Title)</label>
                        <input type="text" name="title" class="w-full text-xs p-2 border border-gray-300 rounded" placeholder="مثال: بدأ تحدي جديد!" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">نوع التنبيه</label>
                        <select name="type" class="w-full text-xs p-2 border border-gray-300 rounded" required>
                            <option value="info">إرشادي (Info)</option>
                            <option value="event">فعالية جديدة (New Event)</option>
                            <option value="warning">تحذير أمني (Warning)</option>
                            <option value="success">تهنئة / نجاح (Success)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ البدء</label>
                        <input type="datetime-local" name="starts_at" class="w-full text-xs p-2 border border-gray-300 rounded" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">تاريخ الانتهاء</label>
                        <input type="datetime-local" name="ends_at" class="w-full text-xs p-2 border border-gray-300 rounded">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">ربط بفعالية (اختياري لكي يتم التحويل إليها)</label>
                        <select name="event_id" class="w-full text-xs p-2 border border-gray-300 rounded">
                            <option value="">غير مرتبط بفعالية محددة</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-xs font-bold text-gray-700 mb-1">نص التنبيه الرسالة بالتفصيل</label>
                    <textarea name="message" rows="3" class="w-full text-xs p-2 border border-gray-300 rounded" placeholder="اكتب تفاصيل التنبيه الذي سيظهر كرسالة بملء الشاشة مع زر إغلاق..." required></textarea>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn btn-primary text-xs px-4 py-2 bg-navy text-white rounded">نشر الإعلان المنبثق</button>
                </div>
            </form>

            {{-- Announcements List Table --}}
            <h3 class="text-sm font-bold text-navy mb-3">📋 الإعلانات النشطة</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 font-bold border-b border-gray-200">
                            <th class="p-2">العنوان</th>
                            <th class="p-2">النوع</th>
                            <th class="p-2">الفعالية</th>
                            <th class="p-2">تاريخ الانتهاء</th>
                            <th class="p-2">الحالة</th>
                            <th class="p-2">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $ann)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-2 font-bold">{{ $ann->title }}</td>
                            <td class="p-2">
                                @if($ann->type == 'info') 🔵 إرشادي
                                @elseif($ann->type == 'warning') 🔴 تحذيري
                                @elseif($ann->type == 'success') 🟢 نجاح
                                @else 🏆 فعالية @endif
                            </td>
                            <td class="p-2 text-gray-500">{{ $ann->event_title ?? 'لا يوجد' }}</td>
                            <td class="p-2 text-gray-500">{{ $ann->ends_at ?? 'دائم' }}</td>
                            <td class="p-2">
                                @if($ann->is_active && (!$ann->ends_at || \Carbon\Carbon::parse($ann->ends_at)->isFuture()))
                                    <span class="text-green-600 font-bold">نشط 🟢</span>
                                @else
                                    <span class="text-red-500">منتهي/موقف</span>
                                @endif
                            </td>
                            <td class="p-2 flex gap-2">
                                <form action="{{ route('admin.app.announcement.toggle', $ann->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn text-[10px] px-2 py-1 bg-yellow-500 text-white rounded">
                                        {{ $ann->is_active ? 'تعطيل' : 'تفعيل' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.app.announcement.delete', $ann->id) }}" method="POST" onsubmit="return confirm('حذف هذا الإعلان؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn text-[10px] px-2 py-1 bg-red-600 text-white rounded">حذف</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-400">لا توجد إعلانات منشورة بعد.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
