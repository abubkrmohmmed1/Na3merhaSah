import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/network/api_client.dart';
import '../../../core/storage/auth_storage_service.dart';

class RecentReportsScreen extends StatefulWidget {
  const RecentReportsScreen({super.key});

  @override
  State<RecentReportsScreen> createState() => _RecentReportsScreenState();
}

class _RecentReportsScreenState extends State<RecentReportsScreen> {
  final ApiClient _api = ApiClient();
  List<dynamic> _reports = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadReports();
  }

  Future<void> _loadReports() async {
    try {
      final response = await _api.getReports();
      setState(() {
        _reports = response['data'] ?? [];
        _isLoading = false;
      });
    } catch (e) {
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
          backgroundColor: Colors.grey.shade600,
          title: Text('البلاغات السابقة', style: GoogleFonts.cairo(fontWeight: FontWeight.bold)),
          elevation: 0,
        ),
        drawer: Drawer(
          child: ListView(
            padding: EdgeInsets.zero,
            children: [
              const DrawerHeader(
                decoration: BoxDecoration(color: Colors.grey),
                child: Center(child: Text('نعمرها صح', style: TextStyle(color: Colors.white, fontSize: 20))),
              ),
              ListTile(
                leading: const Icon(Icons.home),
                title: Text('الرئيسية', style: GoogleFonts.cairo()),
                onTap: () => Navigator.pushNamedAndRemoveUntil(context, '/home', (route) => false),
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
        ),
        body: _isLoading 
          ? const Center(child: CircularProgressIndicator())
          : _reports.isEmpty 
            ? Center(child: Text('لا توجد بلاغات سابقة', style: GoogleFonts.cairo()))
            : ListView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                itemCount: _reports.length,
                itemBuilder: (context, index) => _buildReportListItem(_reports[index]),
              ),
      ),
    );
  }

  Widget _buildReportListItem(dynamic data) {
    final statusTranslation = {
      'started': 'البدء',
      'govt_received': 'مستلمة',
      'resolved': 'مكتمل',
    };

    final statusColor = data['status'] == 'resolved' ? Colors.green : Colors.orange;

    final String reportId = (data['id']?.toString() ?? '').substring(0, 6).toUpperCase();
    final String title = data['description']?.toString() ?? 'بلاغ';
    
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: ListTile(
        title: Text('#$reportId: $title', style: GoogleFonts.cairo(fontWeight: FontWeight.bold)),
        subtitle: Text(statusTranslation[data['status']] ?? 'غير معروف', style: TextStyle(color: statusColor)),
      ),
    );
  }
}
