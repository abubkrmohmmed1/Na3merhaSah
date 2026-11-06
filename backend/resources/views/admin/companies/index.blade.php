@extends('layouts.admin')
@section('title', 'الشركات')
@section('page_title', 'إدارة الشركات (B2B)')

@section('content')
<div class="flex-between mb-4">
    <div></div>
    <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">+ إضافة شركة</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>الاسم</th><th>البريد</th><th>الخطة</th><th>المفاتيح</th><th>الحالة</th><th>تاريخ التسجيل</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                    <tr>
                        <td class="fw-700">{{ $company->name }}</td>
                        <td class="text-sm text-gray">{{ $company->email }}</td>
                        <td>
                            <span class="badge {{ $company->plan === 'enterprise' ? 'badge-yellow' : ($company->plan === 'pro' ? 'badge-blue' : 'badge') }}" style="{{ $company->plan === 'free' ? 'background:#F3F4F6;color:#6B7280;' : '' }}">
                                {{ $company->plan }}
                            </span>
                        </td>
                        <td class="text-sm text-gray">{{ $company->apiKeys->count() }}</td>
                        <td>
                            <span class="badge {{ $company->status === 'active' ? 'badge-green' : 'badge-red' }}">
                                {{ $company->status === 'active' ? 'نشط' : 'موقوف' }}
                            </span>
                        </td>
                        <td class="text-xs text-gray">{{ $company->created_at->format('Y-m-d') }}</td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-outline">عرض</a>
                                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline">تعديل</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">لا توجد شركات مسجلة بعد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
