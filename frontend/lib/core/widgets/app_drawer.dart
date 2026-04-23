import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/storage/auth_storage_service.dart';

class AppDrawer extends StatelessWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          DrawerHeader(
            decoration: BoxDecoration(color: Theme.of(context).primaryColor),
            child: Center(
              child: Text(
                'نعمرها صح', 
                style: GoogleFonts.cairo(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)
              )
            ),
          ),
          ListTile(
            leading: const Icon(Icons.home),
            title: Text('الرئيسية', style: GoogleFonts.cairo()),
            onTap: () => Navigator.pushNamedAndRemoveUntil(context, '/home', (route) => false),
          ),
          ListTile(
            leading: const Icon(Icons.history),
            title: Text('البلاغات السابقة', style: GoogleFonts.cairo()),
            onTap: () => Navigator.pushNamed(context, '/recent_reports'),
          ),
          const Divider(),
          ListTile(
            leading: const Icon(Icons.exit_to_app),
            title: Text('تسجيل الخروج', style: GoogleFonts.cairo()),
            onTap: () async {
              await AuthStorageService.logout();
              Navigator.pushNamedAndRemoveUntil(context, '/login', (route) => false);
            },
          ),
        ],
      ),
    );
  }
}
