class AppConfig {
  /// Toggle this to switch between local and production
  static const bool isProduction = false;

  /// Your production API URL (Once deployed)
  static const String prodBaseUrl = 'https://api.namerha-sah.com/api';

  /// Your local development URL
  /// 10.0.2.2 is for Android Emulator
  /// Using computer IP for testing via Hotspot: 10.155.148.187
  static const String localBaseUrl = 'http://10.155.148.187:8000/api';

  static String get baseUrl => isProduction ? prodBaseUrl : localBaseUrl;
}
