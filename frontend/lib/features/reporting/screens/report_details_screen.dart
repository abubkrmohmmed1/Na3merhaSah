import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/theme/app_theme.dart';
import '../bloc/report_bloc.dart';

class ReportDetailsScreen extends StatefulWidget {
  final Map<String, dynamic> report;

  const ReportDetailsScreen({super.key, required this.report});

  @override
  State<ReportDetailsScreen> createState() => _ReportDetailsScreenState();
}

class _ReportDetailsScreenState extends State<ReportDetailsScreen> {
  int _rating = 0;
  final TextEditingController _feedbackController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    final report = widget.report;
    final String status = report['status'] ?? 'started';
    final String title = report['title'] ?? 'بلاغ مدني';
    final String address = report['digital_address'] ?? 'غير محدد';
    final String date = report['created_at'] ?? '';
    final String? surveyorNotes = report['surveyor_notes'];
    final List<dynamic>? surveyorImages = report['surveyor_images'];

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: const Color(0xFFF9FAFB),
        appBar: AppBar(
          backgroundColor: Colors.white,
          elevation: 0,
          title: Text(
            'تفاصيل البلاغ',
            style: GoogleFonts.cairo(color: Colors.black, fontWeight: FontWeight.bold),
          ),
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: Colors.black),
            onPressed: () => Navigator.pop(context),
          ),
        ),
        body: BlocListener<ReportBloc, ReportState>(
          listener: (context, state) {
            if (state is FeedbackSubmitted) {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('تم إرسال تقييمك بنجاح. شكراً لك!')),
              );
              Navigator.pop(context);
            } else if (state is ReportError) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text(state.message), backgroundColor: Colors.red),
              );
            }
          },
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildStatusHeader(status, date),
                const SizedBox(height: 20),
                _buildSectionTitle('بيانات الموقع'),
                _buildInfoCard(Icons.location_on_outlined, 'العنوان الرقمي', address),
                const SizedBox(height: 20),
                _buildSectionTitle('بيانات الشكوى'),
                _buildInfoCard(Icons.description_outlined, 'الوصف', report['description'] ?? 'بدون وصف'),
                
                if (surveyorNotes != null) ...[
                  const SizedBox(height: 20),
                  _buildSectionTitle('تقرير المعاينة الميدانية'),
                  _buildSurveyorCard(surveyorNotes, surveyorImages),
                ],

                const SizedBox(height: 30),
                if (status == 'resolved') ...[
                  _buildSectionTitle('تقييم الخدمة'),
                  _buildFeedbackForm(report['id']),
                ] else ...[
                   _buildTimelinePlaceholder(status),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatusHeader(String status, String date) {
    Color statusColor = AppColors.primaryBlue;
    String statusText = 'قيد المعالجة';
    
    if (status == 'resolved') {
      statusColor = Colors.green;
      statusText = 'تم الحل بنجاح';
    } else if (status.contains('surveyor') || status == 'admin_approval') {
      statusColor = Colors.orange;
      statusText = 'مرحلة المعاينة والاعتماد';
    }

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: statusColor.withOpacity(0.1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: statusColor.withOpacity(0.2)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: statusColor,
            child: const Icon(Icons.check, color: Colors.white),
          ),
          const SizedBox(width: 15),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  statusText,
                  style: GoogleFonts.cairo(fontWeight: FontWeight.bold, color: statusColor, fontSize: 16),
                ),
                Text(
                  'آخر تحديث: $date',
                  style: GoogleFonts.cairo(fontSize: 12, color: Colors.grey[600]),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10, right: 5),
      child: Text(
        title,
        style: GoogleFonts.cairo(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.black87),
      ),
    );
  }

  Widget _buildInfoCard(IconData icon, String label, String value) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 5)],
      ),
      child: Row(
        children: [
          Icon(icon, color: AppColors.primaryBlue, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: GoogleFonts.cairo(fontSize: 10, color: Colors.grey)),
                Text(value, style: GoogleFonts.cairo(fontSize: 13, fontWeight: FontWeight.w600)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSurveyorCard(String notes, List<dynamic>? images) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.orange.withOpacity(0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.orange.withOpacity(0.1)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(notes, style: GoogleFonts.cairo(fontSize: 13)),
          if (images != null && images.isNotEmpty) ...[
            const SizedBox(height: 12),
            SizedBox(
              height: 80,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: images.length,
                itemBuilder: (context, index) => Container(
                  margin: const EdgeInsets.only(left: 8),
                  width: 80,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(8),
                    image: DecorationImage(
                      image: NetworkImage(images[index]),
                      fit: BoxFit.cover,
                    ),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  String? _selectedQuality;
  String? _selectedTime;
  String? _selectedBehavior;
  String? _selectedCleanliness;
  String? _selectedMainIssue;

  Widget _buildFeedbackForm(String reportId) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10)],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Text(
              'كيف تقيم الخدمة بشكل عام؟',
              style: GoogleFonts.cairo(fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(5, (index) => IconButton(
              icon: Icon(
                index < _rating ? Icons.star : Icons.star_border,
                color: Colors.amber,
                size: 35,
              ),
              onPressed: () => setState(() => _rating = index + 1),
            )),
          ),
          const SizedBox(height: 20),

          _buildFeedbackOptionGroup('جودة التنفيذ الفني', ['ممتاز', 'جيد', 'ضعيف'], _selectedQuality, (v) => setState(() => _selectedQuality = v)),
          _buildFeedbackOptionGroup('الالتزام بالموعد', ['في الموعد', 'متأخر', 'مبكر'], _selectedTime, (v) => setState(() => _selectedTime = v)),
          _buildFeedbackOptionGroup('سلوك الفريق الميداني', ['محترم', 'عادي', 'غير لائق'], _selectedBehavior, (v) => setState(() => _selectedBehavior = v)),
          _buildFeedbackOptionGroup('نظافة الموقع بعد العمل', ['نظيف', 'مقبول', 'سيء'], _selectedCleanliness, (v) => setState(() => _selectedCleanliness = v)),

          if (_rating > 0 && _rating <= 3) ...[
            const SizedBox(height: 20),
            _buildFeedbackOptionGroup('ما هي المشكلة الرئيسية؟', ['تأخير', 'سوء تنفيذ', 'تعامل سيء', 'لم يتم الحل'], _selectedMainIssue, (v) => setState(() => _selectedMainIssue = v)),
          ],

          const SizedBox(height: 20),
          Text('ملاحظات إضافية', style: GoogleFonts.cairo(fontSize: 12, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          TextField(
            controller: _feedbackController,
            maxLines: 3,
            decoration: InputDecoration(
              hintText: 'اكتب هنا...',
              hintStyle: GoogleFonts.cairo(fontSize: 12),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              filled: true,
              fillColor: const Color(0xFFF9FAFB),
            ),
          ),
          const SizedBox(height: 25),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: (_rating == 0 || _selectedQuality == null) ? null : () {
                context.read<ReportBloc>().add(SubmitFeedback(
                  id: reportId,
                  rating: _rating,
                  feedback: _feedbackController.text,
                  quality: _selectedQuality,
                  time: _selectedTime,
                  behavior: _selectedBehavior,
                  cleanliness: _selectedCleanliness,
                  mainIssue: _selectedMainIssue,
                ));
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primaryBlue,
                padding: const EdgeInsets.symmetric(vertical: 15),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: Text('إرسال التقييم', style: GoogleFonts.cairo(fontWeight: FontWeight.bold, color: Colors.white)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFeedbackOptionGroup(String title, List<String> options, String? selectedValue, Function(String) onSelected) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Text(title, style: GoogleFonts.cairo(fontSize: 12, color: Colors.grey[700])),
        ),
        Wrap(
          spacing: 8,
          children: options.map((option) {
            bool isSelected = selectedValue == option;
            return ChoiceChip(
              label: Text(option, style: GoogleFonts.cairo(fontSize: 11, color: isSelected ? Colors.white : Colors.black87)),
              selected: isSelected,
              selectedColor: AppColors.primaryBlue,
              backgroundColor: Colors.grey[100],
              onSelected: (bool selected) {
                if (selected) onSelected(option);
              },
            );
          }).toList(),
        ),
        const SizedBox(height: 10),
      ],
    );
  }

  Widget _buildTimelinePlaceholder(String status) {
    return Center(
      child: Text(
        'جاري العمل على بلاغك، سيتاح التقييم فور اكتمال الحل.',
        textAlign: TextAlign.center,
        style: GoogleFonts.cairo(fontSize: 12, color: Colors.grey),
      ),
    );
  }
}
