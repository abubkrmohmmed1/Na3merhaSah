import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../config/app_config.dart';
import '../storage/auth_storage.dart';

class ApiClient {
  late final Dio _dio;
  final AuthStorage _authStorage;

  ApiClient(this._authStorage) {
    _dio = Dio(BaseOptions(
      baseUrl: AppConfig.baseUrl,
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 15),
      headers: {'Accept': 'application/json'},
    ));

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        try {
          final token = await _authStorage.getToken();
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        } catch (e) {
          handler.reject(DioException(requestOptions: options, message: 'interceptor: $e'));
        }
      },
      onError: (error, handler) async {
        if (error.response?.statusCode == 401) {
          // Attempt to refresh if it's not already a refresh/login request
          final path = error.requestOptions.path;
          if (!path.contains('/refresh') && !path.contains('/login')) {
            try {
              final newTokenData = await refreshToken();
              if (newTokenData != null && newTokenData['token'] != null) {
                final newToken = newTokenData['token'];
                await _authStorage.saveToken(newToken);
                
                // Retry the original request
                final opts = error.requestOptions;
                opts.headers['Authorization'] = 'Bearer $newToken';
                final cloneReq = await _dio.fetch(opts);
                return handler.resolve(cloneReq);
              }
            } catch (_) {
              // Refresh failed, clear storage
              await _authStorage.logout();
            }
          } else {
             await _authStorage.logout();
          }
        }
        
        if (kDebugMode) {
          print('[API Error] ${error.response?.statusCode}: ${error.message}');
        }
        handler.next(error);
      },
    ));
  }

  Future<Map<String, dynamic>?> refreshToken() async {
    try {
      final res = await _dio.post('/refresh');
      return res.data;
    } catch (_) {
      return null;
    }
  }

  // Mobile endpoints
  Future<Map<String, dynamic>> getPlaceDetail(String id) async {
    final res = await _dio.get('/mobile/places/$id');
    return res.data;
  }

  Future<Map<String, dynamic>> addPlace({
    required String name,
    required double lat,
    required double lng,
    required String layer,
    String? description,
    String? imagePath,
  }) async {
    if (imagePath != null) {
      try {
        final file = await MultipartFile.fromFile(imagePath, filename: 'verification.jpg');
        final formData = FormData.fromMap({
          'name': name,
          'lat': lat,
          'lng': lng,
          'layer': layer,
          if (description != null) 'description': description,
          'image': file,
        });
        final res = await _dio.post('/mobile/places', data: formData);
        return res.data;
      } catch (_) {
        // if image fails, retry without it
      }
    }
    final res = await _dio.post('/mobile/places', data: {
      'name': name,
      'lat': lat,
      'lng': lng,
      'layer': layer,
      'description': description,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> confirmPlace(String id, {double? userLat, double? userLng}) async {
    final data = <String, dynamic>{};
    if (userLat != null) data['user_lat'] = userLat;
    if (userLng != null) data['user_lng'] = userLng;
    final res = await _dio.post('/mobile/places/$id/confirm', data: data);
    return res.data;
  }

  Future<Map<String, dynamic>> flagPlace(String id, String reason) async {
    final res = await _dio.post('/mobile/places/$id/flag', data: {
      'reason': reason,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> votePlace(String id, int rating) async {
    final res = await _dio.post('/mobile/places/$id/vote', data: {
      'rating': rating,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> getAchievements() async {
    final res = await _dio.get('/mobile/achievements');
    return res.data;
  }

  Future<Map<String, dynamic>> getQuests() async {
    final res = await _dio.get('/mobile/quests');
    return res.data;
  }

  Future<Map<String, dynamic>> claimQuest(String questId) async {
    final res = await _dio.post('/mobile/quests/$questId/claim');
    return res.data;
  }

  Future<Map<String, dynamic>> getTerritoryHighlights({double? lat, double? lng}) async {
    final res = await _dio.get('/mobile/territory/highlights', queryParameters: {
      if (lat != null) 'lat': lat,
      if (lng != null) 'lng': lng,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> getTerritoryLeaderboard(String neighborhoodId) async {
    final res = await _dio.get('/mobile/territory/leaderboard/$neighborhoodId');
    return res.data;
  }

  Future<Map<String, dynamic>> suggestEdit(String placeId, String field, String newValue) async {
    final res = await _dio.post('/mobile/places/$placeId/suggest-edit', data: {
      'field': field,
      'new_value': newValue,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> getProfile() async {
    final res = await _dio.get('/mobile/profile');
    return res.data;
  }

  Future<Map<String, dynamic>> getFeed() async {
    final res = await _dio.get('/mobile/feed');
    return res.data;
  }

  Future<Map<String, dynamic>> getLayers() async {
    final res = await _dio.get('/mobile/layers');
    return res.data;
  }

  Future<Map<String, dynamic>> getEvents({double? lat, double? lng}) async {
    final res = await _dio.get('/mobile/events', queryParameters: {
      if (lat != null) 'lat': lat,
      if (lng != null) 'lng': lng,
    });
    return res.data;
  }

  // Auth
  Future<Map<String, dynamic>> login(String phone, String password) async {
    final res = await _dio.post('/login', data: {
      'phone': phone,
      'password': password,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String phone,
    required String password,
  }) async {
    final res = await _dio.post('/register', data: {
      'name': name,
      'phone': phone,
      'password': password,
    });
    return res.data;
  }

  Dio get dio => _dio;

  // Spatial
  Future<Map<String, dynamic>> getStates() async {
    final res = await _dio.get('/spatial/states');
    return res.data;
  }

  Future<Map<String, dynamic>> getLocalities(String? stateId) async {
    final res = await _dio.get('/spatial/localities', queryParameters: {
      if (stateId != null) 'state_id': stateId,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> getNeighborhoods(String localityId) async {
    final res = await _dio.get('/spatial/neighborhoods', queryParameters: {
      'locality_id': localityId,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> radiusSearch({
    required double lat,
    required double lng,
    int radius = 1000,
    String? layer,
    int limit = 50,
  }) async {
    final res = await _dio.get('/spatial/radius', queryParameters: {
      'lat': lat,
      'lng': lng,
      'radius': radius,
      if (layer != null) 'layer': layer,
      'limit': limit,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> searchAddresses(String query) async {
    final res = await _dio.get('/addresses/search', queryParameters: {
      'query': query,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> reverseGeocode(double lat, double lng) async {
    final res = await _dio.get('/addresses/reverse', queryParameters: {
      'lat': lat,
      'lng': lng,
    });
    return res.data;
  }

  Future<Map<String, dynamic>> getAppConfig({String platform = 'all'}) async {
    final res = await _dio.get('/mobile/app-config', queryParameters: {
      'platform': platform,
    });
    return res.data;
  }
}
