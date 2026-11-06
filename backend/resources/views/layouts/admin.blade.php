<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة التحكم') - نعمرها صح</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { cairo: ['Cairo', 'sans-serif'] },
                    colors: {
                        navy: { DEFAULT: '#1A284D', light: '#2B3D6B', dark: '#0F172A' },
                        gold: { DEFAULT: '#EAB308', light: '#FACC6B' },
                        brand: { blue: '#4B7DF3', bg: '#F0F2F5' },
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Cairo', 'Segoe UI', sans-serif; background:rgb(243, 244, 246); color: #1F2937; display: flex; min-height: 100vh; }
        .sidebar { position: fixed; right: 0; top: 0; width: 240px; height: 100vh; background: linear-gradient(180deg, #1A284D 0%, #0F172A 100%); color: white; display: flex; flex-direction: column; z-index: 100; box-shadow: 4px 0 20px rgba(0,0,0,0.08); }
        .sidebar .logo { padding: 28px 20px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .sidebar .logo img { width: 56px; height: 56px; border-radius: 14px; object-fit: cover; }
        .sidebar .logo h2 { font-size: 15px; font-weight: 800; margin-top: 8px; color: #fff; }
        .sidebar .logo span { font-size: 11px; color: rgba(255,255,255,0.5); }
        .sidebar nav { flex: 1; padding: 12px; overflow-y: auto; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; padding: 10px 14px; margin-bottom: 3px; color: rgba(255,255,255,0.55); text-decoration: none; font-size: 13px; font-weight: 600; border-radius: 10px; transition: all 0.2s; }
        .sidebar nav a:hover { background: rgba(255,255,255,0.08); color: white; }
        .sidebar nav a.active { background: #4B7DF3; color: white; box-shadow: 0 4px 12px rgba(75,125,243,0.3); }
        .sidebar nav a .nav-icon { width: 20px; text-align: center; font-size: 16px; }
        .sidebar .logout-section { padding: 14px 16px; border-top: 1px solid rgba(255,255,255,0.06); }
        .sidebar .logout-section button { width: 100%; padding: 10px; background: rgba(239,68,68,0.1); color: #FCA5A5; border: none; border-radius: 10px; font-family: 'Cairo'; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .sidebar .logout-section button:hover { background: rgba(239,68,68,0.2); }
        .main-content { margin-right: 240px; flex: 1; min-height: 100vh; }
        .topbar { background: white; padding: 16px 28px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #E5E7EB; position: sticky; top: 0; z-index: 50; }
        .content-area { padding: 24px 28px; }
        .card { background: white; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02); border: 1px solid #F3F4F6; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: white; border-radius: 14px; padding: 20px; border: 1px solid #F3F4F6; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: all 0.2s; }
        .stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); transform: translateY(-1px); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: right; padding: 12px 16px; font-size: 11px; font-weight: 700; color: #6B7280; border-bottom: 2px solid #E5E7EB; white-space: nowrap; }
        td { padding: 12px 16px; font-size: 13px; border-bottom: 1px solid #F3F4F6; }
        tr:hover td { background: #F9FAFB; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-green { background: #D1FAE5; color: #065F46; }
        .badge-red { background: #FEE2E2; color: #991B1B; }
        .badge-yellow { background: #FEF3C7; color: #92400E; }
        .badge-blue { background: #DBEAFE; color: #1E40AF; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 10px; font-family: 'Cairo'; font-size: 13px; font-weight: 700; border: none; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .btn-primary { background: #4B7DF3; color: white; }
        .btn-primary:hover { background: #3B6AE0; }
        .btn-success { background: #10B981; color: white; }
        .btn-outline { background: white; color: #6B7280; border: 1px solid #D1D5DB; }
        .btn-outline:hover { background: #F9FAFB; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .action-group { display: flex; gap: 6px; }
        .icon-btn { width: 32px; height: 32px; border-radius: 8px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.15s; }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid #D1D5DB; border-radius: 10px; font-family: 'Cairo'; font-size: 13px; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: #4B7DF3; box-shadow: 0 0 0 3px rgba(75,125,243,0.1); }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 6px; }
        .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 18px; font-weight: 600; font-size: 13px; display: flex; justify-content: space-between; align-items: center; }
        .alert-success { background: #D1FAE5; color: #065F46; }
        .tabs { display: flex; gap: 4px; padding: 4px; background: #F3F4F6; border-radius: 12px; margin-bottom: 20px; }
        .tabs a { flex: 1; padding: 10px 16px; text-align: center; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; color: #6B7280; transition: all 0.2s; }
        .tabs a.active { background: white; color: #1A284D; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 200; backdrop-filter: blur(2px); }
        .modal-overlay.show { display: flex; }
        .modal-box { background: white; border-radius: 16px; padding: 28px; width: 100%; max-width: 460px; margin: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
        .empty-state { padding: 48px 20px; text-align: center; color: #9CA3AF; }
        @media (max-width: 768px) { .sidebar { width: 64px; } .sidebar .logo h2, .sidebar .logo span, .sidebar nav a span { display: none; } .sidebar nav a { justify-content: center; padding: 12px; } .main-content { margin-right: 64px; } .content-area { padding: 16px; } }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="{{ asset('images/logo.jpeg') }}" alt="">
            <h2>نعمرها صح</h2>
            <span>لوحة الإدارة</span>
        </div>
        <nav>
            <a href="{{ route('admin.dashboard') }}" class="{{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">📊</span><span>الرئيسية</span>
            </a>
            <a href="{{ route('admin.kpi') }}" class="{{ Request::routeIs('admin.kpi') ? 'active' : '' }}">
                <span class="nav-icon">📈</span><span>مؤشرات الأداء</span>
            </a>
            <a href="{{ route('admin.map') }}" class="{{ Request::routeIs('admin.map') ? 'active' : '' }}">
                <span class="nav-icon">🗺️</span><span>الخريطة</span>
            </a>
            <a href="{{ route('admin.spatial.index') }}" class="{{ Request::routeIs('admin.spatial.*') ? 'active' : '' }}">
                <span class="nav-icon">📍</span><span>المعالم الجغرافية</span>
            </a>
            <a href="{{ route('admin.submissions.index') }}" class="{{ Request::routeIs('admin.submissions.*') ? 'active' : '' }}">
                <span class="nav-icon">📋</span><span>مراجعة التقديمات</span>
            </a>
            <a href="{{ route('admin.events.index') }}" class="{{ request()->is('admin/events*') ? 'active' : '' }}">
                <span class="nav-icon">📅</span><span>الفعاليات والأنشطة</span>
            </a>
            <a href="{{ route('admin.spatial.import') }}" class="{{ Request::routeIs('admin.spatial.import') ? 'active' : '' }}">
                <span class="nav-icon">📥</span><span>استيراد</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="{{ Request::routeIs('admin.users.*') ? 'active' : '' }}">
                <span class="nav-icon">👥</span><span>المستخدمين</span>
            </a>
            <a href="{{ route('admin.companies.index') }}" class="{{ Request::routeIs('admin.companies.*') ? 'active' : '' }}">
                <span class="nav-icon">🏢</span><span>الشركات B2B</span>
            </a>
            <a href="{{ route('admin.app.dashboard') }}" class="{{ Request::routeIs('admin.app.*') ? 'active' : '' }}">
                <span class="nav-icon">⚙️</span><span>إدارة التطبيق</span>
            </a>
        </nav>
        <div class="logout-section">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">🚪 تسجيل خروج</button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="text-lg font-extrabold" style="color:#1A284D;">@yield('page_title', 'لوحة التحكم')</div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div class="text-sm font-bold text-gray-700">{{ auth()->user()?->name ?? 'مستخدم' }}</div>
                    <div class="text-xs text-gray-400">{{ auth()->user()?->role ?? 'مسؤول' }}</div>
                </div>
                <div class="w-9 h-9 rounded-full bg-yellow-500 text-white flex items-center justify-center font-extrabold text-sm">
                    {{ mb_substr(auth()->user()?->name ?? 'A', 0, 1) }}
                </div>
            </div>
        </div>

        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success">
                    <span>{{ session('success') }}</span>
                    <button class="alert-close text-lg opacity-60 hover:opacity-100 bg-transparent border-none cursor-pointer text-inherit" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-success" style="background:#FEE2E2;color:#991B1B;">
                    <span>{{ session('error') }}</span>
                    <button class="alert-close text-lg opacity-60 hover:opacity-100 bg-transparent border-none cursor-pointer text-inherit" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            @endif
            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>
</html>
