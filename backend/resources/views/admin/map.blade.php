@extends('layouts.admin')

@section('title', 'عرض الخريطة')
@section('page_title', 'عرض الخريطة')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<style>
    .content { padding: 0 !important; }
    .map-page {
        display: flex;
        height: calc(100vh - 56px);
        gap: 0;
    }
    .hierarchy-panel {
        width: 280px;
        min-width: 280px;
        background: white;
        border-left: 1px solid #E5E7EB;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .hierarchy-header {
        padding: 16px;
        border-bottom: 1px solid #E5E7EB;
        background: #F9FAFB;
    }
    .hierarchy-header h3 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #1F2937;
    }
    .hierarchy-header p {
        margin: 4px 0 0 0;
        font-size: 11px;
        color: #6B7280;
    }
    .hierarchy-tree {
        flex: 1;
        overflow-y: auto;
        padding: 8px;
    }
    .tree-item { margin-bottom: 4px; }
    .tree-item-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 10px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s;
        background: #F3F4F6;
    }
    .tree-item-header:hover { background: #E5E7EB; }
    .tree-item-header.active { background: #EEF2FF; color: #4F46E5; }
    .tree-item-header .name {
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tree-item-header .icon {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tree-item-header .chevron {
        font-size: 10px;
        transition: transform 0.2s;
        color: #9CA3AF;
    }
    .tree-item.expanded .chevron { transform: rotate(90deg); }
    .tree-children {
        display: none;
        padding-right: 20px;
        margin-top: 4px;
        border-right: 2px solid #E5E7EB;
        margin-right: 12px;
        padding-right: 8px;
    }
    .tree-item.expanded .tree-children { display: block; }
    .child-item {
        padding: 6px 10px;
        margin: 2px 0;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        color: #4B5563;
        transition: all 0.15s;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .child-item:hover { background: #F3F4F6; color: #1F2937; }
    .child-item.active { background: #EEF2FF; color: #4F46E5; }
    .child-item .child-count {
        margin-right: auto;
        font-size: 10px;
        color: #9CA3AF;
        background: #E5E7EB;
        padding: 2px 6px;
        border-radius: 10px;
    }
    .child-item.has-children { font-weight: 600; }
    .child-children { display: none; padding-right: 16px; margin-top: 2px; }
    .child-item.expanded + .child-children { display: block; }
    .subchild-item {
        padding: 4px 8px;
        margin: 1px 0;
        font-size: 11px;
        color: #6B7280;
        border-radius: 4px;
        cursor: pointer;
    }
    .subchild-item:hover { background: #F9FAFB; color: #374151; }

    .map-container { flex: 1; position: relative; }
    #fullMap { width: 100%; height: 100%; }
    .map-controls {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .map-control-box {
        background: rgba(255, 255, 255, 0.95);
        padding: 10px 14px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        backdrop-filter: blur(8px);
    }
    .map-control-box label {
        font-size: 12px;
        font-weight: 700;
        color: #1A284D;
        display: block;
        margin-bottom: 6px;
    }
    .map-control-box select {
        width: 200px;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #E5E7EB;
        font-size: 12px;
        outline: none;
    }
    .feature-tooltip {
        position: absolute;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        padding: 16px;
        z-index: 1001;
        min-width: 220px;
        max-width: 280px;
        display: none;
    }
    .feature-tooltip.show { display: block; }
    .feature-tooltip h4 { margin: 0 0 4px 0; font-size: 14px; font-weight: 700; color: #1F2937; }
    .feature-tooltip .layer-badge {
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 20px;
        display: inline-block;
        margin-bottom: 8px;
    }
    .feature-tooltip .stats {
        display: flex;
        gap: 8px;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #E5E7EB;
    }
    .feature-tooltip .stat { text-align: center; flex: 1; }
    .feature-tooltip .stat-value { font-size: 16px; font-weight: 700; color: #1A284D; }
    .feature-tooltip .stat-label { font-size: 10px; color: #6B7280; }
    .feature-tooltip .zoom-btn {
        display: block;
        width: 100%;
        margin-top: 10px;
        padding: 8px;
        background: #1A284D;
        color: white;
        text-align: center;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: none;
    }
    .feature-tooltip .zoom-btn:hover { background: #2a3d6e; }
    .close-tooltip {
        position: absolute;
        top: 8px;
        left: 8px;
        background: none;
        border: none;
        cursor: pointer;
        color: #9CA3AF;
        font-size: 18px;
    }
    .fullscreen-btn {
        position: absolute;
        top: 60px;
        right: 12px;
        z-index: 1000;
        background: white;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 8px 12px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        font-size: 13px;
        font-weight: 600;
        color: #1A284D;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .fullscreen-btn:hover { background: #F3F4F6; }
    .fullscreen-btn svg { width: 16px; height: 16px; }
    .map-legend {
        position: absolute;
        bottom: 20px;
        left: 20px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        padding: 8px 12px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        font-size: 11px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        backdrop-filter: blur(4px);
        max-width: 280px;
    }
    .map-legend h4 { margin: 0; font-size: 11px; font-weight: 700; color: #1A284D; flex-basis: 100%; }
    .legend-item { display: flex; align-items: center; gap: 5px; }
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        border: 1px solid #D1D5DB;
        flex-shrink: 0;
    }
    .legend-item .swatch { border-radius: 50%; }
    .legend-divider { width: 1px; height: 16px; background: #E5E7EB; }
</style>
@endsection

@section('content')
<div class="map-page">
    <div class="hierarchy-panel">
        <div class="hierarchy-header">
            <h3>الحدود الإدارية</h3>
            <p>انقر لاستكشاف المناطق</p>
            <select id="adminLevelFilter" style="margin-top:8px;width:100%;padding:6px 8px;border-radius:6px;border:1px solid #E5E7EB;font-size:12px;">
                <option value="all">كل المستويات</option>
                <option value="states">الولايات فقط</option>
                <option value="localities">المحليات فقط</option>
                <option value="neighborhoods">الأحياء فقط</option>
                <option value="villages">القرى فقط</option>
            </select>
        </div>
        <div class="hierarchy-tree" id="hierarchyTree"></div>
    </div>

    <div class="map-container" id="mapContainer">
        <div class="map-controls">
            <div class="map-control-box">
                <label>الحدود الإدارية:</label>
                <select id="layerFilter">
                    <option value="all">كل المستويات</option>
                    <optgroup label="الحدود الإدارية">
                        <option value="boundary_states">الولايات</option>
                        <option value="boundary_localities">المحليات</option>
                        <option value="boundary_neighborhoods">الأحياء</option>
                    </optgroup>
                    <optgroup label="القرى">
                        <option value="boundary_villages">القرى والمدن</option>
                    </optgroup>
                </select>
                <select id="localityFilter" style="margin-top:6px; display:none;">
                    <option value="">اختر محلية...</option>
                </select>
                <label style="margin-top:8px;display:block;">الخدمات:</label>
                <select id="serviceLayerFilter">
                    <option value="">كل الخدمات</option>
                    @foreach($layers->whereNotIn('name', ['الولايات', 'المحليات', 'الأحياء', 'الحدود الإدارية', 'القرى والمدن']) as $layer)
                        <option value="layer_{{$layer->id}}">{{ $layer->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button class="fullscreen-btn" id="fullscreenBtn" onclick="toggleFullscreen()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m9-5V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m9 5l-5-5m5 5v-4m0 4h-4"/>
            </svg>
            <span id="fullscreenLabel">ملء الشاشة</span>
        </button>

        <div id="fullMap"></div>

        <div class="map-legend" id="mapLegend">
            <h4>الخريطة</h4>
            <div class="legend-item">
                <div class="legend-color" style="background: #4F46E5;"></div>
                <span>الولايات</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #0891B2;"></div>
                <span>المحليات</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #059669;"></div>
                <span>الأحياء</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #6366F1;"></div>
                <span>القرى</span>
            </div>
            <div class="legend-divider"></div>
            <div class="legend-item">
                <div class="legend-color swatch" style="background: #CBD5E0;"></div>
                <span>العناوين</span>
            </div>
            <div class="legend-divider"></div>
            <div class="legend-item">
                <div class="legend-color" style="background: #F59E0B;"></div>
                <span>بانتظار المراجعة</span>
            </div>
        </div>

        <div class="feature-tooltip" id="featureTooltip">
            <button class="close-tooltip" onclick="hideTooltip()">&times;</button>
            <span class="layer-badge" id="tooltipLayer"></span>
            <h4 id="tooltipTitle"></h4>
            <div id="tooltipChildren"></div>
            <div class="stats" id="tooltipStats"></div>
            <div id="tooltipStatus" style="margin-top:6px;"></div>
            <button class="zoom-btn" id="zoomToFeature">تكبير على الخريطة</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mapContainer = document.getElementById('mapContainer');
    if (mapContainer) {
        var loadingDiv = document.createElement('div');
        loadingDiv.id = 'mapLoadingOverlay';
        loadingDiv.innerHTML = '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:9999;background:white;padding:24px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.2);text-align:center;font-family:Tajawal,sans-serif;"><h3 style="margin:0 0 16px;color:#1E40AF;">جاري تحميل البيانات المكانية...</h3><div style="width:40px;height:40px;border:4px solid #DBEAFE;border-top:4px solid #1E40AF;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto;"></div><style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style></div>';
        mapContainer.appendChild(loadingDiv);
        mapContainer.style.position = 'relative';
    }

    fetch('{{ route('admin.map.data') }}')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (document.getElementById('mapLoadingOverlay')) {
                document.getElementById('mapLoadingOverlay').remove();
            }

            var hierarchy = data.hierarchy;
            var adminBoundaries = data.adminBoundaries;
            var layerPoints = data.layerPoints;
            var layersData = data.layers;
            var addrPoints = data.allAddresses;
            var villages = data.villages || [];
            var pendingCounts = data.pendingCounts || {};

    var layerColors = {
        'الولايات': '#4F46E5',
        'المحليات': '#0891B2',
        'الأحياء': '#059669',
        'القرى والمدن': '#6366F1',
        'الصرافات الآلية': '#06B6D4',
        'البنوك': '#4338CA',
        'الصيدليات': '#10B981',
        'المستشفيات': '#EF4444',
        'المخابز': '#D97706',
        'المطاعم': '#F97316',
        'محطات الوقود': '#DC2626',
        'المدارس': '#F59E0B',
        'Default': '#10B981'
    };

    var map = L.map('fullMap').setView([15.6545, 32.4831], 7);

    // Tile layers
    var lightTiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CARTO', maxZoom: 19
    });
    var darkTiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CARTO', maxZoom: 19
    });
    var currentTiles = lightTiles.addTo(map);

    // Map style toggle
    var mapStyle = 'light';
    L.Control.MapStyleToggle = L.Control.extend({
        onAdd: function(map) {
            var btn = L.DomUtil.create('button', 'map-style-btn');
            btn.innerHTML = '🌙';
            btn.title = 'تغيير نمط الخريطة';
            btn.style.cssText = 'width:36px;height:36px;border-radius:8px;border:2px solid rgba(0,0,0,0.1);background:white;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.15);margin-top:4px;';
            L.DomEvent.on(btn, 'click', function() {
                if (mapStyle === 'light') {
                    map.removeLayer(lightTiles);
                    darkTiles.addTo(map);
                    mapStyle = 'dark';
                    btn.innerHTML = '☀️';
                } else {
                    map.removeLayer(darkTiles);
                    lightTiles.addTo(map);
                    mapStyle = 'light';
                    btn.innerHTML = '🌙';
                }
            });
            return btn;
        }
    });
    new L.Control.MapStyleToggle({ position: 'topleft' }).addTo(map);

    map.createPane('statesPane');
    map.getPane('statesPane').style.zIndex = 200;
    map.getPane('statesPane').style.pointerEvents = 'auto';
    map.createPane('localitiesPane');
    map.getPane('localitiesPane').style.zIndex = 250;
    map.createPane('neighborhoodsPane');
    map.getPane('neighborhoodsPane').style.zIndex = 300;
    map.createPane('villagesPane');
    map.getPane('villagesPane').style.zIndex = 350;

    var boundaryLayers = {
        states: L.featureGroup(),
        localities: L.featureGroup(),
        neighborhoods: L.featureGroup(),
        villages: L.featureGroup()
    };

    var layerGroups = {};
    layersData.forEach(function(layer) {
        layerGroups['layer_' + layer.id] = L.featureGroup();
        layerGroups['layer_' + layer.id + '_clusters'] = L.markerClusterGroup();
        layerGroups['layer_' + layer.id].addLayer(layerGroups['layer_' + layer.id + '_clusters']);
    });

    var addrLayerGroup = L.layerGroup();
    var allFeatures = {};
    var highlightedChildrenLayer = L.featureGroup();
    var parentHighlightLayer = L.featureGroup();

    addrPoints.forEach(function(addr) {
        var marker = L.circleMarker([addr.lat, addr.lng], {
            radius: 3,
            fillColor: "#CBD5E0",
            color: "#A0AEC0",
            weight: 1,
            opacity: 0.5,
            fillOpacity: 0.3
        }).bindPopup(addr.address_str);
        addrLayerGroup.addLayer(marker);
    });
    addrLayerGroup.addTo(map);

    var localityColors = {};
    var localityNames = [...new Set(adminBoundaries
        .filter(function(f) { return f.properties.layer === 'المحليات'; })
        .map(function(f) { return f.properties.name; })
    )];
    var localityPalette = ['#059669', '#0891B2', '#7C3AED', '#DC2626', '#EA580C', '#16A34A', '#2563EB', '#9333EA', '#DB2777', '#0D9488'];
    localityNames.forEach(function(name, i) {
        localityColors[name] = localityPalette[i % localityPalette.length];
    });

    function getLocalityForNeighborhood(neighborId) {
        var neigh = adminBoundaries.find(function(f) { return f.properties.id === neighborId; });
        if (!neigh || !neigh.properties.parent_id) return null;
        var locality = adminBoundaries.find(function(f) { return f.properties.id === neigh.properties.parent_id; });
        return locality ? locality.properties.name : null;
    }

    function getChildren(parentId) {
        return adminBoundaries.filter(function(f) { return f.properties.parent_id === parentId; });
    }

    function highlightChildren(featureId, childLayerName) {
        highlightedChildrenLayer.clearLayers();
        parentHighlightLayer.clearLayers();
        var parentFeature = adminBoundaries.find(function(f) { return f.properties.id === featureId; });
        if (!parentFeature) return;
        var children = getChildren(featureId);
        var childColor = childLayerName === 'المحليات' ? '#0891B2' : '#059669';

        if (parentFeature && parentFeature.geometry) {
            var parentStyle = {
                color: '#FFD700',
                weight: 5,
                opacity: 1,
                fillOpacity: 0.25,
                fillColor: '#FFD700'
            };
            var parentLayer = L.geoJSON(parentFeature, { style: parentStyle });
            parentHighlightLayer.addLayer(parentLayer);
            parentHighlightLayer.addTo(map);
        }

        children.forEach(function(child) {
            if (!child.geometry) return;
            var style = {
                color: childColor,
                weight: 3,
                opacity: 0.9,
                fillOpacity: 0.15,
                fillColor: childColor,
                dashArray: '6, 3'
            };
            var childLayer = L.geoJSON(child, { style: style });
            childLayer.bindTooltip(child.properties.name + ' - ' + (parentFeature.properties.name || ''), {
                direction: 'top',
                offset: [0, -5],
                permanent: false
            });
            highlightedChildrenLayer.addLayer(childLayer);
        });

        if (highlightedChildrenLayer.getLayers().length > 0) {
            highlightedChildrenLayer.addTo(map);
        }

        var allBounds = L.featureGroup([parentHighlightLayer, highlightedChildrenLayer]).getBounds();
        if (allBounds.isValid()) {
            map.fitBounds(allBounds, { padding: [50, 50], maxZoom: 13 });
        }
    }

    function clearHighlights() {
        highlightedChildrenLayer.clearLayers();
        parentHighlightLayer.clearLayers();
    }

    adminBoundaries.forEach(function(feature) {
        var layerName = feature.properties.layer;
        var color = layerColors[layerName] || '#6B7280';
        if (layerName === 'الأحياء' && feature.properties.parent_id) {
            var localityName = getLocalityForNeighborhood(feature.properties.id);
            if (localityName && localityColors[localityName]) {
                color = localityColors[localityName];
            }
        }
        var pane = layerName === 'الولايات' ? 'statesPane' :
                   layerName === 'المحليات' ? 'localitiesPane' : 'neighborhoodsPane';
        var weight = layerName === 'الولايات' ? 3 :
                     layerName === 'المحليات' ? 2 : 1;
        var fillOpacity = layerName === 'الولايات' ? 0.15 :
                          layerName === 'المحليات' ? 0.1 : 0.05;

        var defaultStyle = {
            color: color,
            weight: weight,
            opacity: 0.7,
            fillOpacity: fillOpacity,
            fillColor: color
        };
        var hoverStyle = {
            color: color,
            weight: weight + 2,
            opacity: 1,
            fillOpacity: Math.min(fillOpacity + 0.2, 0.5),
            fillColor: color
        };

        var geoLayer = null;
        var geomType = feature.geometry ? feature.geometry.type : null;

        if (geomType === 'Point' || geomType === 'MultiPoint') {
            var coords = geomType === 'Point' ? feature.geometry.coordinates :
                         (feature.geometry.coordinates && feature.geometry.coordinates[0] ? feature.geometry.coordinates[0] : null);
            if (coords) {
                geoLayer = L.circleMarker([coords[1], coords[0]], {
                    radius: layerName === 'الولايات' ? 8 : 6,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.7
                });
                geoLayer.bindTooltip(feature.properties.name, { direction: 'top', offset: [0, -10] });
            }
        } else if (geomType === 'LineString' || geomType === 'MultiLineString') {
            geoLayer = L.geoJSON(feature, {
                pane: pane,
                style: { color: color, weight: 2, opacity: 0.8, dashArray: '5, 5' }
            });
        } else if (geomType === 'Polygon' || geomType === 'MultiPolygon') {
            geoLayer = L.geoJSON(feature, { pane: pane, style: defaultStyle });
            geoLayer.on('mouseover', function(e) {
                geoLayer.setStyle(hoverStyle);
                geoLayer.openTooltip(feature.properties.name, { direction: 'top', offset: [0, -10] });
            });
            geoLayer.on('mouseout', function(e) {
                geoLayer.setStyle(defaultStyle);
                geoLayer.closeTooltip();
            });
        }

        if (geoLayer) {
            var onClickHandler = function() {
                clearHighlights();
                showFeatureTooltip(feature.properties.id, feature.properties.name, layerName);
                if (layerName === 'الولايات') {
                    highlightChildren(feature.properties.id, 'المحليات');
                } else if (layerName === 'المحليات') {
                    highlightChildren(feature.properties.id, 'الأحياء');
                }
            };
            geoLayer.on('click', onClickHandler);

            allFeatures[feature.properties.id] = { layer: geoLayer, feature: feature };

            if (layerName === 'الولايات') {
                boundaryLayers.states.addLayer(geoLayer);
            } else if (layerName === 'المحليات') {
                boundaryLayers.localities.addLayer(geoLayer);

                var pendingKey = feature.properties.id;
                if (pendingCounts[pendingKey] && pendingCounts[pendingKey].total > 0) {
                    var center = null;
                    if (feature.geometry && feature.geometry.type === 'Point') {
                        center = [feature.geometry.coordinates[1], feature.geometry.coordinates[0]];
                    } else if (geoLayer && geoLayer.getBounds) {
                        center = geoLayer.getBounds().getCenter();
                    }
                    if (center) {
                        var badgeLayer = L.circleMarker(center, {
                            radius: 10,
                            fillColor: '#F59E0B',
                            color: '#fff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        });
                        badgeLayer.bindTooltip(
                            pendingCounts[pendingKey].total + ' بانتظار المراجعة\n' +
                            '(' + pendingCounts[pendingKey].addresses + ' عناوين، ' +
                            pendingCounts[pendingKey].neighborhoods + ' أحياء، ' +
                            pendingCounts[pendingKey].villages + ' قرى)',
                            { direction: 'top', offset: [0, -12], permanent: false }
                        );
                        boundaryLayers.localities.addLayer(badgeLayer);
                    }
                }
            } else if (layerName === 'الأحياء') {
                boundaryLayers.neighborhoods.addLayer(geoLayer);
            }
        }
    });

    villages.forEach(function(feature) {
        var geomType = feature.geometry ? feature.geometry.type : null;
        var geoLayer = null;
        var villageColor = '#6366F1';

        if (geomType === 'Point' || geomType === 'MultiPoint') {
            var coords = geomType === 'Point' ? feature.geometry.coordinates :
                         (feature.geometry.coordinates && feature.geometry.coordinates[0] ? feature.geometry.coordinates[0] : null);
            if (coords) {
                geoLayer = L.circleMarker([coords[1], coords[0]], {
                    radius: 5,
                    fillColor: villageColor,
                    color: '#fff',
                    weight: 1.5,
                    opacity: 1,
                    fillOpacity: 0.8
                });
                geoLayer.bindTooltip(feature.properties.name, { direction: 'top', offset: [0, -8], permanent: false });
            }
        } else if (geomType === 'Polygon' || geomType === 'MultiPolygon') {
            geoLayer = L.geoJSON(feature, {
                pane: 'villagesPane',
                style: {
                    color: villageColor,
                    weight: 1,
                    opacity: 0.7,
                    fillOpacity: 0.15,
                    fillColor: villageColor
                }
            });
        } else if (geomType === 'LineString') {
            geoLayer = L.geoJSON(feature, {
                pane: 'villagesPane',
                style: { color: villageColor, weight: 1.5, opacity: 0.8 }
            });
        }

        if (geoLayer) {
            allFeatures[feature.properties.id] = { layer: geoLayer, feature: feature };
            boundaryLayers.villages.addLayer(geoLayer);
        }
    });

    boundaryLayers.states.addTo(map);
    boundaryLayers.localities.addTo(map);
    boundaryLayers.neighborhoods.addTo(map);
    boundaryLayers.villages.addTo(map);

    layerPoints.forEach(function(point) {
        var layerObj = layersData.find(function(l) { return l.id === point.layer_id; });
        var layerName = layerObj ? layerObj.name : 'Unknown';
        if (['الولايات', 'المحليات', 'الأحياء', 'الحدود الإدارية', 'القرى والمدن'].indexOf(layerName) !== -1) return;

        var color = layerColors[layerName] || layerColors['Default'];
        var geojsonData = JSON.parse(point.geojson);

        if (geojsonData.type === 'Point') {
            var marker = L.circleMarker([point.lat, point.lng], {
                radius: 5,
                fillColor: color,
                color: "#FFFFFF",
                weight: 1,
                opacity: 1,
                fillOpacity: 0.8
            }).bindPopup('<b>' + point.name + '</b><br>' + layerName);

            layerGroups['layer_' + point.layer_id + '_clusters'].addLayer(marker);
            allFeatures[point.id] = { marker: marker, feature: point };
        }
    });

    layersData.forEach(function(layer) {
        if (['الولايات', 'المحليات', 'الأحياء', 'الحدود الإدارية', 'القرى والمدن'].indexOf(layer.name) === -1) {
            map.addLayer(layerGroups['layer_' + layer.id]);
        }
    });

    var adminLevelFilter = 'all';
    document.getElementById('adminLevelFilter').addEventListener('change', function(e) {
        adminLevelFilter = e.target.value;
        renderHierarchyTree();
    });

    function renderHierarchyTree() {
        var container = document.getElementById('hierarchyTree');
        var html = '';

        hierarchy.forEach(function(state) {
            var hoodCount = state.children ? state.children.reduce(function(sum, loc) {
                return sum + (loc.children ? loc.children.length : 0);
            }, 0) : 0;

            if (adminLevelFilter === 'neighborhoods' || adminLevelFilter === 'villages') {
                html += '<div style="padding:8px 10px;font-size:11px;color:#9CA3AF;">اختر ولاية ثم محلية لعرض الأحياء</div>';
                container.innerHTML = html;
                return;
            }

            var showStates = adminLevelFilter === 'all' || adminLevelFilter === 'states';
            var showLocalities = adminLevelFilter === 'all' || adminLevelFilter === 'localities';
            var showNeighborhoods = adminLevelFilter === 'all' || adminLevelFilter === 'neighborhoods';
            var showVillages = adminLevelFilter === 'all' || adminLevelFilter === 'villages';

            html += '<div class="tree-item" data-id="' + state.id + '">';
            html += '<div class="tree-item-header" onclick="toggleTreeItem(this, \'' + state.id + '\')">';
            html += '<div class="name">';
            html += '<div class="icon" style="background: #EEF2FF; color: #4F46E5;">';
            html += '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7"/></svg>';
            html += '</div>' + state.name + '</div>';
            html += (showLocalities || showNeighborhoods || showVillages ? '<span class="chevron">▶</span>' : '');
            html += '</div>';
            html += '<div class="tree-children">';

            if (state.children && (showLocalities || showNeighborhoods)) {
                state.children.forEach(function(locality) {
                    var locHoodCount = locality.children ? locality.children.length : 0;
                    var showLocItem = showLocalities || showVillages || (showNeighborhoods && locality.children && locality.children.length > 0);

                    if (showLocItem) {
                        html += '<div class="child-item has-children" data-id="' + locality.id + '" onclick="toggleChildItem(this, \'' + locality.id + '\')">';
                        html += '<svg style="width:12px;height:12px;color:#0891B2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>';
                        html += locality.name;
                        if (showNeighborhoods && locHoodCount > 0) {
                            html += '<span class="child-count">' + locHoodCount + '</span>';
                        }
                        html += '</div>';

                        if (showNeighborhoods && locality.children && locality.children.length > 0) {
                            html += '<div class="child-children">';
                            locality.children.forEach(function(hood) {
                                html += '<div class="subchild-item" data-id="' + hood.id + '" onclick="zoomToFeature(\'' + hood.id + '\')">' + hood.name + '</div>';
                            });
                            html += '</div>';
                        }

                        if (showVillages && locality.villages && locality.villages.length > 0) {
                            html += '<div class="child-children" style="padding-right:8px;">';
                            locality.villages.forEach(function(village) {
                                html += '<div class="subchild-item" data-id="' + village.id + '" onclick="zoomToFeature(\'' + village.id + '\')" style="font-size:10px;opacity:0.7;">&#9679; ' + village.name + '</div>';
                            });
                            html += '</div>';
                        } else if (showLocItem) {
                            html += '<div class="child-children"></div>';
                        }
                    }
                });
            }

            html += '</div></div>';
        });

        container.innerHTML = html;
    }
    window.toggleTreeItem = function(header, id) {
        var item = header.parentElement;
        item.classList.toggle('expanded');
        header.classList.toggle('active');
        highlightFeature(id);

        var feature = adminBoundaries.find(function(f) { return f.properties.id === id; });
        if (feature) {
            clearHighlights();
            var layerName = feature.properties.layer;
            if (layerName === 'الولايات') {
                highlightChildren(id, 'المحليات');
                var stateFeature = adminBoundaries.find(function(f) { return f.properties.id === id; });
                if (stateFeature && stateFeature.geometry) {
                    var bounds = L.geoJSON(stateFeature).getBounds();
                    if (bounds.isValid()) map.fitBounds(bounds, { padding: [30, 30], maxZoom: 10 });
                }
                document.getElementById('layerFilter').value = 'all';
                document.getElementById('localityFilter').style.display = 'none';
                document.getElementById('localityFilter').value = '';
                activeBoundary = 'all';
                activeLocality = null;
                applyAllFilters();
            } else if (layerName === 'المحليات') {
                highlightChildren(id, 'الأحياء');
            }
        }
    };

    window.toggleChildItem = function(item, id) {
        item.classList.toggle('expanded');
        highlightFeature(id);
        var feature = adminBoundaries.find(function(f) { return f.properties.id === id; });
        if (feature) {
            clearHighlights();
            var layerName = feature.properties.layer;
            if (layerName === 'المحليات') {
                highlightChildren(id, 'الأحياء');
                var locFeature = adminBoundaries.find(function(f) { return f.properties.id === id; });
                if (locFeature && locFeature.geometry) {
                    var bounds = L.geoJSON(locFeature).getBounds();
                    if (bounds.isValid()) map.fitBounds(bounds, { padding: [30, 30], maxZoom: 12 });
                }
                document.getElementById('layerFilter').value = 'boundary_neighborhoods';
                document.getElementById('localityFilter').style.display = 'block';
                rebuildLocalityDropdown();
                document.getElementById('localityFilter').value = id;
                activeBoundary = 'boundary_neighborhoods';
                activeLocality = id;
                applyAllFilters();
            }
        }
    };

    window.highlightFeature = function(id) {
        document.querySelectorAll('.tree-item-header.active, .child-item.active').forEach(function(el) {
            el.classList.remove('active');
        });
        var treeItem = document.querySelector('[data-id="' + id + '"]');
        if (treeItem) {
            var header = treeItem.querySelector('.tree-item-header');
            if (header) header.classList.add('active');
            var childHeader = treeItem.querySelector('.child-item');
            if (childHeader) childHeader.classList.add('active');
        }
    };

    window.showFeatureTooltip = function(id, name, layer) {
        var tooltip = document.getElementById('featureTooltip');
        var title = document.getElementById('tooltipTitle');
        var layerBadge = document.getElementById('tooltipLayer');
        var stats = document.getElementById('tooltipStats');

        title.textContent = name;
        layerBadge.textContent = layer;
        layerBadge.style.background = layerColors[layer] ? layerColors[layer] + '20' : '#E5E7EB';
        layerBadge.style.color = layerColors[layer] || '#6B7280';

        var childrenHtml = '';
        var hierarchyItem = findInHierarchy(id);
        if (hierarchyItem && hierarchyItem.children) {
            if (hierarchyItem.children.length > 0) {
                childrenHtml = '<p style="margin:0;font-size:11px;color:#6B7280;">يحتوي على ' + hierarchyItem.children.length + ' مناطق فرعية</p>';
            }
        }
        document.getElementById('tooltipChildren').innerHTML = childrenHtml;

        stats.innerHTML = '<div class="stat"><div class="stat-value">' + (hierarchyItem ? (hierarchyItem.children ? hierarchyItem.children.length : '-') : '-') + '</div><div class="stat-label">فرعي</div></div>';

        tooltip.classList.add('show');
        tooltip.dataset.featureId = id;
    };

    window.findInHierarchy = function(id) {
        for (var i = 0; i < hierarchy.length; i++) {
            var state = hierarchy[i];
            if (state.id === id) return state;
            if (state.children) {
                for (var j = 0; j < state.children.length; j++) {
                    var loc = state.children[j];
                    if (loc.id === id) return loc;
                    if (loc.children) {
                        for (var k = 0; k < loc.children.length; k++) {
                            var hood = loc.children[k];
                            if (hood.id === id) return hood;
                        }
                    }
                }
            }
        }
        return null;
    };

    window.hideTooltip = function() {
        document.getElementById('featureTooltip').classList.remove('show');
    };

    window.zoomToFeature = function(id) {
        var featureData = allFeatures[id];
        if (featureData) {
            if (featureData.layer) {
                map.fitBounds(featureData.layer.getBounds(), { padding: [50, 50] });
            } else if (featureData.marker) {
                map.setView(featureData.marker.getLatLng(), 15);
            }
        }
        highlightFeature(id);
        hideTooltip();
    };

    document.getElementById('zoomToFeature').addEventListener('click', function() {
        var id = this.parentElement.dataset.featureId;
        zoomToFeature(id);
        var feature = adminBoundaries.find(function(f) { return f.properties.id === id; });
        if (feature) {
            clearHighlights();
            var layerName = feature.properties.layer;
            if (layerName === 'الولايات') {
                highlightChildren(id, 'المحليات');
            } else if (layerName === 'المحليات') {
                highlightChildren(id, 'الأحياء');
            }
        }
    });

    var activeBoundary = 'all';
    var activeLocality = null;
    var activeService = null;

    document.getElementById('layerFilter').addEventListener('change', function(e) {
        activeBoundary = e.target.value;
        activeService = null;
        document.getElementById('serviceLayerFilter').value = '';

        var locFilter = document.getElementById('localityFilter');
        if (activeBoundary === 'boundary_neighborhoods' || activeBoundary === 'boundary_villages') {
            locFilter.style.display = 'block';
            rebuildLocalityDropdown();
        } else {
            locFilter.style.display = 'none';
            activeLocality = null;
            applyAllFilters();
        }
    });

    document.getElementById('localityFilter').addEventListener('change', function(e) {
        activeLocality = e.target.value || null;
        applyAllFilters();
    });

    document.getElementById('serviceLayerFilter').addEventListener('change', function(e) {
        activeService = e.target.value || null;
        applyAllFilters();
    });

    function rebuildLocalityDropdown() {
        var locFilter = document.getElementById('localityFilter');
        var localities = adminBoundaries
            .filter(function(f) { return f.properties.layer === 'المحليات'; })
            .map(function(f) { return { id: f.properties.id, name: f.properties.name }; });

        localities.sort(function(a, b) { return a.name.localeCompare(b.name, 'ar'); });

        var uniqueLoc = [];
        var seen = {};
        localities.forEach(function(l) {
            if (!seen[l.id]) {
                seen[l.id] = true;
                uniqueLoc.push(l);
            }
        });

        var html = '<option value="">كل المحليات</option>';
        uniqueLoc.forEach(function(loc) {
            html += '<option value="' + loc.id + '">' + loc.name + '</option>';
        });
        locFilter.innerHTML = html;
    }

    function applyAllFilters() {
        map.removeLayer(boundaryLayers.states);
        map.removeLayer(boundaryLayers.localities);
        map.removeLayer(boundaryLayers.neighborhoods);
        map.removeLayer(boundaryLayers.villages);

        layersData.forEach(function(layer) {
            map.removeLayer(layerGroups['layer_' + layer.id]);
        });

        map.removeLayer(addrLayerGroup);
        map.addLayer(addrLayerGroup);

        if (activeBoundary === 'all' || activeBoundary === 'boundary_states') {
            map.addLayer(boundaryLayers.states);
        }

        if (activeBoundary === 'all' || activeBoundary === 'boundary_localities') {
            if (!activeLocality) {
                map.addLayer(boundaryLayers.localities);
            } else {
                boundaryLayers.localities.clearLayers();
                var locFeature = adminBoundaries.find(function(f) { return f.properties.id === activeLocality; });
                if (locFeature && allFeatures[locFeature.properties.id]) {
                    boundaryLayers.localities.addLayer(allFeatures[locFeature.properties.id].layer);
                    boundaryLayers.localities.addTo(map);
                }
            }
        }

        if (activeBoundary === 'boundary_neighborhoods' || activeBoundary === 'all') {
            boundaryLayers.neighborhoods.clearLayers();
            if (activeLocality) {
                var children = getChildren(activeLocality);
                children.forEach(function(child) {
                    if (child.properties.id && allFeatures[child.properties.id]) {
                        boundaryLayers.neighborhoods.addLayer(allFeatures[child.properties.id].layer);
                    }
                });
                boundaryLayers.neighborhoods.addTo(map);
            } else {
                map.addLayer(boundaryLayers.neighborhoods);
            }
        }

        if (activeBoundary === 'boundary_villages') {
            boundaryLayers.villages.clearLayers();
            if (activeLocality) {
                villages.forEach(function(v) {
                    if (v.properties.parent_id === activeLocality && allFeatures[v.properties.id]) {
                        boundaryLayers.villages.addLayer(allFeatures[v.properties.id].layer);
                    }
                });
                var locFeature = adminBoundaries.find(function(f) { return f.properties.id === activeLocality; });
                if (locFeature && allFeatures[locFeature.properties.id]) {
                    boundaryLayers.localities.clearLayers();
                    boundaryLayers.localities.addLayer(allFeatures[locFeature.properties.id].layer);
                    boundaryLayers.localities.addTo(map);
                }
                boundaryLayers.villages.addTo(map);
                if (locFeature && locFeature.geometry) {
                    var bounds = L.geoJSON(locFeature).getBounds();
                    if (bounds.isValid()) map.fitBounds(bounds, { padding: [30, 30] });
                }
            } else {
                map.addLayer(boundaryLayers.villages);
            }
        }

        if (activeService && layerGroups[activeService]) {
            map.addLayer(layerGroups[activeService]);
        } else if (!activeService && activeBoundary === 'all') {
            layersData.forEach(function(layer) {
                if (['الولايات', 'المحليات', 'الأحياء', 'الحدود الإدارية', 'القرى والمدن'].indexOf(layer.name) === -1) {
                    map.addLayer(layerGroups['layer_' + layer.id]);
                }
            });
        }
    }

    window.toggleFullscreen = function() {
        var mapContainer = document.getElementById('mapContainer');
        var btn = document.getElementById('fullscreenBtn');
        var label = document.getElementById('fullscreenLabel');
        var legend = document.getElementById('mapLegend');

        if (!document.fullscreenElement) {
            mapContainer.requestFullscreen().catch(function(err) {
                console.warn('Fullscreen error:', err);
            });
            label.textContent = 'خروج';
            btn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg><span id="fullscreenLabel">خروج</span>';
            if (legend) legend.classList.add('in-fullscreen');
            mapContainer.classList.add('fullscreen-mode');
        } else {
            document.exitFullscreen();
            label.textContent = 'ملء الشاشة';
            btn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m9-5V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m9 5l-5-5m5 5v-4m0 4h-4"/></svg><span id="fullscreenLabel">ملء الشاشة</span>';
            if (legend) legend.classList.remove('in-fullscreen');
            mapContainer.classList.remove('fullscreen-mode');
        }
    };

    document.addEventListener('fullscreenchange', function() {
        var legend = document.getElementById('mapLegend');
        var mapContainer = document.getElementById('mapContainer');
        var btn = document.getElementById('fullscreenBtn');
        var label = document.getElementById('fullscreenLabel');

        if (!document.fullscreenElement) {
            if (legend) legend.classList.remove('in-fullscreen');
            if (mapContainer) mapContainer.classList.remove('fullscreen-mode');
            if (label) {
                label.textContent = 'ملء الشاشة';
                btn.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m9-5V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m9 5l-5-5m5 5v-4m0 4h-4"/></svg><span id="fullscreenLabel">ملء الشاشة</span>';
            }
        }
    });

    renderHierarchyTree();
        })
        .catch(error => {
            console.error('Error fetching map data:', error);
            if (document.getElementById('mapLoadingOverlay')) {
                document.getElementById('mapLoadingOverlay').innerHTML = '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:9999;background:#FEE2E2;padding:24px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.2);text-align:center;color:#991B1B;"><h3 style="margin:0 0 8px;">حدث خطأ أثناء تحميل البيانات</h3><p style="margin:0;">يرجى تحديث الصفحة والمحاولة مرة أخرى.</p></div>';
            }
        });
});
</script>
@endsection