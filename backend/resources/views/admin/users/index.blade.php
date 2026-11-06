@extends('layouts.admin')
@section('title', 'المستخدمين')
@section('page_title', 'إدارة المستخدمين')

@section('content')
<div class="card">
    <div class="flex-between mb-4">
        <div class="card-header" style="margin:0;">👥 المستخدمين ({{ $users->count() }})</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>رقم المحمول</th>
                    <th>البريد</th>
                    <th>الدور</th>
                    <th>تاريخ التسجيل</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td><span class="fw-700">{{ $user->name }}</span></td>
                    <td class="text-sm text-gray">{{ $user->phone ?? '—' }}</td>
                    <td class="text-sm text-gray">{{ $user->email ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $user->role === 'admin' ? 'badge-red' : ($user->role === 'surveyor' ? 'badge-yellow' : 'badge-blue') }}">
                            {{ $user->role ?? 'user' }}
                        </span>
                    </td>
                    <td class="text-sm text-gray">{{ $user->created_at->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="empty-state">لا يوجد مستخدمين</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
