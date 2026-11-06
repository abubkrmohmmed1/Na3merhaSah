import 'package:hive_flutter/hive_flutter.dart';

class AuthStorage {
  late Box _box;

  Future<void> init() async {
    _box = await Hive.openBox('auth');
  }

  Future<void> saveToken(String token) async {
    await _box.put('token', token);
  }

  Future<String?> getToken() async {
    return _box.get('token');
  }

  Future<bool> get isLoggedIn async {
    return _box.containsKey('token');
  }

  Future<void> saveUser(Map<String, dynamic> user) async {
    await _box.put('user', user);
  }

  Map<String, dynamic>? getUser() {
    return _box.get('user')?.cast<String, dynamic>();
  }

  Future<void> logout() async {
    await _box.delete('token');
    await _box.delete('user');
  }

  Future<void> clear() async {
    await _box.clear();
  }
}
