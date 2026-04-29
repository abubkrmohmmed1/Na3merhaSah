@extends('layouts.admin')

@section('title', 'عرض الخريطة')
@section('page_title', 'عرض الخريطة')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<style>
    .map-container {
        width: 100%;
        height: 75vh;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        background-color: white;
        padding: 10px;
    }
    #fullMap { width: 100%; height: 100%; border-radius: 12px; z-index: 1; }
</style>
@endsection

@section('content')
    <div class="map-container" style="position: relative;">
        <!-- Stats Overlay (omitted for brevity, keep the previous one) -->
        <div style="position: absolute; top: 20px; right: 20px; z-index: 1000; display: flex; gap: 10px; background: rgba(255, 255, 255, 0.9); padding: 15px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); backdrop-filter: blur(8px);">
            <div style="text-align: center; padding: 0 10px;">
                <div style="font-size: 10px; color: #718096; font-weight: 700;">البلاغات</div>
                <div style="font-size: 18px; font-weight: 800; color: #1A284D;">{{ $summary['total'] }}</div>
            </div>
            <div style="text-align: center; padding: 0 10px; border-right: 1px solid #E5E7EB;">
                <div style="font-size: 10px; color: #D69E2E; font-weight: 700;">الرضا</div>
                <div style="font-size: 18px; font-weight: 800; color: #D69E2E;">{{ $summary['avg_rating'] }} ★</div>
            </div>
        </div>

        <div id="fullMap"></div>
    </div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('fullMap').setView([15.6545, 32.4831], 12);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CARTO'
        }).addTo(map);

        const categoryColors = {
            1: '#3B82F6', // مياه
            2: '#F59E0B', // كهرباء
            3: '#6B7280', // طرق
            4: '#78350F', // صرف صحي
            5: '#10B981', // مباني
            6: '#EF4444'  // طوارئ
        };

        const categoryNames = {
            1: 'مياه', 2: 'كهرباء', 3: 'طرق', 4: 'صرف صحي', 5: 'مباني', 6: 'طوارئ'
        };

        // 1. Plot Registered Addresses (Small gray circles)
        var addrPoints = @json($allAddresses);
        addrPoints.forEach(function(addr) {
            L.circleMarker([addr.lat, addr.lng], {
                radius: 3,
                fillColor: "#CBD5E0",
                color: "#A0AEC0",
                weight: 1,
                opacity: 0.5,
                fillOpacity: 0.3
            }).addTo(map).bindPopup(addr.address_str);
        });

        // 2. Plot Reports with Clusters
        var reportPoints = @json($reportPoints);
        var clusters = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                var childCount = cluster.getChildCount();
                var c = ' marker-cluster-';
                if (childCount < 10) c += 'small';
                else if (childCount < 100) c += 'medium';
                else c += 'large';

                return new L.DivIcon({ 
                    html: '<div><span>' + childCount + '</span></div>', 
                    className: 'marker-cluster' + c, 
                    iconSize: new L.Point(40, 40) 
                });
            }
        });

        reportPoints.forEach(function(point) {
            var color = categoryColors[point.category_id] || '#4B7DF3';
            
            var marker = L.circleMarker([point.lat, point.lng], {
                radius: 8,
                fillColor: color,
                color: "#FFFFFF",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            });

            var popupContent = `
                <div style="font-family: 'Cairo', sans-serif; text-align: right; direction: rtl; min-width: 150px;">
                    <div style="color: ${color}; font-weight: 800; font-size: 14px; margin-bottom: 5px;">
                        ${categoryNames[point.category_id] || 'بلاغ عام'}
                    </div>
                    <p style="margin: 0 0 5px 0; font-size: 12px;"><strong>الموقع:</strong> ${point.address}</p>
                    <p style="margin: 0 0 10px 0; font-size: 11px;">الحالة: ${point.status}</p>
                    <a href="/admin/reports/${point.id}" style="background: ${color}; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; display: block; text-align: center; font-size: 11px;">عرض التفاصيل</a>
                </div>
            `;
            marker.bindPopup(popupContent);
            clusters.addLayer(marker);
        });

        map.addLayer(clusters);
    });
</script>
@endsection
