import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_colors.dart';
import '../bloc/auth_bloc.dart';

class SignupScreen extends StatefulWidget {
  const SignupScreen({super.key});

  @override
  State<SignupScreen> createState() => _SignupScreenState();
}

class _SignupScreenState extends State<SignupScreen> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmController = TextEditingController();
  bool _obscure = true;
  String? _error;

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  void _register() {
    final name = _nameController.text.trim();
    final phone = _phoneController.text.trim();
    final password = _passwordController.text.trim();
    final confirm = _confirmController.text.trim();

    if (name.isEmpty || phone.isEmpty || password.isEmpty) {
      setState(() => _error = 'يرجى ملء جميع الحقول المطلوبة');
      return;
    }
    if (password.length < 6) {
      setState(() => _error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل');
      return;
    }
    if (password != confirm) {
      setState(() => _error = 'كلمة المرور غير متطابقة');
      return;
    }

    setState(() => _error = null);
    context.read<AuthBloc>().add(RegisterRequested(
      name: name,
      phone: phone,
      password: password,
    ));
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
        appBar: AppBar(title: const Text('إنشاء حساب')),
        body: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const SizedBox(height: 8),
                TextField(
                  controller: _nameController,
                  textDirection: TextDirection.rtl,
                  decoration: const InputDecoration(
                    hintText: 'الاسم الكامل *',
                    prefixIcon: Icon(Icons.person_outline),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _phoneController,
                  textDirection: TextDirection.rtl,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(
                    hintText: 'رقم المحمول *',
                    prefixIcon: Icon(Icons.phone_outlined),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _passwordController,
                  textDirection: TextDirection.rtl,
                  obscureText: _obscure,
                  decoration: InputDecoration(
                    hintText: 'كلمة المرور *',
                    prefixIcon: const Icon(Icons.lock_outlined),
                    suffixIcon: IconButton(
                      icon: Icon(_obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined),
                      onPressed: () => setState(() => _obscure = !_obscure),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _confirmController,
                  textDirection: TextDirection.rtl,
                  obscureText: true,
                  decoration: const InputDecoration(
                    hintText: 'تأكيد كلمة المرور *',
                    prefixIcon: Icon(Icons.lock_outlined),
                  ),
                ),
                if (_error != null) ...[
                  const SizedBox(height: 12),
                  Text(_error!, style: const TextStyle(color: AppColors.error, fontSize: 13), textAlign: TextAlign.center),
                ],
                const SizedBox(height: 8),
                BlocBuilder<AuthBloc, AuthState>(
                  builder: (context, state) {
                    if (state is AuthLoading) {
                      return const Center(child: CircularProgressIndicator());
                    }
                    if (state is AuthError) {
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Text(state.message, style: const TextStyle(color: AppColors.error, fontSize: 13), textAlign: TextAlign.center),
                      );
                    }
                    return const SizedBox.shrink();
                  },
                ),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _register,
                    child: const Text('إنشاء حساب', style: TextStyle(fontSize: 16)),
                  ),
                ),
                const SizedBox(height: 16),
                TextButton(
                  onPressed: () => context.pop(),
                  child: const Text('لديك حساب بالفعل؟ تسجيل دخول', style: TextStyle(color: AppColors.blue)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
