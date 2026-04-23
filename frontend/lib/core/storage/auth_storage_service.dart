import 'package:hive_flutter/hive_flutter.dart';

class AuthStorageService {
  static const String boxName = 'auth_box';
  static const String tokenKey = 'token';
  static const String userKey = 'user_data';

  static Future<void> init() async {
    await Hive.openBox(boxName);
  }

  static Future<void> saveToken(String token) async {
    final box = Hive.box(boxName);
    await box.put(tokenKey, token);
  }

  static String? getToken() {
    final box = Hive.box(boxName);
    return box.get(tokenKey);
  }

  static Future<void> saveUser(Map<String, dynamic> userData) async {
    final box = Hive.box(boxName);
    await box.put(userKey, userData);
  }

  static Map<String, dynamic>? getUser() {
    final box = Hive.box(boxName);
    final data = box.get(userKey);
    return data != null ? Map<String, dynamic>.from(data) : null;
  }

  static Future<void> logout() async {
    final box = Hive.box(boxName);
    await box.clear();
  }
}
