@extends('layouts.admin')
@section('title', $company->name)
@section('page_title', 'الشركة: '.$company->name)

@section('content')
<div class="flex gap-2 mb-4">
    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline btn-sm">← العودة</a>
    <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline">تعديل</a>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
    <div class="card">
        <div class="card-header">🔑 مفاتيح API</div>

        <form action="{{ route('admin.companies.generate-key', $company) }}" method="POST" style="display:flex; gap:10px; margin-bottom:16px;">
            @csrf
            <input type="text" name="name" placeholder="اسم المفتاح (اختياري)" class="form-control" style="flex:1;">
            <button type="submit" class="btn btn-success">+ توليد مفتاح</button>
        </form>

        <div class="table-wrap">
            <table>
                <thead><tr><th>الاسم</th><th>المفتاح</th><th>آخر استخدام</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                    @forelse($company->apiKeys as $key)
                        <tr>
                            <td class="fw-700">{{ $key->name }}</td>
                            <td style="font-family:monospace;font-size:11px;direction:ltr;max-width:200px;overflow:hidden;text-overflow:ellipsis;">
                                {{ $key->revoked_at ? '••••••••' : $key->key }}
                            </td>
                            <td class="text-xs text-gray">{{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'لم يستخدم' }}</td>
                            <td>
                                @if($key->revoked_at)
                                    <span class="badge badge-red">ملغي</span>
                                @else
                                    <span class="badge badge-green">نشط</span>
                                @endif
                            </td>
                            <td>
                                @unless($key->revoked_at)
                                    <form action="{{ route('admin.companies.revoke-key', [$company, $key]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('إلغاء هذا المفتاح؟')">إلغاء</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">لا توجد مفاتيح API بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">🏢 معلومات الشركة</div>
        <table style="width:100%;">
            <tr><td class="text-gray" style="padding:8px 0;">الباقة</td><td class="fw-700" style="padding:8px 0;">
                <span class="badge {{ $company->plan === 'enterprise' ? 'badge-yellow' : ($company->plan === 'pro' ? 'badge-blue' : '') }}" style="{{ $company->plan === 'free' ? 'background:#F3F4F6;color:#6B7280;' : '' }}">{{ $company->plan }}</span>
            </td></tr>
            <tr><td class="text-gray" style="padding:8px 0;">الحالة</td><td class="fw-700" style="padding:8px 0;">
                <span class="badge {{ $company->status === 'active' ? 'badge-green' : 'badge-red' }}">{{ $company->status === 'active' ? 'نشط' : 'موقوف' }}</span>
            </td></tr>
            <tr><td class="text-gray" style="padding:8px 0;">البريد</td><td style="padding:8px 0;">{{ $company->email }}</td></tr>
            <tr><td class="text-gray" style="padding:8px 0;">الهاتف</td><td style="padding:8px 0;">{{ $company->phone ?? '—' }}</td></tr>
            <tr><td class="text-gray" style="padding:8px 0;">مسجل منذ</td><td class="text-sm" style="padding:8px 0;">{{ $company->created_at->format('Y-m-d') }}</td></tr>
        </table>
    </div>
</div>
@endsection
