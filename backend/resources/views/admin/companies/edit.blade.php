@extends('layouts.admin')
@section('title', 'تعديل الشركة')
@section('page_title', 'تعديل: '.$company->name)

@section('content')
<div class="card" style="max-width:600px;">
    <form action="{{ route('admin.companies.update', $company) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>اسم الشركة</label>
            <input type="text" name="name" value="{{ old('name', $company->name) }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" value="{{ old('email', $company->email) }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="form-control">
        </div>
        <div class="form-group">
            <label>الباقة</label>
            <select name="plan" class="form-control" required>
                <option value="free" {{ $company->plan === 'free' ? 'selected' : '' }}>Free</option>
                <option value="pro" {{ $company->plan === 'pro' ? 'selected' : '' }}>Pro</option>
                <option value="enterprise" {{ $company->plan === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
            </select>
        </div>
        <div class="form-group">
            <label>الحالة</label>
            <select name="status" class="form-control" required>
                <option value="active" {{ $company->status === 'active' ? 'selected' : '' }}>نشط</option>
                <option value="suspended" {{ $company->status === 'suspended' ? 'selected' : '' }}>موقوف</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
    </form>
</div>
@endsection
