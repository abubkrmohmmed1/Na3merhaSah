@extends('layouts.admin')
@section('title', 'إضافة شركة')
@section('page_title', 'إضافة شركة جديدة')

@section('content')
<div class="card" style="max-width:600px;">
    <form action="{{ route('admin.companies.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>اسم الشركة</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
            @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
        </div>
        <div class="form-group">
            <label>كلمة المرور الافتراضية</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Webhook URL (للإشعارات)</label>
            <input type="url" name="webhook_url" value="{{ old('webhook_url') }}" class="form-control">
        </div>
        <div class="form-group">
            <label>Sudapass ID (للجهات الحكومية)</label>
            <input type="text" name="sudapass_id" value="{{ old('sudapass_id') }}" class="form-control">
        </div>
        <div class="form-group">
            <label>الباقة</label>
            <select name="plan" class="form-control" required>
                <option value="free">Free — 100 طلب/يوم</option>
                <option value="pro">Pro — 10,000 طلب/يوم</option>
                <option value="enterprise">Enterprise — غير محدود</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
    </form>
</div>
@endsection
