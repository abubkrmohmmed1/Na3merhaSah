import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_drawer.dart';
import '../bloc/report_bloc.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => ReportBloc()..add(FetchReports()),
      child: const HomeView(),
    );
  }
}

class HomeView extends StatelessWidget {
  const HomeView({super.key});

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          backgroundColor: AppColors.headerBg,
          elevation: 0,
          leading: Builder(
            builder: (context) => IconButton(
              icon: const Icon(Icons.menu, color: Colors.white, size: 20),
              onPressed: () => Scaffold.of(context).openDrawer(),
            ),
          ),
        ),
        drawer: const AppDrawer(),
        body: SingleChildScrollView(
          child: Column(
            children: [
              const SizedBox(height: 30),
              Center(
                child: Column(
                  children: [
                    Container(
                      width: 80, height: 80,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.grey.shade100, width: 2),
                      ),
                      child: ClipOval(
                        child: Image.asset(
                          'assets/images/logo.jpeg',
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'نعمرها صح',
                      style: GoogleFonts.cairo(
                        fontSize: 22, 
                        fontWeight: FontWeight.bold, 
                        color: AppColors.primaryBlue
                      ),
                    ),
                    Text(
                      'معا لسودان افضل',
                      style: GoogleFonts.cairo(
                        fontSize: 12, 
                        fontWeight: FontWeight.w600,
                        color: AppColors.primaryBlue
                      ),
                    ),
                  ],
                ),
              ),
              
              const SizedBox(height: 40),
              
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 40),
                child: Align(
                  alignment: Alignment.centerRight,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'مرحبا بك يا محمد',
                        style: GoogleFonts.cairo(
                          fontSize: 18, 
                          fontWeight: FontWeight.w900, 
                          color: Colors.black87
                        ),
                      ),
                      Text(
                        'كيف يمكننا مساعدتك؟',
                        style: GoogleFonts.cairo(
                          fontSize: 14, 
                          fontWeight: FontWeight.w700,
                          color: Colors.black54
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 30),

              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 40),
                child: Column(
                  children: [
                    _buildMainCard(
                      context: context,
                      title: 'الابلاغ عن مشكلة',
                      subtitle: 'الابلاغ بسرعة عن مشكلة جديدة',
                      isPrimary: true,
                      onTap: () => Navigator.pushNamed(context, '/address_picker'),
                    ),
                    const SizedBox(height: 20),
                    _buildMainCard(
                      context: context,
                      title: 'التحقق من التقارير السابقة',
                      subtitle: 'تتبع حالة التقارير السابقة',
                      isPrimary: false,
                      onTap: () => Navigator.pushNamed(context, '/recent_reports'),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMainCard({
    required BuildContext context,
    required String title,
    required String subtitle,
    required bool isPrimary,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 25),
        decoration: BoxDecoration(
          gradient: isPrimary ? const LinearGradient(
            colors: [Color(0xFF5B8EF7), Color(0xFF2E6FF2)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ) : null,
          color: isPrimary ? null : Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
          border: isPrimary ? null : Border.all(color: Colors.grey.shade50),
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: GoogleFonts.cairo(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: isPrimary ? Colors.white : Colors.black87,
                    ),
                  ),
                  Text(
                    subtitle,
                    style: GoogleFonts.cairo(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: isPrimary ? Colors.white.withValues(alpha: 0.9) : Colors.black54,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 10),
            Container(
              width: 80, height: 60,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(
                isPrimary ? Icons.map_outlined : Icons.find_in_page_outlined,
                size: 45,
                color: isPrimary ? Colors.white : AppColors.primaryBlue,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
