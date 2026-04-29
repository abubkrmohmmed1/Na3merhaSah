import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/theme/app_theme.dart';
import '../bloc/auth_bloc.dart';

class LoginScreen extends StatelessWidget {
  const LoginScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => AuthBloc(),
      child: const LoginView(),
    );
  }
}

class LoginView extends StatefulWidget {
  const LoginView({super.key});

  @override
  State<LoginView> createState() => _LoginViewState();
}

class _LoginViewState extends State<LoginView> {
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

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
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('مرحباً بك ${state.user['name']}', style: GoogleFonts.cairo())),
          );
          Navigator.pushReplacementNamed(context, '/home');
        } else if (state is AuthError) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(state.message, style: GoogleFonts.cairo()), backgroundColor: AppColors.statusRed),
          );
        }
      },
      child: Directionality(
        textDirection: TextDirection.rtl,
        child: Scaffold(
          backgroundColor: AppColors.background,
          appBar: AppBar(
            backgroundColor: AppColors.headerBg,
            actions: [
              IconButton(icon: const Icon(Icons.info_outline, color: Colors.white), onPressed: () {}),
            ],
          ),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(30),
            child: Column(
              children: [
                const SizedBox(height: 20),
                // Logo/Avatar Area matching design
                Container(
                  width: 110, height: 110,
                  decoration: BoxDecoration(color: Colors.white, shape: BoxShape.circle, border: Border.all(color: Colors.grey.shade100, width: 4)),
                  child: Icon(Icons.person, size: 60, color: AppColors.textHint),
                ),
                const SizedBox(height: 10),
                Container(width: 40, height: 4, decoration: BoxDecoration(color: AppColors.statusGreen, borderRadius: BorderRadius.circular(2))),
                
                const SizedBox(height: 40),
                _buildFieldLabel('رقم المحمول'),
                const SizedBox(height: 8),
                TextField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  textAlign: TextAlign.center,
                  decoration: const InputDecoration(hintText: '099 122 584 7825'),
                ),

                const SizedBox(height: 20),
                _buildFieldLabel('الرقم السري'),
                const SizedBox(height: 8),
                TextField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  textAlign: TextAlign.center,
                  decoration: InputDecoration(
                    hintText: '•••••',
                    suffixIcon: IconButton(
                      icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility),
                      onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                    ),
                  ),
                ),

                const SizedBox(height: 30),
                BlocBuilder<AuthBloc, AuthState>(
                  builder: (context, state) {
                    return ElevatedButton(
                      onPressed: state is AuthLoading 
                        ? null 
                        : () => context.read<AuthBloc>().add(LoginRequested(_phoneController.text, _passwordController.text)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primaryDark,
                      ),
                      child: state is AuthLoading 
                        ? const CircularProgressIndicator(color: Colors.white)
                        : Text('تسجيل دخول', style: GoogleFonts.cairo(fontWeight: FontWeight.bold, color: Colors.white)),
                    );
                  },
                ),

                const SizedBox(height: 20),
                Text('أو', style: GoogleFonts.cairo(color: Colors.grey)),
                const SizedBox(height: 20),

                const SizedBox(height: 15),
                // Sign up button matching design (Amber/Orange)
                ElevatedButton(
                  onPressed: () => Navigator.pushNamed(context, '/signup'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFFFCC80), // Orange/Amber from design
                    foregroundColor: Colors.black,
                  ),
                  child: Text('إنشاء حساب جديد', style: GoogleFonts.cairo(fontWeight: FontWeight.bold)),
                ),
                
                const SizedBox(height: 30),
                // Social Divider
                Row(
                  children: [
                    Expanded(child: Divider(color: Colors.grey.shade300)),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Text('أو عبر', style: GoogleFonts.cairo(fontSize: 12, color: Colors.grey)),
                    ),
                    Expanded(child: Divider(color: Colors.grey.shade300)),
                  ],
                ),
                const SizedBox(height: 20),

                // Social Buttons
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    _buildSocialButton(Icons.g_mobiledata, Colors.red.shade600),
                    const SizedBox(width: 20),
                    _buildSocialButton(Icons.facebook, Colors.blue.shade900),
                  ],
                ),

                const SizedBox(height: 20),
                // Bot Protection Note (Captcha)
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.security, size: 14, color: Colors.grey.shade400),
                    const SizedBox(width: 8),
                    Text('محمي بواسطة reCAPTCHA', style: GoogleFonts.cairo(fontSize: 10, color: Colors.grey.shade400)),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSocialButton(IconData icon, Color color) {
    return Container(
      width: 50, height: 50,
      decoration: BoxDecoration(
        color: Colors.white,
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2)),
        ],
        border: Border.all(color: Colors.grey.shade100),
      ),
      child: Icon(icon, color: color, size: 30),
    );
  }

  Widget _buildFieldLabel(String label) {
    return Align(
      alignment: Alignment.centerRight,
      child: Text(label, style: GoogleFonts.cairo(fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
    );
  }
}
