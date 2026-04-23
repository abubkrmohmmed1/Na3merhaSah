import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:dio/dio.dart';
import 'package:geolocator/geolocator.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/network/api_client.dart';

class SignUpScreen extends StatefulWidget {
  const SignUpScreen({super.key});

  @override
  State<SignUpScreen> createState() => _SignUpScreenState();
}

class _SignUpScreenState extends State<SignUpScreen> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _addressController = TextEditingController();
  final _nationalIdController = TextEditingController();
  double? _homeLat;
  double? _homeLng;
  final ApiClient _api = ApiClient();
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _determinePosition();
  }

  Future<void> _determinePosition() async {
    bool serviceEnabled;
    LocationPermission permission;

    serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) return;

    permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) return;
    }
    
    if (permission == LocationPermission.deniedForever) return;

    Position position = await Geolocator.getCurrentPosition();
    setState(() {
      _homeLat = position.latitude;
      _homeLng = position.longitude;
    });
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _addressController.dispose();
    _nationalIdController.dispose();
    super.dispose();
  }

  Future<void> _handleSignUp() async {
    if (_nameController.text.isEmpty || _phoneController.text.isEmpty || _passwordController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('يرجى ملء جميع الحقول')),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      final response = await _api.register(
        name: _nameController.text.trim(),
        phone: _phoneController.text.trim(),
        password: _passwordController.text.trim(),
        homeAddress: _addressController.text.trim(),
        homeLat: _homeLat,
        homeLng: _homeLng,
        nationalId: _nationalIdController.text.trim(),
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(response['message'] ?? 'تم إنشاء الحساب بنجاح')),
        );
        Navigator.pop(context); // Return to login
      }
    } catch (e) {
      if (mounted) {
        String errorMsg = 'حدث خطأ أثناء إنشاء الحساب';
        if (e is DioException && e.response?.data != null) {
          final data = e.response?.data;
          if (data['errors'] != null && data['errors'] is Map) {
            // Get the first error message from the first field that failed
            final firstError = (data['errors'] as Map).values.first;
            if (firstError is List && firstError.isNotEmpty) {
              errorMsg = firstError.first.toString();
            }
          } else {
            errorMsg = data['message'] ?? errorMsg;
          }
        }
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(errorMsg), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          backgroundColor: AppColors.headerBg,
          title: Text('إنشاء حساب جديد', style: GoogleFonts.cairo(fontWeight: FontWeight.bold)),
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(30),
          child: Column(
            children: [
              const SizedBox(height: 10),
              // Logo/Avatar Area matching design
              Container(
                width: 80, height: 80,
                decoration: BoxDecoration(color: Colors.white, shape: BoxShape.circle, border: Border.all(color: Colors.grey.shade100, width: 4)),
                child: Icon(Icons.person_add_outlined, size: 40, color: AppColors.primary),
              ),
              
              const SizedBox(height: 20),
              _buildFieldLabel('الاسم الكامل'),
              const SizedBox(height: 6),
              TextField(
                controller: _nameController,
                textAlign: TextAlign.center,
                decoration: const InputDecoration(hintText: 'ادخل اسمك هنا'),
              ),

              const SizedBox(height: 12),
              _buildFieldLabel('رقم المحمول'),
              const SizedBox(height: 6),
              TextField(
                controller: _phoneController,
                keyboardType: TextInputType.phone,
                textAlign: TextAlign.center,
                decoration: const InputDecoration(hintText: '09xxxxxxx'),
              ),

              const SizedBox(height: 12),
              _buildFieldLabel('العنوان (اختياري)'),
              const SizedBox(height: 6),
              TextField(
                controller: _addressController,
                textAlign: TextAlign.center,
                decoration: const InputDecoration(hintText: 'مثلاً: بحري، المزاد، مربع 4'),
              ),

              const SizedBox(height: 12),
              _buildFieldLabel('الرقم الوطني (اختياري)'),
              const SizedBox(height: 6),
              TextField(
                controller: _nationalIdController,
                keyboardType: TextInputType.number,
                textAlign: TextAlign.center,
                decoration: const InputDecoration(hintText: 'الرقم الوطني المكون من 11 خانة'),
              ),

              const SizedBox(height: 12),
              _buildFieldLabel('الرقم السري'),
              const SizedBox(height: 6),
              TextField(
                controller: _passwordController,
                obscureText: true,
                textAlign: TextAlign.center,
                decoration: const InputDecoration(hintText: '••••••••'),
              ),

              const SizedBox(height: 30),
              ElevatedButton(
                onPressed: _isLoading ? null : _handleSignUp,
                style: ElevatedButton.styleFrom(backgroundColor: AppColors.primaryDark),
                child: _isLoading 
                  ? const CircularProgressIndicator(color: Colors.white)
                  : Text('إنشاء حساب', style: GoogleFonts.cairo(fontWeight: FontWeight.bold, color: Colors.white)),
              ),

              const SizedBox(height: 20),
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: Text('لديك حساب بالفعل؟ تسجيل دخول', style: GoogleFonts.cairo(color: AppColors.primary)),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFieldLabel(String label) {
    return Align(
      alignment: Alignment.centerRight,
      child: Text(label, style: GoogleFonts.cairo(fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
    );
  }
}
