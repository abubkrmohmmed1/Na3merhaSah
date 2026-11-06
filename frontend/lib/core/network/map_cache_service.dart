import 'package:hive_flutter/hive_flutter.dart';

class MapCacheService {
  static const String boxName = 'map_markers_cache';

  static Future<void> init() async {
    await Hive.openBox(boxName);
  }

  static Future<void> cacheMarkers(List<dynamic> features) async {
    final box = Hive.box(boxName);
    // Keep only the most recent 500 features to avoid bloating
    await box.clear();
    for (var i = 0; i < features.length && i < 500; i++) {
      await box.add(features[i]);
    }
  }

  static List<dynamic> getCachedMarkers() {
    final box = Hive.box(boxName);
    return box.values.toList();
  }
}
