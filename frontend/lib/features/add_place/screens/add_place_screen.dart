import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:geolocator/geolocator.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import 'package:image_picker/image_picker.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../../core/di.dart' as di;

class AddPlaceScreen extends StatefulWidget {
  final String? preSelectedLayer;
  final bool? startInPolygonMode;
  final bool isBoundaryChallenge;
  final bool forceWalkingMode;

  const AddPlaceScreen({
    super.key,
    this.preSelectedLayer,
    this.startInPolygonMode,
    this.isBoundaryChallenge = false,
    this.forceWalkingMode = false,
  });

  @override
  State<AddPlaceScreen> createState() => _AddPlaceScreenState();
}

class _AddPlaceScreenState extends State<AddPlaceScreen> {
  static const _initialPosition = LatLng(15.5871, 32.5232);
  LatLng? _selectedPosition;
  
  // Polygon drawing state
  final List<LatLng> _polygonPoints = [];
  bool _isPolygonMode = false;
  bool _isFreehandDrawing = false;
  bool _isWalkingGPS = false;
  StreamSubscription<Position>? _positionSubscription;
  GoogleMapController? _mapController;

  final _nameController = TextEditingController();
  final _descController = TextEditingController();
  final _newCategoryController = TextEditingController();
  
  String? _selectedLayerId;
  String _selectedLayerName = '';
  
  bool _loading = false;
  bool _forceAdd = false;
  bool _gettingLocation = true;
  List<dynamic> _availableLayers = [];
  String? _imagePath;
  final _picker = ImagePicker();

  // Icons for new categories
  final List<String> _iconOptions = ['📍', '🍴', '🍞', '☕', '💊', '🏥', '🏥', '🏦', '🏫', '⛽', '🏨', '👮', '🕌', '🛒', '🛒', '🛒', '🛒', '💪', '🌳', '🚌', '🅿️', '🔒', '🗺️', '🏙️'];
  String _selectedIcon = '📍';

