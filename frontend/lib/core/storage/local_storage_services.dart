import 'package:hive_flutter/hive_flutter.dart';

/// Service to handle local storage of digital addresses.
class LocalAddressService {
  static const String boxName = 'saved_addresses';

  static Future<void> init() async {
    await Hive.openBox(boxName);
  }

  Future<void> saveAddress(Map<String, dynamic> address) async {
    final box = Hive.box(boxName);
    await box.put(address['id'] ?? address['plus_code'], address);
  }

  List<Map<String, dynamic>> getSavedAddresses() {
    final box = Hive.box(boxName);
    return box.values.map((e) => Map<String, dynamic>.from(e)).toList();
  }

  Future<void> deleteAddress(String key) async {
    final box = Hive.box(boxName);
    await box.delete(key);
  }
}

/// Service to handle local storage of statistics and metadata.
class LocalStatsService {
  static const String boxName = 'app_stats';

  static Future<void> init() async {
    await Hive.openBox(boxName);
  }

  Future<void> setLastUpdated(String key, DateTime timestamp) async {
    final box = Hive.box(boxName);
    await box.put('${key}_last_updated', timestamp.toIso8601String());
  }

  String? getLastUpdated(String key) {
    final box = Hive.box(boxName);
    return box.get('${key}_last_updated');
  }

  Future<void> setStat(String key, dynamic value) async {
    final box = Hive.box(boxName);
    await box.put(key, value);
  }

  dynamic getStat(String key) {
    final box = Hive.box(boxName);
    return box.get(key);
  }
}
