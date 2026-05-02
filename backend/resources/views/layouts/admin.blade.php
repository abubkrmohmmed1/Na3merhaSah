<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة التحكم') - نعمرها صح</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        :root {
            --sidebar-bg: #1A284D;
            --main-bg: #F5F7FB;
            --primary-blue: #4B7DF3;
            --text-dark: #2B3674;
            --text-gray: #A3AED0;
            --white: #FFFFFF;
        }

        body {
            font-family: 'Cairo', sans-serif;
            margin: 0;
            background-color: var(--main-bg);
            display: flex;
            min-height: 100vh;
            color: var(--text-dark);
        }

        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: var(--white);
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
            padding-top: 30px;
            border-top-left-radius: 40px;
            border-bottom-left-radius: 40px;
            margin-left: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .sidebar .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 50px;
            gap: 10px;
        }

        .sidebar .logo-container img {
            width: 100px;
            height: auto;
            border-radius: 12px;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 80%;
        }

        .sidebar nav ul li {
            margin-bottom: 15px;
            text-align: center;
        }

        .sidebar nav ul li a {
            display: block;
            padding: 12px 20px;
            color: #A3AED0;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .sidebar nav ul li a:hover {
            color: var(--white);
        }

        .sidebar nav ul li a.active {
            background-color: var(--primary-blue);
            color: var(--white);
        }

        .main-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 20px 40px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .user-name {
            font-weight: 700;
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: var(--text-gray);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .topbar-right .page-title {
            color: var(--primary-blue);
            font-weight: 800;
            font-size: 20px;
        }

        .content {
            flex-grow: 1;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <img src="{{ asset('images/logo.jpeg') }}" alt="نعمرها صح Logo">
        </div>
        <nav>
            <ul>
                <li><a href="{{ route('admin.dashboard') }}" class="{{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">اللوحة الرئيسية</a></li>
                <li><a href="{{ route('admin.reports.index') }}" class="{{ Request::routeIs('admin.reports.*') ? 'active' : '' }}">عرض الشكاوى</a></li>
                <li><a href="{{ route('admin.map') }}" class="{{ Request::routeIs('admin.map') ? 'active' : '' }}">عرض الخريطة</a></li>
                <li><a href="{{ route('admin.entities.index') }}" class="{{ Request::routeIs('admin.entities.*') ? 'active' : '' }}">الجهات الحكومية</a></li>
                <li><a href="{{ route('admin.kpi') }}" class="{{ Request::routeIs('admin.kpi') ? 'active' : '' }}">مؤشرات الأداء</a></li>
                <li><a href="{{ route('admin.users.index') }}" class="{{ Request::routeIs('admin.users.*') ? 'active' : '' }}">المستخدمين</a></li>
                <li><a href="#">الضبط</a></li>
                <li style="margin-top: 50px;">
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" style="background:none;border:none;color:#A3AED0;font-size:16px;font-weight:600;font-family:'Cairo';cursor:pointer;width:100%;text-align:center;padding:12px 20px;border-radius:12px;transition:all 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#A3AED0'">خروج</button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>

    <div class="main-wrapper">
        <div class="topbar">
            <div class="topbar-right">
                <div class="page-title">@yield('page_title', 'اللوحة الرئيسية :')</div>
            </div>
            <div class="topbar-left">
                <div class="user-info">
                    <span class="user-name">{{ auth()->user()?->name ?? 'مستخدم' }}</span>
                    <span class="user-role">{{ auth()->user()?->role ?? 'مسؤول النظام' }}</span>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->name ?? 'Admin') }}&background=EAB308&color=fff" class="user-avatar" alt="Avatar">
            </div>
        </div>
        <div class="content">
            @if(session('success'))
                <div style="background-color: #10B981; color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.style.display='none';" style="background: none; border: none; color: white; cursor: pointer; font-size: 18px;">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div style="background-color: #EF4444; color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.style.display='none';" style="background: none; border: none; color: white; cursor: pointer; font-size: 18px;">&times;</button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
    @yield('scripts')
</body>
</html>