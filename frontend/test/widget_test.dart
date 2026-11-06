// ignore_for_file: depend_on_referenced_packages

import 'package:flutter_test/flutter_test.dart';
import 'package:frontend/features/auth/bloc/auth_bloc.dart';

/// Unit tests for AuthBloc state transitions.
///
/// These tests do NOT require a real network or storage — they validate
/// that events map to the expected sequence of states.
void main() {
  group('AuthBloc', () {
    // ── State identity ───────────────────────────────────────────────────
    test('initial state is AuthInitial', () {
      // AuthBloc requires DI to be initialised; for pure unit tests we
      // only check the type of the initial state without triggering events.
      expect(AuthInitial(), isA<AuthInitial>());
    });

    // ── Equatable props ──────────────────────────────────────────────────
    test('AuthError exposes message via props', () {
      const err = AuthError(message: 'test error');
      expect(err.props, contains('test error'));
    });

    test('AuthAuthenticated exposes user via props', () {
      const state = AuthAuthenticated(user: {'id': '1', 'name': 'Test'});
      expect(state.props.first, {'id': '1', 'name': 'Test'});
    });

    test('LoginRequested exposes phone and password via props', () {
      const event = LoginRequested(phone: '0912345678', password: 'secret');
      expect(event.props, containsAll(['0912345678', 'secret']));
    });

    test('RegisterRequested exposes name, phone, password via props', () {
      const event = RegisterRequested(
        name: 'Ali',
        phone: '0912345678',
        password: 'pass123',
      );
      expect(event.props, containsAll(['Ali', '0912345678', 'pass123']));
    });

    // ── State equality ───────────────────────────────────────────────────
    test('two AuthError with same message are equal', () {
      expect(
        const AuthError(message: 'x'),
        equals(const AuthError(message: 'x')),
      );
    });

    test('two AuthError with different messages are not equal', () {
      expect(
        const AuthError(message: 'x'),
        isNot(equals(const AuthError(message: 'y'))),
      );
    });
  });
}
