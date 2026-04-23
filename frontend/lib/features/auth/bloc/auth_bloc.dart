import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import '../../../core/network/api_client.dart';
import '../../../core/storage/auth_storage_service.dart';

// Events
abstract class AuthEvent extends Equatable {
  @override
  List<Object?> get props => [];
}

class LoginRequested extends AuthEvent {
  final String phone;
  final String password;
  LoginRequested(this.phone, this.password);
  @override
  List<Object?> get props => [phone, password];
}

class AppStarted extends AuthEvent {}

class LogoutRequested extends AuthEvent {}

// States
abstract class AuthState extends Equatable {
  @override
  List<Object?> get props => [];
}

class AuthInitial extends AuthState {}
class AuthLoading extends AuthState {}
class AuthAuthenticated extends AuthState {
  final Map<String, dynamic> user;
  final String token;
  AuthAuthenticated(this.user, this.token);
  @override
  List<Object?> get props => [user, token];
}
class AuthError extends AuthState {
  final String message;
  AuthError(this.message);
}

// Bloc
class AuthBloc extends Bloc<AuthEvent, AuthState> {
  // Use the singleton instance
  final ApiClient _api = ApiClient();

  AuthBloc() : super(AuthInitial()) {
    on<AppStarted>((event, emit) async {
      final token = AuthStorageService.getToken();
      final user = AuthStorageService.getUser();

      if (token != null && user != null) {
        _api.updateToken(token);
        emit(AuthAuthenticated(user, token));
      } else {
        emit(AuthInitial());
      }
    });

    on<LoginRequested>((event, emit) async {
      emit(AuthLoading());
      try {
        final data = await _api.login(event.phone, event.password);
        final user = Map<String, dynamic>.from(data['user']);
        final token = data['token'] as String;

        // 1. Update ApiClient singleton with the new token
        _api.updateToken(token);

        // 2. Persist data locally
        await AuthStorageService.saveToken(token);
        await AuthStorageService.saveUser(user);

        emit(AuthAuthenticated(user, token));
      } catch (e) {
        emit(AuthError("فشل تسجيل الدخول: تأكد من صحة البيانات واتصال الخادم"));
      }
    });

    on<LogoutRequested>((event, emit) async {
      await AuthStorageService.logout();
      _api.updateToken(null);
      emit(AuthInitial());
    });
  }
}
