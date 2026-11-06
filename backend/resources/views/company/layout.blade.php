<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'بوابة الشركات') - نعمرها صح</title>
    <!-- Fluent UI Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Segoe UI', 'Tajawal', 'sans-serif'] 
                    },
                    colors: { 
                        fluent: {
                            blue: '#0078d4',
                            blueDark: '#106ebe',
                            bg: '#faf9f8',
                            surface: '#ffffff',
                            text: '#323130',
                            textSecondary: '#605e5c',
                            border: '#edebe9',
                            hover: '#f3f2f1',
                            navBg: '#ffffff',
                            headerBg: '#0f172a' /* Darker header for enterprise feel */
                        }
                    },
                    boxShadow: {
                        'fluent': '0 1.6px 3.6px 0 rgba(0,0,0,0.132), 0 0.3px 0.9px 0 rgba(0,0,0,0.108)',
                        'fluent-nav': 'rgba(0, 0, 0, 0.1) 0px 1px 2px 0px'
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', 'Tajawal', sans-serif; margin: 0; background: #faf9f8; color: #323130; }
        
        /* Microsoft Header / Topbar */
        .global-header {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            height: 48px;
            background-color: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            z-index: 50;
        }

        .global-header .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            font-weight: 600;
        }
        
        .global-header .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 13px;
        }

        .api-key-badge {
            background-color: rgba(255,255,255,0.1);
            padding: 4px 8px;
            border-radius: 2px;
            font-family: monospace;
            font-size: 12px;
            direction: ltr;
        }

        /* Fluent Sidebar */
        .sidebar { 
            position: fixed; 
            right: 0; 
            top: 48px; 
            width: 250px; 
            height: calc(100vh - 48px); 
            background: #ffffff; 
            border-left: 1px solid #edebe9;
            display: flex; 
            flex-direction: column; 
            z-index: 10; 
        }

        .sidebar-header {
            padding: 24px 16px;
            border-bottom: 1px solid #edebe9;
        }
        
        .sidebar-header h2 {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 4px 0;
            color: #323130;
        }

        .sidebar-header .plan-badge {
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 2px;
            background: #e1dfdd;
            color: #323130;
            font-weight: 600;
            display: inline-block;
        }

        .sidebar nav { 
            flex: 1; 
            padding: 12px 0; 
            overflow-y: auto;
        }

        .sidebar nav a { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            padding: 10px 20px; 
            color: #323130; 
            text-decoration: none; 
            font-size: 14px; 
            transition: all 0.1s; 
            border-right: 3px solid transparent;
        }

        .sidebar nav a:hover { 
            background: #f3f2f1; 
        }

        .sidebar nav a.active { 
            background: #f3f2f1; 
            border-right-color: #0078d4;
            font-weight: 600;
        }

        .sidebar nav a.active span {
            color: #0078d4;
        }

        .sidebar .logout { 
            color: #a4262c; 
            text-decoration: none; 
            font-size: 14px; 
            padding: 16px 20px; 
            border-top: 1px solid #edebe9; 
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar .logout:hover { background: #fde7e9; }

        /* Main Content */
        .main-content { 
            margin-right: 250px; 
            margin-top: 48px;
            padding: 32px 40px; 
            min-height: calc(100vh - 48px); 
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-header h1 { 
            font-size: 24px; 
            font-weight: 600; 
            color: #323130; 
            margin: 0; 
        }

        /* Fluent Cards */
        .card { 
            background: #ffffff; 
            border-radius: 4px; 
            padding: 24px; 
            box-shadow: 0 1.6px 3.6px 0 rgba(0,0,0,0.132), 0 0.3px 0.9px 0 rgba(0,0,0,0.108); 
            margin-bottom: 24px;
        }

        /* Fluent Forms */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; color: #323130; margin-bottom: 6px; }
        .form-control { 
            width: 100%; 
            padding: 8px 12px; 
            border: 1px solid #8a8886; 
            border-bottom: 2px solid #8a8886;
            border-radius: 2px; 
            font-family: inherit; 
            font-size: 14px; 
            box-sizing: border-box; 
            transition: all 0.1s;
        }
        .form-control:focus {
            border-color: #0078d4;
            border-bottom-color: #0078d4;
            outline: none;
        }

        /* Fluent Buttons */
        .btn { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            padding: 6px 20px; 
            border-radius: 2px; 
            font-family: inherit; 
            font-size: 14px; 
            font-weight: 600; 
            border: none; 
            cursor: pointer; 
            transition: background 0.1s;
        }
        .btn-primary { background: #0078d4; color: white; }
        .btn-primary:hover { background: #106ebe; }
        .btn-primary:active { background: #005a9e; }
        
        .btn-outline { background: transparent; border: 1px solid #8a8886; color: #323130; }
        .btn-outline:hover { background: #f3f2f1; }

        @media (max-width: 768px) { 
            .sidebar { width: 0; transform: translateX(100%); transition: transform 0.2s; } 
            .main-content { margin-right: 0; padding: 20px; } 
            .global-header { padding: 0 12px; }
        }
    </style>
    @yield('styles')
</head>
<body>
    
    <!-- Top Header (Microsoft 365 Style) -->
    <header class="global-header">
        <div class="brand">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <span>نعمرها صح - بوابة المؤسسات</span>
        </div>
        <div class="user-info">
            <span class="api-key-badge" title="مفتاح API الخاص بك">{{ $apiKey->key }}</span>
            <div style="width: 32px; height: 32px; background: #0078d4; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px;">
                {{ mb_substr($apiKey->company->name, 0, 1) }}
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>{{ $apiKey->company->name }}</h2>
            <span class="plan-badge">{{ ucfirst($apiKey->company->plan) }}</span>
        </div>
        <nav>
            <a href="{{ route('company.dashboard') }}" class="{{ Request::routeIs('company.dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>لوحة المعلومات</span>
            </a>
            <a href="{{ route('company.code-examples') }}" class="{{ Request::routeIs('company.code-examples') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                <span>أمثلة التكامل</span>
            </a>
            <a href="{{ route('company.segments') }}" class="{{ Request::routeIs('company.segments*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>حالات الاستخدام</span>
            </a>
            <a href="{{ route('company.my-locations') }}" class="{{ Request::routeIs('company.my-locations') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>إدارة الفروع</span>
            </a>
            <a href="{{ route('company.widget.autocomplete') }}" class="{{ Request::routeIs('company.widget.*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>البحث الجغرافي</span>
            </a>
            <a href="/api/documentation" target="_blank">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>توثيق المطورين</span>
            </a>
        </nav>
        <a href="{{ route('company.logout') }}" class="logout">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            تسجيل خروج
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>@yield('page_title', 'لوحة المعلومات')</h1>
        </div>
        
        @if(session('success'))
            <div style="background-color: #dff6dd; color: #107c10; padding: 12px 16px; margin-bottom: 24px; border-radius: 2px; display:flex; align-items:center; gap:8px;">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M14.854 4.146a.5.5 0 010 .708l-8 8a.5.5 0 01-.708 0l-4-4a.5.5 0 01.708-.708L6.5 11.793l7.646-7.647a.5.5 0 01.708 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
