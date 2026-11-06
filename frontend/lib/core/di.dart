import 'package:get_it/get_it.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'network/api_client.dart';
import 'network/map_cache_service.dart';
import 'storage/auth_storage.dart';
import 'theme/theme_service.dart';

final sl = GetIt.instance;

Future<void> initDependencies() async {
  await Hive.initFlutter();
  await MapCacheService.init();

  final authStorage = AuthStorage();
  await authStorage.init();
  sl.registerLazySingleton<AuthStorage>(() => authStorage);

  sl.registerLazySingleton<ThemeService>(() => ThemeService());
  sl.registerLazySingleton<ApiClient>(() => ApiClient(sl()));
}
