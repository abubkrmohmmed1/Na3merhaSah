import 'package:flutter/material.dart';
import 'core/theme/app_theme.dart';
import 'core/storage/local_report_service.dart';
import 'core/storage/auth_storage_service.dart';
import 'core/utils/permission_service.dart';
import 'features/auth/screens/login_screen.dart';
import 'features/reporting/screens/home_screen.dart';
import 'features/reporting/screens/report_issue_screen.dart';
import 'features/address_picker/screens/address_picker_screen.dart';
import 'features/reporting/screens/recent_reports_screen.dart';
import 'features/reporting/screens/success_screen.dart';
import 'features/auth/screens/signup_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await LocalReportService.init();
  await AuthStorageService.init();
  await PermissionService.requestAllPermissions();
  
  // التحقق من وجود توكن مسبق لتثبيت تسجيل الدخول
  final bool isLoggedIn = AuthStorageService.getToken() != null;
  
  runApp(NamerhaSahApp(isLoggedIn: isLoggedIn));
}

class NamerhaSahApp extends StatelessWidget {
  final bool isLoggedIn;
  const NamerhaSahApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'نعمرها صح',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      initialRoute: isLoggedIn ? '/home' : '/login',
      routes: {
        '/login': (context) => const LoginScreen(),
        '/home': (context) => const HomeScreen(),
        '/address_picker': (context) => const AddressPickerScreen(),
        '/report': (context) => const ReportIssueScreen(),
        '/recent_reports': (context) => const RecentReportsScreen(),
        '/success': (context) => const SuccessScreen(),
        '/signup': (context) => const SignUpScreen(),
      },
    );
  }
}
