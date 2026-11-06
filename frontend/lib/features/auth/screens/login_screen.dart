import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_colors.dart';
import '../bloc/auth_bloc.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscure = true;

  @override
  void dispose() {
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<AuthBloc, AuthState>(
      listener: (context, state) {
        if (state is AuthAuthenticated) {
          context.go('/map');
        }
      },
      child: Scaffold(
        body: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(32),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(16),
                    child: Image.asset(
                      'assets/images/logo.jpeg',
                      width: 120, height: 120, fit: BoxFit.cover,
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text('نُعمرها صح', style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: AppColors.navy)),
                  const SizedBox(height: 8),
                  Text('ويكيبيديا الخرائط', style: TextStyle(fontSize: 14, color: AppColors.textGray)),
                  const SizedBox(height: 48),
                  TextField(
                    controller: _phoneController,
                    textDirection: TextDirection.rtl,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(
                      hintText: 'رقم المحمول',
                      prefixIcon: Icon(Icons.phone_outlined),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: _passwordController,
                    textDirection: TextDirection.rtl,
                    obscureText: _obscure,
                    decoration: InputDecoration(
                      hintText: 'كلمة المرور',
                      prefixIcon: const Icon(Icons.lock_outlined),
                      suffixIcon: IconButton(
                        icon: Icon(_obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined),
                        onPressed: () => setState(() => _obscure = !_obscure),
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  BlocBuilder<AuthBloc, AuthState>(
                    builder: (context, state) {
                      if (state is AuthLoading) {
                        return const CircularProgressIndicator();
                      }
                      if (state is AuthError) {
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: Text(state.message, style: const TextStyle(color: AppColors.error, fontSize: 13)),
                        );
                      }
                      return const SizedBox.shrink();
                    },
                  ),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () {
                        context.read<AuthBloc>().add(LoginRequested(
                          phone: _phoneController.text.trim(),
                          password: _passwordController.text.trim(),
                        ));
                      },
                      child: const Text('تسجيل دخول', style: TextStyle(fontSize: 16)),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextButton(
                    onPressed: () => context.push('/signup'),
                    child: const Text('ليس لديك حساب؟ سجّل الآن', style: TextStyle(color: AppColors.blue)),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
