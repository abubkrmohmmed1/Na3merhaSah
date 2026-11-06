<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - بوابة الشركات | نعمرها صح</title>
    <!-- Fluent UI Typography & Styling Inspiration -->
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            /* Authentic Microsoft / Fluent UI Background */
            font-family: 'Segoe UI', 'Tajawal', sans-serif;
            margin: 0;
            background-color: #f3f2f1; /* Fluent UI Gray 10 */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle at top right, #e1dfdd 0%, #f3f2f1 100%);
        }
        
        /* Microsoft Account Login Card Style */
        .login-card {
            background: #ffffff;
            padding: 44px;
            width: 440px;
            max-width: 90%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1), 0 8px 16px rgba(0,0,0,0.1); /* Elevated depth */
            text-align: right;
            border-radius: 4px; /* Subtle Fluent UI curve */
            box-sizing: border-box;
        }

        .login-card img { 
            width: 48px; 
            margin-bottom: 24px; 
            border-radius: 4px;
        }

        .login-card h1 { 
            font-size: 24px; 
            color: #1b1b1b; 
            margin: 0 0 12px 0; 
            font-weight: 600; 
        }

        .login-card p { 
            color: #1b1b1b; 
            font-size: 15px; 
            margin: 0 0 32px 0; 
        }

        /* Fluent UI Input Field */
        .input-group {
            margin-bottom: 24px;
            position: relative;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #8a8886;
            border-bottom: 2px solid #8a8886;
            border-radius: 2px;
            font-family: 'Segoe UI', 'Tajawal', sans-serif;
            font-size: 15px;
            box-sizing: border-box;
            background-color: #ffffff;
            transition: border-color 0.1s ease-in-out;
            direction: ltr; /* Keeping LTR for API keys */
            text-align: left;
        }

        input:focus { 
            border-color: #0078d4; /* Microsoft Blue */
            border-bottom-color: #0078d4;
            outline: none; 
        }

        input::placeholder {
            color: #605e5c;
        }

        /* Fluent UI Primary Button */
        .actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 32px;
        }

        button {
            padding: 8px 32px;
            background: #0078d4;
            color: #ffffff;
            border: none;
            border-radius: 2px;
            font-family: 'Segoe UI', 'Tajawal', sans-serif;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.1s ease;
        }

        button:hover { 
            background: #106ebe; 
        }

        button:active {
            background: #005a9e;
        }

        /* Error Message styled like Fluent UI MessageBar */
        .error { 
            background: #fde7e9; 
            color: #a4262c; /* Fluent Red 10 */
            padding: 12px 16px; 
            margin-bottom: 24px; 
            font-size: 13px; 
            display: flex;
            align-items: center;
            gap: 8px;
            border-left: 3px solid #a4262c;
        }

        .footer {
            margin-top: 24px;
            font-size: 13px;
            color: #605e5c;
        }

        .key-icon {
            position: absolute;
            right: -30px;
            top: 50%;
            transform: translateY(-50%);
            color: #605e5c;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="{{ asset('images/logo.jpeg') }}" alt="نعمرها صح">
        <h1>تسجيل الدخول</h1>
        <p>بوابة الشركات والمؤسسات</p>

        @if(session('error'))
            <div class="error">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 15A7 7 0 108 1a7 7 0 000 14zm-.5-4.5h1v1h-1v-1zm0-7h1v6h-1v-6z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('company.authenticate') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="كلمة المرور" required>
            </div>
            
            <div class="actions">
                <button type="submit">تسجيل الدخول</button>
            </div>
        </form>
    </div>
</body>
</html>