  @override
  void initState() {
    super.initState();
    _getCurrentLocation();
    _loadLayers();
    if (widget.preSelectedLayer != null) {
      _selectedLayerName = widget.preSelectedLayer!;
    } else if (widget.isBoundaryChallenge) {
      _selectedLayerName = 'الأحياء';
    }
    if (widget.startInPolygonMode != null) {
      _isPolygonMode = widget.startInPolygonMode!;
    } else if (widget.isBoundaryChallenge) {
      _isPolygonMode = true;
    }
    if (widget.forceWalkingMode) {
      _isWalkingGPS = true;
      _startWalkingTracking();
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _descController.dispose();
    _newCategoryController.dispose();
    _positionSubscription?.cancel();
    super.dispose();
  }

  Future<void> _loadLayers() async {
    try {
      final api = di.sl<ApiClient>();
      final res = await api.getLayers();
      if (mounted) {
        setState(() {
          _availableLayers = res['placeable_layers'] ?? [];
          if (_availableLayers.isNotEmpty) {
            if (widget.preSelectedLayer == null) {
              _selectedLayerId = _availableLayers.first['id'];
              _selectedLayerName = _availableLayers.first['name'];
            } else {
              final matched = _availableLayers.firstWhere(
                (l) => l['name'] == widget.preSelectedLayer,
                orElse: () => null,
              );
              if (matched != null) {
                _selectedLayerId = matched['id'];
              }
            }
          }
        });
      }
    } catch (_) {}
  }

  Future<void> _getCurrentLocation() async {
    try {
      final pos = await Geolocator.getCurrentPosition();
      if (mounted) {
        setState(() { 
          _selectedPosition = LatLng(pos.latitude, pos.longitude); 
          _gettingLocation = false; 
        });
        _mapController?.animateCamera(CameraUpdate.newLatLng(LatLng(pos.latitude, pos.longitude)));
      }
    } catch (_) {
      if (mounted) setState(() => _gettingLocation = false);
    }
  }

  void _startWalkingTracking() async {
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) return;

    _positionSubscription = Geolocator.getPositionStream(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 5, // Record every 5 meters
      ),
    ).listen((Position position) {
      if (mounted) {
        final newPoint = LatLng(position.latitude, position.longitude);
        setState(() {
          _polygonPoints.add(newPoint);
          _selectedPosition = newPoint;
        });
        _mapController?.animateCamera(CameraUpdate.newLatLng(newPoint));
      }
    });
  }

  void _toggleWalking() {
    setState(() {
      _isWalkingGPS = !_isWalkingGPS;
      if (_isWalkingGPS) {
        _isPolygonMode = true;
        _startWalkingTracking();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('🚶 تم بدء تتبع المشي. تحرك لرسم الحدود!'), backgroundColor: Colors.blue),
        );
      } else {
        _positionSubscription?.cancel();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('🛑 تم إيقاف تتبع المشي.'), backgroundColor: Colors.orange),
        );
      }
    });
  }

  Future<void> _pickImage() async {
    final XFile? photo = await _picker.pickImage(source: ImageSource.camera, maxWidth: 1280, maxHeight: 1280);
    if (photo != null) setState(() => _imagePath = photo.path);
  }

  void _onMapTap(LatLng pos) {
    if (_isWalkingGPS) return; // Prevent manual taps during walking mode
    setState(() {
      if (_isPolygonMode) {
        _polygonPoints.add(pos);
      } else {
        _selectedPosition = pos;
      }
    });
  }

  Future<void> _submit() async {
    if (!_isPolygonMode && _selectedPosition == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('⚠️ اختر الموقع على الخريطة'), backgroundColor: AppColors.warning));
      return;
    }
    if (_isPolygonMode && _polygonPoints.length < 3) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('⚠️ يجب تحديد 3 نقاط على الأقل للحدود'), backgroundColor: AppColors.warning));
      return;
    }
    if (_nameController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('⚠️ أدخل اسم المنطقة'), backgroundColor: AppColors.warning));
      return;
    }

    setState(() => _loading = true);
    try {
      final api = di.sl<ApiClient>();

      if (widget.isBoundaryChallenge || _isWalkingGPS) {
        final coordsList = _polygonPoints.map((p) => {'lat': p.latitude, 'lng': p.longitude}).toList();
        final data = {
          'name': _nameController.text.trim(),
          'layer': _selectedLayerName,
          'coordinates': coordsList,
          'execution_mode': _isWalkingGPS ? 'walking_gps' : 'manual',
        };
        await api.dio.post('/mobile/boundary-challenge', data: data);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('✅ تم إرسال رسم الحدود للمراجعة بنجاح!'), backgroundColor: AppColors.success));
          context.pop();
        }
        return;
      }
      
      String? wkt;
      if (_isPolygonMode) {
        // Create WKT Polygon
        final coords = _polygonPoints.map((p) => "${p.longitude} ${p.latitude}").join(", ");
        // Closure point to make it valid
        final first = "${_polygonPoints.first.longitude} ${_polygonPoints.first.latitude}";
        wkt = "POLYGON(($coords, $first))";
      }

      final data = {
        'name': _nameController.text.trim(),
        'layer': _selectedLayerName,
        if (!_isPolygonMode) 'lat': _selectedPosition!.latitude,
        if (!_isPolygonMode) 'lng': _selectedPosition!.longitude,
        if (_isPolygonMode) 'geometry_wkt': wkt,
        'description': _descController.text.trim(),
        'icon': _selectedIcon,
        if (_forceAdd) 'force_add': 1,
      };

      final formData = FormData.fromMap(data);
      if (_imagePath != null) {
        formData.files.add(MapEntry('image', await MultipartFile.fromFile(_imagePath!, filename: 'verification.jpg')));
      }

      final response = await api.dio.post('/mobile/places', data: formData);
      final respData = response.data;

      if (respData is Map && respData['status'] == 'duplicate_check') {
        setState(() => _loading = false);
        if (mounted) {
          _showDuplicateCheckDialog(respData);
        }
        return;
      }

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('✅ تم إضافة المكان بنجاح وبانتظار المراجعة'), backgroundColor: AppColors.success));
        context.pop();
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('⚠️ حدث خطأ أثناء الإضافة'), backgroundColor: AppColors.error));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _showDuplicateCheckDialog(Map<dynamic, dynamic> respData) {
    final existing = respData['existing_place'];
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            const Text(
              'تنبيه: مكان مشابه قريباً منك',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.navy),
              textAlign: TextAlign.right,
            ),
            const SizedBox(width: 8),
            const Icon(Icons.location_on, color: AppColors.gold),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            const Text(
              'وجدنا مكاناً قريباً جداً مشابهاً في الاسم والتصنيف:',
              style: TextStyle(fontSize: 13, color: Colors.black87),
              textAlign: TextAlign.right,
            ),
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.amber[50],
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.amber[100]!),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    '📍 ${existing['name']}',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppColors.navy),
                    textAlign: TextAlign.right,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '📏 يبعد عنك حوالي ${existing['distance']} متر',
                    style: const TextStyle(fontSize: 11, color: Colors.black54),
                    textAlign: TextAlign.right,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'هل تقصد نفس هذا المعلم؟ (إذا كان هو نفسه، سنقوم بتسجيل تأكيدك له لتكسب نقاط التوثيق فوراً!)',
              style: TextStyle(fontSize: 12, color: Colors.black54),
              textAlign: TextAlign.right,
            ),
          ],
        ),
        actions: [
          Row(
            children: [
              Expanded(
                child: TextButton(
                  onPressed: () {
                    Navigator.pop(ctx);
                    setState(() {
                      _forceAdd = true;
                    });
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('💡 يُفضل تعديل اسم المكان قليلاً (مثلاً إضافة الحي) للتمييز!'),
                        backgroundColor: AppColors.gold,
                        duration: Duration(seconds: 4),
                      ),
                    );
                    _submit();
                  },
                  child: const Text(
                    'لا، مكان آخر جديد',
                    style: TextStyle(color: Colors.grey, fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: ElevatedButton(
                  onPressed: () async {
                    Navigator.pop(ctx);
                    setState(() => _loading = true);
                    try {
                      final api = di.sl<ApiClient>();
                      await api.dio.post('/mobile/places/${existing['id']}/confirm', data: {
                        'user_lat': _selectedPosition?.latitude,
                        'user_lng': _selectedPosition?.longitude,
                      });
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('✅ تم تأكيد المعلم الموجود بنجاح! سيتم منح نقاطك فور موافقة المشرف.'), backgroundColor: AppColors.success),
                        );
                        context.pop();
                      }
                    } catch (_) {
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('⚠️ حدث خطأ أثناء تأكيد المعلم'), backgroundColor: AppColors.error),
                        );
                      }
                    } finally {
                      if (mounted) setState(() => _loading = false);
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.gold,
                    foregroundColor: AppColors.navy,
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: const Text(
                    'نعم، هو تأكيد',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _showAddCategoryDialog() {
    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
          title: const Text('إضافة تصنيف جديد', textAlign: TextAlign.right),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: _newCategoryController,
                decoration: const InputDecoration(hintText: 'اسم التصنيف (مثال: صالون حلاقة)', alignLabelWithHint: true),
                textAlign: TextAlign.right,
              ),
              const SizedBox(height: 16),
              const Text('اختر أيقونة', style: TextStyle(fontSize: 12, color: AppColors.textGray)),
              const SizedBox(height: 8),
              SizedBox(
                height: 120,
                width: 300,
                child: GridView.builder(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 6),
                  itemCount: _iconOptions.length,
                  itemBuilder: (ctx, i) => GestureDetector(
                    onTap: () => setDialogState(() => _selectedIcon = _iconOptions[i]),
                    child: Container(
                      margin: const EdgeInsets.all(2),
                      decoration: BoxDecoration(
                        color: _selectedIcon == _iconOptions[i] ? AppColors.blue.withValues(alpha: 0.2) : Colors.transparent,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: _selectedIcon == _iconOptions[i] ? AppColors.blue : Colors.transparent),
                      ),
                      child: Center(child: Text(_iconOptions[i], style: const TextStyle(fontSize: 20))),
                    ),
                  ),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
            ElevatedButton(
              onPressed: () {
                if (_newCategoryController.text.trim().isNotEmpty) {
                  setState(() {
                    _selectedLayerName = _newCategoryController.text.trim();
                    _isPolygonMode = false; // Point by default for new
                  });
                  Navigator.pop(ctx);
                }
              },
              child: const Text('إضافة'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLayerOption(String label, String layerName) {
    final isSelected = _selectedLayerName == layerName;
    return InkWell(
      onTap: () {
        final layer = _availableLayers.firstWhere(
          (l) => l['name'] == layerName,
          orElse: () => {'id': '', 'name': layerName, 'type': 'polygons'},
        );
        setState(() {
          _selectedLayerName = layerName;
          _selectedLayerId = layer['id'] as String;
          _isPolygonMode = true;
        });
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.gold : Colors.grey[200],
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 12,
            color: isSelected ? AppColors.navy : Colors.black87,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.isBoundaryChallenge ? 'تحدي رسم الحدود 🗺️' : 'إضافة مكان جديد'),
        actions: [
          if (_loading)
            const Padding(padding: EdgeInsets.all(16), child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
          else
            IconButton(onPressed: _submit, icon: const Icon(Icons.check, color: AppColors.gold)),
        ],
      ),
      body: Column(children: [
        Expanded(
          flex: 4,
          child: Stack(children: [
            GestureDetector(
              behavior: HitTestBehavior.translucent,
              onPanUpdate: _isFreehandDrawing
                  ? (details) async {
                      if (_mapController == null) return;
                      final screenCoordinate = ScreenCoordinate(
                        x: details.localPosition.dx.round(),
                        y: details.localPosition.dy.round(),
                      );
                      final latLng = await _mapController!.getLatLng(screenCoordinate);
                      if (_polygonPoints.isEmpty) {
                        setState(() => _polygonPoints.add(latLng));
                      } else {
                        final last = _polygonPoints.last;
                        final diffLat = (latLng.latitude - last.latitude).abs();
                        final diffLng = (latLng.longitude - last.longitude).abs();
                        if (diffLat > 0.00015 || diffLng > 0.00015) {
                          setState(() => _polygonPoints.add(latLng));
                        }
                      }
                    }
                  : null,
              child: GoogleMap(
                initialCameraPosition: CameraPosition(target: _selectedPosition ?? _initialPosition, zoom: 15),
                onMapCreated: (controller) => _mapController = controller,
                onTap: _onMapTap,
                myLocationEnabled: true,
                scrollGesturesEnabled: !_isFreehandDrawing,
                zoomGesturesEnabled: !_isFreehandDrawing,
                rotateGesturesEnabled: !_isFreehandDrawing,
                tiltGesturesEnabled: !_isFreehandDrawing,
                markers: !_isPolygonMode && _selectedPosition != null
                    ? {Marker(markerId: const MarkerId('selected'), position: _selectedPosition!)}
                    : (_isPolygonMode ? _polygonPoints.asMap().entries.map((e) => Marker(markerId: MarkerId('p${e.key}'), position: e.value, icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueYellow))).toSet() : {}),
                polygons: _isPolygonMode && _polygonPoints.length >= 3
                    ? {Polygon(polygonId: const PolygonId('boundary'), points: _polygonPoints, fillColor: AppColors.blue.withValues(alpha: 0.3), strokeColor: AppColors.blue, strokeWidth: 3)}
                    : {},
              ),
            ),
            
            // 1. خيارات قرية ولا حي
            if (widget.isBoundaryChallenge || (_isPolygonMode && (_selectedLayerName == 'الأحياء' || _selectedLayerName == 'القرى والمدن')))
              Positioned(
                top: 16,
                left: 16,
                right: 16,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(30),
                    boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 8)],
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    textDirection: TextDirection.rtl,
                    children: [
                      const Text(
                        'نوع الحدود:',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.navy),
                      ),
                      Row(
                        children: [
                          _buildLayerOption('حي', 'الأحياء'),
                          const SizedBox(width: 8),
                          _buildLayerOption('قرية', 'القرى والمدن'),
                        ],
                      ),
                    ],
                  ),
                ),
              ),

            // 2. أدوات الرسم (قلم، مشي، مسح)
            if (_isPolygonMode)
              Positioned(
                bottom: 16,
                right: 16,
                child: Column(
                  children: [
                    FloatingActionButton.small(
                      heroTag: 'clearPoints',
                      onPressed: () => setState(() => _polygonPoints.clear()),
                      backgroundColor: Colors.white,
                      child: const Icon(Icons.refresh, color: AppColors.error),
                    ),
                    const SizedBox(height: 12),
                    // Walking Mode Button
                    FloatingActionButton.small(
                      heroTag: 'walkingMode',
                      onPressed: _toggleWalking,
                      backgroundColor: _isWalkingGPS ? Colors.blue : Colors.white,
                      child: Icon(Icons.directions_walk, color: _isWalkingGPS ? Colors.white : AppColors.navy),
                    ),
                    const SizedBox(height: 12),
                    FloatingActionButton(
                      heroTag: 'penTool',
                      onPressed: () {
                        setState(() {
                          _isFreehandDrawing = !_isFreehandDrawing;
                          if (_isFreehandDrawing) _isWalkingGPS = false;
                        });
                      },
                      backgroundColor: _isFreehandDrawing ? AppColors.blue : Colors.white,
                      child: Icon(
                        Icons.draw_rounded,
                        color: _isFreehandDrawing ? Colors.white : AppColors.navy,
                      ),
                    ),
                  ],
                ),
              ),

            // Hint Overlays
            if (_isWalkingGPS)
              Positioned(bottom: 80, left: 16, right: 16,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(color: Colors.blue.withValues(alpha: 0.9), borderRadius: BorderRadius.circular(12)),
                  child: Column(mainAxisSize: MainAxisSize.min, children: [
                    Row(mainAxisAlignment: MainAxisAlignment.center, children: const [
                      Icon(Icons.directions_walk, color: Colors.white),
                      SizedBox(width: 10),
                      Text('جاري تتبع مشيك... تحرك لرسم الحدود', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
                    ]),
                    if (_polygonPoints.length >= 3)
                      Padding(
                        padding: const EdgeInsets.only(top: 8),
                        child: Text(
                          'المساحة التقريبية: ~${(_polygonPoints.length * 15).toStringAsFixed(0)} م²', 
                          style: const TextStyle(color: Colors.white70, fontSize: 10)
                        ),
                      ),
                  ]),
                ),
              ),
            
            if (_isPolygonMode && !_isWalkingGPS && _polygonPoints.isNotEmpty)
              Positioned(bottom: 80, right: 16,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(color: AppColors.navy.withValues(alpha: 0.8), borderRadius: BorderRadius.circular(8)),
                  child: Text(
                    '${_polygonPoints.length} نقاط محددة',
                    style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
          ]),
        ),
        Expanded(
          flex: 5,
          child: Container(
            decoration: const BoxDecoration(color: Colors.white, borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
            child: ListView(
              padding: const EdgeInsets.all(24),
              children: [
                Directionality(
                  textDirection: TextDirection.rtl,
                  child: Column(children: [
                    TextField(
                      controller: _nameController, 
                      decoration: InputDecoration(
                        labelText: widget.isBoundaryChallenge ? 'اسم الحي أو القرية *' : 'اسم المكان *', 
                        prefixIcon: const Icon(Icons.title),
                      ),
                    ),
                    const SizedBox(height: 16),
                    
                    if (!widget.isBoundaryChallenge) ...[
                      Row(children: [
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            value: _selectedLayerId,
                            items: [
                              ..._availableLayers.map((l) => DropdownMenuItem(value: l['id'] as String, child: Text(l['name']))),
                            ],
                            onChanged: (v) {
                              if (v != null) {
                                final layer = _availableLayers.firstWhere((l) => l['id'] == v);
                                setState(() {
                                  _selectedLayerId = v;
                                  _selectedLayerName = layer['name'];
                                  _isPolygonMode = (layer['type'] == 'polygons');
                                });
                              }
                            },
                            decoration: const InputDecoration(labelText: 'التصنيف', prefixIcon: Icon(Icons.category)),
                          ),
                        ),
                        IconButton(onPressed: _showAddCategoryDialog, icon: const Icon(Icons.add_circle, color: AppColors.success)),
                      ]),
                      const SizedBox(height: 16),
                      TextField(controller: _descController, maxLines: 2, decoration: const InputDecoration(labelText: 'وصف إضافي (اختياري)', prefixIcon: Icon(Icons.description))),
                      const SizedBox(height: 20),
                    ],
                  ]),
                ),
              ],
            ),
          ),
        ),
      ]),
    );
  }
}

