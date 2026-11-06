import 'dart:ui' as ui;
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart';
import '../../../core/network/map_cache_service.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../../core/di.dart' as di;

class MapScreen extends StatefulWidget {
  final bool isSearchMode;
  final double? initialLat;
  final double? initialLng;
  final int? eventId;

  const MapScreen({
    super.key,
    this.isSearchMode = false,
    this.initialLat,
    this.initialLng,
    this.eventId,
  });

  @override
  State<MapScreen> createState() => _MapScreenState();
}

class _MapScreenState extends State<MapScreen> {
  static const _defaultPosition = LatLng(15.5871, 32.5232);
  GoogleMapController? _mapController;
  final Set<Marker> _markers = {};
  final _searchController = TextEditingController();
  List<dynamic> _searchResults = [];
  bool _showSearchResults = false;
  String _activeFilter = 'الكل';
  List<dynamic> _availableLayers = [];
  bool _loadingMarkers = false;
  bool _permissionDenied = false;
  LatLng? _userLocation;

  @override
  void initState() {
    super.initState();
    _loadCachedMarkers();
    _checkPermissionAndInit();
    _loadAvailableLayers();
    _searchController.addListener(_onSearchChanged);
  }

  Future<void> _loadCachedMarkers() async {
    final features = MapCacheService.getCachedMarkers();
    if (features.isNotEmpty) {
      final cachedMarkers = <Marker>{};
      for (final f in features) {
        final id = f['id'];
        final name = f['name'] ?? '';
        final layer = f['layer_name'] ?? '';
        final lat = double.tryParse(f['lat']?.toString() ?? '0') ?? 0.0;
        final lng = double.tryParse(f['lng']?.toString() ?? '0') ?? 0.0;
        if (lat != 0 && lng != 0) {
          cachedMarkers.add(Marker(
            markerId: MarkerId('cached_$id'),
            position: LatLng(lat, lng),
            icon: await _getMarkerIcon(layer),
            infoWindow: InfoWindow(title: name, snippet: '$layer (مخزن)'),
            onTap: () => context.push('/place/$id'),
          ));
        }
      }
      if (mounted) setState(() { _markers.addAll(cachedMarkers); });
    }
  }

  Future<void> _checkPermissionAndInit() async {
    if (widget.initialLat != null && widget.initialLng != null) {
      final myLoc = LatLng(widget.initialLat!, widget.initialLng!);
      if (mounted) {
        setState(() { _permissionDenied = false; });
        _mapController?.animateCamera(CameraUpdate.newLatLngZoom(myLoc, 15));
        _loadMarkersAt(myLoc);
      }
      return;
    }
    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    if (permission == LocationPermission.deniedForever || permission == LocationPermission.denied) {
      if (mounted) setState(() => _permissionDenied = true);
      return;
    }
    try {
      final pos = await Geolocator.getCurrentPosition(locationSettings: const LocationSettings(accuracy: LocationAccuracy.high));
      final myLoc = LatLng(pos.latitude, pos.longitude);
      if (mounted) {
        setState(() { _userLocation = myLoc; _permissionDenied = false; });
        _mapController?.animateCamera(CameraUpdate.newLatLngZoom(myLoc, 15));
        _loadMarkersAt(myLoc);
      }
    } catch (e) {
      _loadMarkersAt(_defaultPosition);
    }
  }

  Future<void> _loadAvailableLayers() async {
    try {
      final api = di.sl<ApiClient>();
      final res = await api.getLayers();
      if (mounted) setState(() { _availableLayers = res['placeable_layers'] ?? []; });
    } catch (_) {}
  }

  @override
  void dispose() {
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    super.dispose();
  }

  void _onSearchChanged() {
    final q = _searchController.text.trim();
    if (q.length < 2) { setState(() { _searchResults = []; _showSearchResults = false; }); return; }
    _searchPlaces(q);
  }

  Future<void> _searchPlaces(String query) async {
    try {
      final api = di.sl<ApiClient>();
      final res = await api.dio.get('/mobile/search', queryParameters: {
        'query': query,
        if (_userLocation != null) 'lat': _userLocation!.latitude,
        if (_userLocation != null) 'lng': _userLocation!.longitude,
      });
      final results = (res.data['results'] as List?) ?? [];
      if (mounted) setState(() { _searchResults = results; _showSearchResults = true; });
    } catch (_) {}
  }

