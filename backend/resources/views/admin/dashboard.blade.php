@extends('layouts.admin')

@section('title', 'اللوحة الرئيسية')
@section('page_title', 'اللوحة الرئيسية :')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        padding: 20px;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .stat-blue { background-color: #D3E2FF; color: #2B3674; }
    .stat-pink { background-color: #F8D5DA; color: #2B3674; }
    .stat-yellow { background-color: #FDE8C4; color: #2B3674; }
    .stat-green { background-color: #D1F0E0; color: #2B3674; }

    .stat-value {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 14px;
        font-weight: 600;
        color: #6B7280;
    }

    .section-title {
        color: #4B7DF3;
        font-weight: 800;
        font-size: 18px;
        margin-bottom: 20px;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .map-container {
        background-color: white;
        border-radius: 16px;
        padding: 10px;
        height: 300px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    .table-container {
        background-color: white;
        border-radius: 16px;
        padding: 0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
    }

    th {
        background-color: #F3F4F6;
        padding: 12px;
        font-size: 14px;
        color: #6B7280;
        font-weight: 700;
        border: 1px solid #E5E7EB;
    }

    td {
        padding: 12px;
        font-size: 14px;
        font-weight: 600;
        border: 1px solid #E5E7EB;
    }

    .priority-low { color: #10B981; }
    .priority-medium { color: #F59E0B; }
    .priority-high { color: #EF4444; }

    .top-header-map {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 12px;
        color: #6B7280;
    }
</style>
@endsection

@section('content')
    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <span class="stat-value">{{ number_format($stats['total']) }}</span>
            <span class="stat-label">إجمالي عدد الشكاوي</span>
        </div>
        <div class="stat-card stat-pink">
            <span class="stat-value">{{ number_format($stats['new']) }}</span>
            <span class="stat-label">شكاوي جديدة</span>
        </div>
        <div class="stat-card stat-yellow">
            <span class="stat-value">{{ number_format($stats['under_review']) }}</span>
            <span class="stat-label">قيد الدراسة</span>
        </div>
        <div class="stat-card stat-green">
            <span class="stat-value">{{ number_format($stats['completed']) }}</span>
            <span class="stat-label">مكتملة</span>
        </div>
    </div>

    <div class="section-title">الشكاوي الاخيرة</div>

    <div class="content-grid">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>رقم الشكوى</th>
                        <th>النوع</th>
                        <th>الموقع</th>
                        <th>الحالة</th>
                        <th>الاولوية</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReports as $report)
                    <tr>
                        <td>{{ substr($report->id, 0, 5) }}</td>
                        <td>{{ $report->category_id == 1 ? 'تسريب مياه' : 'مشكلة عامة' }}</td>
                        <td>{{ $report->address ? $report->address->digital_address : 'غير محدد' }}</td>
                        <td>{{ $report->status }}</td>
                        <td class="priority-high">عالي</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">لا توجد شكاوى حتى الآن</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="map-container">
            <div class="top-header-map">عرض الكل</div>
            <div id="dashboardMap" style="width:100%; height:100%; border-radius:12px; z-index: 1;"></div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Map
        var map = L.map('dashboardMap').setView([15.6545, 32.4831], 12); // Default to Khartoum/Omdurman area

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        var mapPoints = @json($mapPoints);
        var bounds = [];

        mapPoints.forEach(function(point) {
            var marker = L.marker([point.lat, point.lng]).addTo(map);
            marker.bindPopup("<b>الشكوى: " + point.id.substring(0, 5) + "</b><br>" + point.address);
            bounds.push([point.lat, point.lng]);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds);
        }
    });
</script>
@endsection