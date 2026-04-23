import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/network/api_client.dart';

class AddressPickerScreen extends StatefulWidget {
  const AddressPickerScreen({super.key});

  @override
  State<AddressPickerScreen> createState() => _AddressPickerScreenState();
}

class _AddressPickerScreenState extends State<AddressPickerScreen> {
  GoogleMapController? _mapController;
  final ApiClient _api = ApiClient();
  
  LatLng _currentLocation = const LatLng(15.6361, 32.4777); // Default to Omdurman
  String _addressStr = "جاري تحديد العنوان...";
  String _s2Token = "";
  bool _isLoadingAddress = false;

  @override
  void initState() {
    super.initState();
    _determinePosition();
  }

  Future<void> _determinePosition() async {
    bool serviceEnabled;
    LocationPermission permission;

    serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) return;

    permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) return;
    }

    Position position = await Geolocator.getCurrentPosition();
    setState(() {
      _currentLocation = LatLng(position.latitude, position.longitude);
    });
    
    _mapController?.animateCamera(
      CameraUpdate.newLatLngZoom(_currentLocation, 18),
    );
    
    _onCameraMove(_currentLocation);
  }

  Future<void> _onCameraMove(LatLng position) async {
    setState(() {
      _currentLocation = position;
      _isLoadingAddress = true;
    });

    try {
      final addressData = await _api.reverseGeocode(position.latitude, position.longitude);
      setState(() {
        _addressStr = addressData['address_str'] ?? "عنوان غير معروف";
        _s2Token = addressData['s2_token'] ?? "";
        _isLoadingAddress = false;
      });
    } catch (e) {
      setState(() {
        _addressStr = "حدث خطأ في تحديد العنوان";
        _isLoadingAddress = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        appBar: AppBar(
          title: Text('تحديد موقع البلاغ', style: GoogleFonts.cairo(fontWeight: FontWeight.bold)),
          backgroundColor: AppColors.headerBg,
        ),
        body: Stack(
          children: [
            GoogleMap(
              initialCameraPosition: CameraPosition(target: _currentLocation, zoom: 18),
              onMapCreated: (controller) => _mapController = controller,
              onCameraMove: (position) => _currentLocation = position.target,
              onCameraIdle: () => _onCameraMove(_currentLocation),
              myLocationEnabled: true,
              myLocationButtonEnabled: false,
              zoomControlsEnabled: false,
            ),
            
            // Center Pin
            Center(
              child: Padding(
                padding: const EdgeInsets.only(bottom: 35),
                child: Icon(Icons.location_on, color: AppColors.primary, size: 45),
              ),
            ),

            // Bottom Address Card
            Positioned(
              bottom: 20,
              left: 20,
              right: 20,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Card(
                    elevation: 10,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.map_outlined, color: AppColors.primary),
                              const SizedBox(width: 10),
                              Expanded(
                                child: _isLoadingAddress 
                                  ? const LinearProgressIndicator()
                                  : Text(
                                      _addressStr,
                                      style: GoogleFonts.cairo(fontWeight: FontWeight.w600),
                                      maxLines: 2,
                                    ),
                              ),
                            ],
                          ),
                          if (_s2Token.isNotEmpty) ...[
                            const SizedBox(height: 5),
                            Text(
                              "S2 Token: $_s2Token",
                              style: GoogleFonts.cairo(fontSize: 10, color: Colors.grey),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  ElevatedButton(
                    onPressed: () {
                      Navigator.pushNamed(context, '/report', arguments: {
                        'lat': _currentLocation.latitude,
                        'lng': _currentLocation.longitude,
                        'address': _addressStr,
                        'token': _s2Token,
                      });
                    },
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 15),
                    ),
                    child: Text('تأكيد الموقع والبدء بالبلاغ', style: GoogleFonts.cairo(fontSize: 18, fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
            
            // My Location Button
            Positioned(
              right: 20,
              top: 20,
              child: FloatingActionButton(
                mini: true,
                backgroundColor: Colors.white,
                onPressed: _determinePosition,
                child: const Icon(Icons.my_location, color: Colors.black54),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
