class AppConfig {
  /// Base API URL — injected at build/run time via --dart-define=API_BASE_URL=...
  /// Fallbacks:
  ///   Production  : https://n3mr.nafir.net/api/v1
  ///   Android Emu : http://10.0.2.2:8000/api/v1
  ///   iOS Sim     : http://127.0.0.1:8000/api/v1
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://n3mr.nafir.net/api/v1',
  );

  static String get baseUrlB2B => '$baseUrl/b2b';
  static String get baseUrlMobile => '$baseUrl/mobile';
  static String get baseUrlAuth => baseUrl;
}
