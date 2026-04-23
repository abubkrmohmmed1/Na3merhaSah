import 'package:dio/dio.dart';
import '../config/app_config.dart';

/// Centralized API client configured for the Namerha Sah backend.
class ApiClient {
  static String get baseApiUrl => AppConfig.baseUrl;

  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  late final Dio _dio;
  String? _token;

  ApiClient._internal() {
    _dio = Dio(BaseOptions(
      baseUrl: baseApiUrl,
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 10),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    // Add interceptor to inject Token in every request
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        if (_token != null) {
          options.headers['Authorization'] = 'Bearer $_token';
        }
        return handler.next(options);
      },
    ));
  }

  /// Update the token used for requests
  void updateToken(String? newToken) {
    _token = newToken;
  }

  /// POST /v1/login
  Future<Map<String, dynamic>> login(String phone, String password) async {
    final response = await _dio.post('/v1/login', data: {
      'phone': phone,
      'password': password,
    });
    return response.data;
  }

  /// POST /v1/register
  Future<Map<String, dynamic>> register({
    required String name,
    required String phone,
    required String password,
    String? homeAddress,
    double? homeLat,
    double? homeLng,
    String? nationalId,
  }) async {
    final response = await _dio.post('/v1/register', data: {
      'name': name,
      'phone': phone,
      'password': password,
      'home_address': homeAddress,
      'home_lat': homeLat,
      'home_lng': homeLng,
      'national_id': nationalId,
    });
    return response.data;
  }

  /// GET /v1/addresses/reverse?lat=...&lng=...
  Future<Map<String, dynamic>> reverseGeocode(double lat, double lng) async {
    final response = await _dio.get('/v1/addresses/reverse', queryParameters: {
      'lat': lat,
      'lng': lng,
    });
    return response.data['data'];
  }

  /// GET /v1/addresses/search?query=...
  Future<List<dynamic>> searchAddresses(String query, {double? lat, double? lng}) async {
    final params = <String, dynamic>{'query': query};
    if (lat != null) params['lat'] = lat;
    if (lng != null) params['lng'] = lng;

    final response = await _dio.get('/v1/addresses/search', queryParameters: params);
    return response.data['data'];
  }

  /// GET /v1/reports
  Future<Map<String, dynamic>> getReports({int page = 1}) async {
    final response = await _dio.get('/v1/reports', queryParameters: {'page': page});
    return response.data;
  }

  /// POST /v1/reports (multipart)
  Future<Map<String, dynamic>> submitReport({
    required double lat,
    required double lng,
    required String description,
    required int categoryId,
    List<String> imagePaths = const [],
    required String workflowStep,
    required Map<String, dynamic> workflowData,
  }) async {
    final formData = FormData.fromMap({
      'lat': lat,
      'lng': lng,
      'description': description,
      'category_id': categoryId,
      'workflow_step': workflowStep,
      'workflow_metadata': workflowData,
      'images[]': await Future.wait(
        imagePaths.map((path) => MultipartFile.fromFile(path)),
      ),
    });

    final response = await _dio.post('/v1/reports', data: formData);
    return response.data;
  }
}
