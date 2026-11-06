part of 'auth_bloc.dart';

abstract class AuthEvent extends Equatable {
  const AuthEvent();

  @override
  List<Object?> get props => [];
}

class CheckAuthEvent extends AuthEvent {
  const CheckAuthEvent();
}

class LoginRequested extends AuthEvent {
  final String phone;
  final String password;

  const LoginRequested({required this.phone, required this.password});

  @override
  List<Object?> get props => [phone, password];
}

class LogoutRequested extends AuthEvent {
  const LogoutRequested();
}

class RegisterRequested extends AuthEvent {
  final String name;
  final String phone;
  final String password;

  const RegisterRequested({required this.name, required this.phone, required this.password});

  @override
  List<Object?> get props => [name, phone, password];
}