  Future<BitmapDescriptor> _getMarkerIcon(String layer) async {
    final ui.PictureRecorder pictureRecorder = ui.PictureRecorder();
    final Canvas canvas = Canvas(pictureRecorder);
    const double size = 120.0;
    Color color = AppColors.navy;
    IconData iconData = Icons.place;

    switch (layer) {
      case 'المطاعم': color = Colors.orange; iconData = Icons.restaurant; break;
      case 'المساجد': color = Colors.cyan; iconData = Icons.mosque; break;
      case 'الصيدليات': color = Colors.red; iconData = Icons.local_pharmacy; break;
      case 'المستشفيات': color = Colors.red; iconData = Icons.local_hospital; break;
      case 'المدارس': color = Colors.blue; iconData = Icons.school; break;
      case 'ميادين': color = Colors.green; iconData = Icons.park; break;
      case 'الأحياء':
      case 'القرى والمدن': color = Colors.teal; iconData = Icons.map; break;
      default: color = AppColors.gold; iconData = Icons.place;
    }

    final Paint paint = Paint()..color = color;
    canvas.drawCircle(const Offset(size / 2, size / 2), size / 2, paint);
    paint.color = Colors.white;
    canvas.drawCircle(const Offset(size / 2, size / 2), size / 2.2, paint);
    paint.color = color;
    canvas.drawCircle(const Offset(size / 2, size / 2), size / 2.5, paint);
    TextPainter textPainter = TextPainter(textDirection: TextDirection.ltr);
    textPainter.text = TextSpan(text: String.fromCharCode(iconData.codePoint), style: TextStyle(fontSize: size / 2.2, fontFamily: iconData.fontFamily, package: iconData.fontPackage, color: Colors.white));
    textPainter.layout();
    textPainter.paint(canvas, Offset(size / 2 - textPainter.width / 2, size / 2 - textPainter.height / 2));
    final ui.Image image = await pictureRecorder.endRecording().toImage(size.toInt(), size.toInt());
    final ByteData? byteData = await image.toByteData(format: ui.ImageByteFormat.png);
    return BitmapDescriptor.fromBytes(byteData!.buffer.asUint8List());
  }

  Future<void> _loadMarkersAt(LatLng pos) async {
    if (_loadingMarkers || _mapController == null) return;
    setState(() => _loadingMarkers = true);
    try {
      final api = di.sl<ApiClient>();
      final bounds = await _mapController!.getVisibleRegion();
      
      final res = await api.dio.get('/mobile/map-features', queryParameters: {
        'min_lat': bounds.southwest.latitude,
        'min_lng': bounds.southwest.longitude,
        'max_lat': bounds.northeast.latitude,
        'max_lng': bounds.northeast.longitude,
        if (_activeFilter != 'الكل') 'layer': _activeFilter,
      });

      final features = (res.data['features'] as List?) ?? [];
      if (features.isNotEmpty) {
        await MapCacheService.cacheMarkers(features);
      }

      final newMarkers = <Marker>{};
       for (final f in features) {
         final id = f['id'];
         final name = f['name'] ?? '';
         final layer = f['layer_name'] ?? '';
         final lat = double.tryParse(f['lat']?.toString() ?? '0') ?? 0.0;
         final lng = double.tryParse(f['lng']?.toString() ?? '0') ?? 0.0;

         if (lat != 0 && lng != 0) {
           newMarkers.add(Marker(
             markerId: MarkerId(id),
             position: LatLng(lat, lng),
             icon: await _getMarkerIcon(layer),
             infoWindow: InfoWindow(title: name, snippet: layer),
             onTap: () => context.push('/place/$id'),
           ));
         }
       }
      if (mounted) setState(() { _markers.clear(); _markers.addAll(newMarkers); _loadingMarkers = false; });
    } catch (_) {
      if (mounted) setState(() => _loadingMarkers = false);
    }
  }

