@extends('layouts.admin')

@section('title', 'تسجيل دخول المسؤول')

@section('content')
<style>
    body {
        background-color: #f9fafb; /* Ensure body background is consistent */
    }
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 80px); /* Adjust based on topbar height */
        padding: 20px;
    }
    .login-box {
        background-color: #ffffff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        width: 100%;
        max-width: 400px;
        text-align: center;
    }
    .login-box h1 {
        font-size: 28px;
        color: #0b1a3d; /* Dark blue from sidebar */
        margin-bottom: 30px;
    }
    .form-group {
        margin-bottom: 20px;
        text-align: right;
    }
    .form-group label {
        display: block;
        font-size: 14px;
        color: #4b5563;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 16px;
        font-family: 'Cairo', sans-serif;
        box-sizing: border-box; /* Include padding in width */
    }
    .form-group input:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }
    .login-button {
        width: 100%;
        padding: 12px 15px;
        background-color: #3b82f6; /* Primary blue */
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
        font-family: 'Cairo', sans-serif;
    }
    .login-button:hover {
        background-color: #2563eb; /* Darker blue on hover */
    }
    .alert {
        background-color: #fee2e2;
        color: #dc2626;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #dc2626;
        text-align: right;
    }
</style>

<div class="login-container">
    <div class="login-box">
        <h1>تسجيل دخول المسؤول</h1>
        
        @if ($errors->any())
            <div class="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-button">تسجيل الدخول</button>
        </form>
    </div>
</div>
@endsection
