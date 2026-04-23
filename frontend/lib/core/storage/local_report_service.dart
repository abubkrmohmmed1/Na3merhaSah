import 'package:hive_flutter/hive_flutter.dart';

/// Service to handle local storage of reports for offline support.
class LocalReportService {
  static const String boxName = 'pending_reports';

  /// Initialize Hive and open the box.
  static Future<void> init() async {
    await Hive.initFlutter();
    await Hive.openBox(boxName);
  }

  /// Save a report to local storage.
  Future<void> saveReport(Map<String, dynamic> reportData) async {
    final box = Hive.box(boxName);
    await box.add({
      ...reportData,
      'offline_created_at': DateTime.now().toIso8601String(),
    });
  }

  /// Get all pending reports.
  List<Map<String, dynamic>> getPendingReports() {
    final box = Hive.box(boxName);
    return box.values.map((e) => Map<String, dynamic>.from(e)).toList();
  }

  /// Clear all pending reports after successful sync.
  Future<void> clearPendingReports() async {
    final box = Hive.box(boxName);
    await box.clear();
  }

  /// Get count of pending reports.
  int getPendingCount() {
    return Hive.box(boxName).length;
  }
}