  void _showLayerFilter() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          const Center(child: SizedBox(width: 40, height: 4, child: DecoratedBox(decoration: BoxDecoration(color: Color(0xFFE5E7EB), borderRadius: BorderRadius.all(Radius.circular(2)))))),
          const SizedBox(height: 16),
          const Text('تصفية حسب نوع المعلم', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          Expanded(
            child: SingleChildScrollView(
              child: Wrap(spacing: 8, runSpacing: 8, textDirection: TextDirection.rtl,
                children: [
                  'الكل',
                  ..._availableLayers.map((l) => l['name'] as String),
                ].map((l) {
                  final selected = l == _activeFilter;
                  return FilterChip(
                    label: Text(l, style: const TextStyle(fontSize: 12)), selected: selected,
                    onSelected: (_) { setState(() => _activeFilter = l); Navigator.pop(ctx); _loadMarkersAt(_userLocation ?? _defaultPosition); },
                    selectedColor: AppColors.navy, checkmarkColor: Colors.white,
                    labelStyle: TextStyle(color: selected ? Colors.white : AppColors.textGray, fontWeight: selected ? FontWeight.w600 : FontWeight.normal),
                    backgroundColor: Colors.white, side: const BorderSide(color: Color(0xFFE5E7EB)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  );
                }).toList(),
              ),
            ),
          ),
        ]),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_permissionDenied) return _buildPermissionError();
    return Scaffold(
      body: Stack(
        children: [
          GoogleMap(
            initialCameraPosition: CameraPosition(target: _userLocation ?? _defaultPosition, zoom: 13),
            myLocationEnabled: true, myLocationButtonEnabled: false, mapType: MapType.normal, markers: _markers,
            onMapCreated: (c) => _mapController = c,
            onCameraIdle: () async {
              if (_mapController == null) return;
              final region = await _mapController!.getVisibleRegion();
              final center = LatLng((region.northeast.latitude + region.southwest.latitude) / 2, (region.northeast.longitude + region.southwest.longitude) / 2);
              _loadMarkersAt(center);
            },
          ),
          Positioned(top: MediaQuery.of(context).padding.top + 8, left: 16, right: 16,
            child: Column(children: [
              Container(height: 48, decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 8, offset: const Offset(0, 2))]),
                child: TextField(controller: _searchController, textDirection: TextDirection.rtl,
                  decoration: InputDecoration(hintText: 'ابحث عن منطقة أو مكان...', hintTextDirection: TextDirection.rtl, prefixIcon: const Icon(Icons.search, color: Color(0xFF6B7280)), suffixIcon: _searchController.text.isNotEmpty ? IconButton(icon: const Icon(Icons.close, size: 18), onPressed: () { _searchController.clear(); setState(() { _searchResults = []; _showSearchResults = false; }); }) : null, border: InputBorder.none, contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12)),
                ),
              ),
              if (_showSearchResults && _searchResults.isNotEmpty)
                Container(margin: const EdgeInsets.only(top: 4), constraints: const BoxConstraints(maxHeight: 300), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 8)]),
                  child: ListView.builder(shrinkWrap: true, itemCount: _searchResults.length,
                    itemBuilder: (ctx, i) {
                      final item = _searchResults[i];
                      return ListTile(dense: true, leading: const Icon(Icons.location_on_outlined, size: 18, color: AppColors.navy), title: Text(item['name'] ?? '', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)), subtitle: Text('${item['layer_name']}', style: const TextStyle(fontSize: 11)), onTap: () { final lat = item['lat']; final lng = item['lng']; _mapController?.animateCamera(CameraUpdate.newLatLngZoom(LatLng(lat, lng), 16)); setState(() { _showSearchResults = false; }); });
                    },
                  ),
                ),
            ]),
          ),
          Positioned(bottom: 120, right: 16, child: FloatingActionButton.small(heroTag: 'location', backgroundColor: Colors.white, onPressed: _checkPermissionAndInit, child: const Icon(Icons.my_location, color: AppColors.navy))),
          Positioned(bottom: 70, right: 16, child: FloatingActionButton.small(heroTag: 'filter', backgroundColor: Colors.white, onPressed: _showLayerFilter, child: const Icon(Icons.tune, color: AppColors.navy))),
          if (_activeFilter != 'الكل')
            Positioned(top: MediaQuery.of(context).padding.top + 64, right: 16,
              child: Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4), decoration: BoxDecoration(color: AppColors.navy, borderRadius: BorderRadius.circular(20)),
                child: Row(mainAxisSize: MainAxisSize.min, children: [Text(_activeFilter, style: const TextStyle(color: Colors.white, fontSize: 12)), const SizedBox(width: 4), GestureDetector(onTap: () { setState(() => _activeFilter = 'الكل'); _loadMarkersAt(_userLocation ?? _defaultPosition); }, child: const Icon(Icons.close, color: Colors.white, size: 14))]),
              )),
        ],
      ),
    );
  }

  Widget _buildPermissionError() {
    return Scaffold(body: Center(child: Padding(padding: const EdgeInsets.all(32), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [const Icon(Icons.location_off_outlined, size: 80, color: AppColors.error), const SizedBox(height: 24), const Text('صلاحية الموقع إلزامية', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.textDark)), const SizedBox(height: 12), const Text('يرجى تفعيل خدمة تحديد الموقع والسماح للتطبيق بالوصول إليها لكي تتمكن من استخدامه.', textAlign: TextAlign.center, style: TextStyle(color: AppColors.textGray)), const SizedBox(height: 32), ElevatedButton(onPressed: _checkPermissionAndInit, child: const Text('محاولة مرة أخرى')), TextButton(onPressed: () => openAppSettings(), child: const Text('فتح إعدادات النظام'))]))));
  }
}
