@extends('layouts.admin')

@section('title', 'إدارة المستخدمين')

@section('content')
    <div class="header" style="box-shadow: none; margin-bottom: 0; padding: 0;">
        <h1>إدارة المستخدمين</h1>
        <p>آخر تحديث: <span id="last-updated"></span></p>
    </div>

    <div class="section">
        <h2>قائمة المستخدمين</h2>
        @if (!empty($users))
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>تاريخ التسجيل</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ substr($user->id, 0, 5) }}</td>
                            <td>{{ $user->name ?? 'غير معروف' }}</td>
                            <td>{{ $user->email ?? 'N/A' }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>{{ $user->created_at ? $user->created_at->format('Y-m-d') : 'غير معروف' }}</td>
                            <td class="action-buttons">
                                <a href="#">عرض</a>
                                <a href="#">تعديل</a>
                                <a href="#">حذف</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($users->hasPages())
                <div style="padding: 20px; display: flex; justify-content: center;">
                    {{ $users->links() }}
                </div>
            @endif
        @else
            <p>لا توجد بيانات مستخدمين متاحة.</p>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('last-updated').innerText = new Date().toLocaleString('ar-EG', {
                year: 'numeric', month: 'long', day: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
        });
    </script>
@endsection
