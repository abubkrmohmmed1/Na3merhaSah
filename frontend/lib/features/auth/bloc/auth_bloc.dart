import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import '../../../core/network/api_client.dart';
import '../../../core/storage/auth_storage.dart';
import '../../../core/di.dart' as di;

part 'auth_event.dart';
part 'auth_state.dart';

class AuthBloc extends Bloc<AuthEvent, AuthState> {
  final ApiClient _api = di.sl<ApiClient>();
  final AuthStorage _storage = di.sl<AuthStorage>();

  AuthBloc() : super(AuthInitial()) {
    on<CheckAuthEvent>(_onCheckAuth);
    on<LoginRequested>(_onLogin);
    on<RegisterRequested>(_onRegister);
    on<LogoutRequested>(_onLogout);
  }

  Future<void> _onCheckAuth(CheckAuthEvent event, Emitter<AuthState> emit) async {
    final loggedIn = await _storage.isLoggedIn;
    if (loggedIn) {
      final user = _storage.getUser();
      emit(AuthAuthenticated(user: user));
    } else {
      emit(AuthUnauthenticated());
    }
  }

  Future<void> _onLogin(LoginRequested event, Emitter<AuthState> emit) async {
    emit(AuthLoading());
    try {
      final data = await _api.login(event.phone, event.password);
      await _storage.saveToken(data['token']);
      await _storage.saveUser(data['user']);
      emit(AuthAuthenticated(user: data['user']));
    } catch (e) {
      emit(AuthError(message: 'فشل تسجيل الدخول. تأكد من بياناتك.'));
    }
  }

  Future<void> _onRegister(RegisterRequested event, Emitter<AuthState> emit) async {
    emit(AuthLoading());
    try {
      final data = await _api.register(
        name: event.name,
        phone: event.phone,
        password: event.password,
      );
      await _storage.saveToken(data['token']);
      await _storage.saveUser(data['user']);
      emit(AuthAuthenticated(user: data['user']));
    } catch (e) {
      emit(AuthError(message: 'فشل إنشاء الحساب. تأكد من صحة البيانات.'));
    }
  }

  Future<void> _onLogout(LogoutRequested event, Emitter<AuthState> emit) async {
    await _storage.clear();
    emit(AuthUnauthenticated());
  }
}
