import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:image_picker/image_picker.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'dart:io';
import '../../../core/theme/app_theme.dart';
import '../../../core/network/api_client.dart';
import '../bloc/report_bloc.dart';

class ReportIssueScreen extends StatelessWidget {
  const ReportIssueScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider.value(
      value: BlocProvider.of<ReportBloc>(context),
      child: const ReportIssueView(),
    );
  }
}

class ReportIssueView extends StatefulWidget {
  const ReportIssueView({super.key});

  @override
  State<ReportIssueView> createState() => _ReportIssueViewState();
}

class _ReportIssueViewState extends State<ReportIssueView> {
  final _descriptionController = TextEditingController();
  final ApiClient _api = ApiClient();
  final ImagePicker _picker = ImagePicker();
  
  int? _selectedCategory;
  bool _isSubmitting = false;
  List<File> _selectedImages = [];

  double? _lat;
  double? _lng;
  String _address = "جاري جلب العنوان...";
  final Map<String, dynamic> _workflowData = {};
  final Map<String, DateTime> _stepTimestamps = {};

  final List<Map<String, dynamic>> _categories = [
    {'id': 1, 'name': 'مياه', 'icon': Icons.water_drop},
    {'id': 2, 'name': 'كهرباء', 'icon': Icons.bolt},
    {'id': 3, 'name': 'طرق', 'icon': Icons.edit_road},
    {'id': 4, 'name': 'صرف صحي', 'icon': Icons.plumbing},
    {'id': 5, 'name': 'مباني', 'icon': Icons.apartment},
    {'id': 6, 'name': 'طوارئ', 'icon': Icons.warning_amber},
  ];

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
    if (args != null) {
      _lat = args['lat'];
      _lng = args['lng'];
      _address = args['address'] ?? "عنوان غير محدد";
    }
  }

  @override
  void dispose() {
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final XFile? image = await _picker.pickImage(source: ImageSource.camera, imageQuality: 50);
    if (image != null) {
      setState(() {
        _selectedImages.add(File(image.path));
      });
    }
  }

  Future<void> _submitReport() async {
    if (_selectedCategory == null || _lat == null || _lng == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('يرجى إكمال البيانات واختيار الموقع')),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      context.read<ReportBloc>().add(SubmitReport(
        lat: _lat!,
        lng: _lng!,
        description: _descriptionController.text,
        categoryId: _selectedCategory!,
        images: _selectedImages.map((f) => f.path).toList(),
        workflowStep: 'review_submit',
        workflowData: _workflowData,
      ));

      if (mounted) {
        Navigator.of(context).pushReplacementNamed('/success');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('فشل الإرسال: تأكد من الاتصال بالخادم')),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
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
          title: Text('تفاصيل البلاغ', style: GoogleFonts.cairo(fontWeight: FontWeight.w700)),
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildMiniMap(),
              const SizedBox(height: 12),
              _buildLocationCard(),
              const SizedBox(height: 20),
              Text('نوع البلاغ', style: GoogleFonts.cairo(fontSize: 16, fontWeight: FontWeight.w700)),
              const SizedBox(height: 12),
              _buildCategoryGrid(),
              const SizedBox(height: 20),
              Text('وصف المشكلة', style: GoogleFonts.cairo(fontSize: 16, fontWeight: FontWeight.w700)),
              const SizedBox(height: 8),
              TextField(controller: _descriptionController, maxLines: 4),
              const SizedBox(height: 20),
              Text('إرفاق صور', style: GoogleFonts.cairo(fontSize: 16, fontWeight: FontWeight.w700)),
              const SizedBox(height: 8),
              _buildImageUploadArea(),
              const SizedBox(height: 32),
              ElevatedButton(
                onPressed: _isSubmitting ? null : _submitReport,
                child: _isSubmitting ? const CircularProgressIndicator() : Text('إرسال البلاغ', style: GoogleFonts.cairo()),
              ),
            ],
          ),
        ),
      ),
    );
  }
  
  Widget _buildMiniMap() { /* ... كما هو سابقاً ... */ return const SizedBox.shrink(); }
  Widget _buildLocationCard() { /* ... كما هو سابقاً ... */ return const SizedBox.shrink(); }
  Widget _buildCategoryGrid() { /* ... كما هو سابقاً ... */ return const SizedBox.shrink(); }
  Widget _buildImageUploadArea() { /* ... كما هو سابقاً ... */ return const SizedBox.shrink(); }
}
