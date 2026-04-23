import 'package:permission_handler/permission_handler.dart';

class PermissionService {
  static Future<void> requestAllPermissions() async {
    Map<Permission, PermissionStatus> statuses = await [
      Permission.location,
      Permission.camera,
    ].request();
    
    if (statuses[Permission.location]!.isDenied) {
      // Handle denied
    }
  }

  static Future<bool> hasLocationPermission() async {
    return await Permission.location.isGranted;
  }
}
