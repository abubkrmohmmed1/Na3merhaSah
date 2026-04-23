import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/theme/app_theme.dart';

class SuccessScreen extends StatelessWidget {
  const SuccessScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: AppColors.background,
        body: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const SizedBox(height: 40),
                Text(
                  'تم ارسال الشكوى\nبنجاح',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.cairo(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: AppColors.primaryDark,
                    height: 1.2,
                  ),
                ),
                const SizedBox(height: 30),
                
                // Info Card
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF3F4F6),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Column(
                    children: [
                      _buildInfoRow('رقم الشكوى:', '15867#'),
                      _buildInfoRow('نوع المشكلة:', 'تسريب مياه'),
                      _buildInfoRow('أقرب عنوان:', 'القطعة 22 - بلوك 04 - بحري، الخرطوم'),
                      _buildInfoRow('الجهة المسؤولة:', 'هيئة المياه'),
                      _buildInfoRow('تاريخ الإرسال:', '20 ابريل 2026 الساعة 10:15 ص'),
                    ],
                  ),
                ),
                
                const SizedBox(height: 30),
                Text(
                  'يمكنك تتبع حالة الطلب من الرابط أدناه بالضغط على\nتتبع:',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.cairo(fontSize: 12, color: Colors.grey),
                ),
                
                const SizedBox(height: 40),
                
                // Track Button
                ElevatedButton(
                  onPressed: () => Navigator.pushNamed(context, '/recent_reports'),
                  style: ElevatedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    backgroundColor: AppColors.primary,
                  ),
                  child: Text('تتبع حالة الطلب', style: GoogleFonts.cairo(fontWeight: FontWeight.bold, color: Colors.white)),
                ),
                
                const SizedBox(height: 12),
                
                // Return Home Button
                TextButton(
                  onPressed: () => Navigator.of(context).pushNamedAndRemoveUntil('/home', (route) => false),
                  child: Text(
                    'العودة للصفحة الرئيسية',
                    style: GoogleFonts.cairo(fontWeight: FontWeight.bold, color: AppColors.primary),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: GoogleFonts.cairo(fontWeight: FontWeight.bold, fontSize: 13)),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              value,
              style: GoogleFonts.cairo(fontSize: 13, color: AppColors.textSecondary),
              textAlign: TextAlign.left,
            ),
          ),
        ],
      ),
    );
  }
}
