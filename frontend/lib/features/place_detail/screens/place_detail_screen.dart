import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';
import 'package:geolocator/geolocator.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../../core/di.dart' as di;

class PlaceDetailScreen extends StatefulWidget {
  final String placeId;
  const PlaceDetailScreen({super.key, required this.placeId});

  @override
  State<PlaceDetailScreen> createState() => _PlaceDetailScreenState();
}

class _PlaceDetailScreenState extends State<PlaceDetailScreen> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadPlace();
  }

  Future<void> _loadPlace() async {
    setState(() { _loading = true; _error = null; });
    try {
      final api = di.sl<ApiClient>();
      final data = await api.getPlaceDetail(widget.placeId);
      try {
        final lat = double.tryParse('${data['lat'] ?? ''}') ?? 15.5871;
        final lng = double.tryParse('${data['lng'] ?? ''}') ?? 32.5232;
        final card = await api.radiusSearch(lat: lat, lng: lng, radius: 500, limit: 0);
        data['nearby_within_500m'] = card['count'] ?? 0;
      } catch (_) {
        data['nearby_within_500m'] = 0;
      }
      if (mounted) setState(() { _data = data; _loading = false; });
    } catch (e) {
      if (mounted) setState(() { _error = 'فشل تحميل البيانات'; _loading = false; });
    }
  }

  Future<void> _confirm() async {
    try {
      final api = di.sl<ApiClient>();
      try {
        final pos = await Geolocator.getCurrentPosition();
        await api.confirmPlace(widget.placeId, userLat: pos.latitude, userLng: pos.longitude);
      } catch (_) {
        await api.confirmPlace(widget.placeId);
      }
      _loadPlace();
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('✅ تم التأكيد'), backgroundColor: AppColors.success));
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) { if (mounted) { context.push('/login'); } return; }
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('فشل التأكيد'), backgroundColor: AppColors.error));
    } catch (_) {}
  }

  Future<void> _flag() async {
    final reason = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('الإبلاغ عن خطأ'),
        content: TextField(
          textDirection: TextDirection.rtl,
          decoration: const InputDecoration(hintText: 'اكتب سبب الإبلاغ...', hintTextDirection: TextDirection.rtl),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
          ElevatedButton(onPressed: () => Navigator.pop(ctx, 'معلومات غير صحيحة'), child: const Text('إرسال')),
        ],
      ),
    );
    if (reason == null) return;
    try {
      final api = di.sl<ApiClient>();
      await api.flagPlace(widget.placeId, reason);
      _loadPlace();
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('🚩 تم الإبلاغ، شكراً'), backgroundColor: AppColors.success));
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) { if (mounted) { context.push('/login'); } return; }
    } catch (_) {}
  }

  Future<void> _vote() async {
    final rating = await showDialog<int>(
      context: context,
      builder: (ctx) => SimpleDialog(
        title: const Text('تقييم المكان', textAlign: TextAlign.center),
        children: [1, 2, 3, 4, 5].map((r) => SimpleDialogOption(
          onPressed: () => Navigator.pop(ctx, r),
          child: Text('⭐' * r, textAlign: TextAlign.center, style: const TextStyle(fontSize: 24)),
        )).toList(),
      ),
    );
    if (rating == null) return;
    try {
      final api = di.sl<ApiClient>();
      await api.votePlace(widget.placeId, rating);
      _loadPlace();
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('⭐ تم التقييم: $rating/5'), backgroundColor: AppColors.gold));
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) { if (mounted) { context.push('/login'); } return; }
    } catch (_) {}
  }

  Future<void> _suggestEdit() async {
    final field = await showDialog<String>(
      context: context,
      builder: (ctx) => SimpleDialog(
        title: const Text('اختر الحقل المراد تعديله', textAlign: TextAlign.center),
        children: [
          SimpleDialogOption(onPressed: () => Navigator.pop(ctx, 'name'), child: const Text('📝 الاسم', textAlign: TextAlign.center)),
          SimpleDialogOption(onPressed: () => Navigator.pop(ctx, 'description'), child: const Text('📋 الوصف', textAlign: TextAlign.center)),
        ],
      ),
    );
    if (field == null) return;

    final newValue = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('القيمة الجديدة'),
        content: TextField(textDirection: TextDirection.rtl, decoration: const InputDecoration(hintText: 'اكتب القيمة الجديدة...')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إلغاء')),
          ElevatedButton(onPressed: () => Navigator.pop(ctx, 'updated'), child: const Text('إرسال الاقتراح')),
        ],
      ),
    );
    if (newValue == null) return;

    try {
      final api = di.sl<ApiClient>();
      await api.suggestEdit(widget.placeId, field, newValue);
      _loadPlace();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('✏️ تم إرسال اقتراح التعديل')));
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) { if (mounted) context.push('/login'); return; }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('تفاصيل'),
        actions: [IconButton(icon: const Icon(Icons.refresh), onPressed: _loadPlace)],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                  const Icon(Icons.cloud_off, size: 48, color: AppColors.textLight),
                  const SizedBox(height: 12),
                  Text(_error!, style: const TextStyle(color: AppColors.textGray)),
                  const SizedBox(height: 12),
                  ElevatedButton(onPressed: _loadPlace, child: const Text('إعادة المحاولة')),
                ]))
              : RefreshIndicator(
                  onRefresh: _loadPlace,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Card(
                          child: Padding(
                            padding: const EdgeInsets.all(20),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(_data!['name'] ?? '', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.textDark)),
                                const SizedBox(height: 4),
                                Row(children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(color: AppColors.blue.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6)),
                                    child: Text(_data!['layer'] ?? '', style: TextStyle(fontSize: 12, color: AppColors.blue, fontWeight: FontWeight.w600)),
                                  ),
                                  const SizedBox(width: 8),
                                  if (_data!['status'] != null)
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: (_data!['status'] == 'verified' || _data!['status'] == 'active')
                                            ? AppColors.success.withValues(alpha: 0.1) : AppColors.warning.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        _data!['status'] == 'verified' || _data!['status'] == 'active' ? '✅ موثّق' : '⏳ قيد المراجعة',
                                        style: TextStyle(fontSize: 12,
                                          color: _data!['status'] == 'verified' || _data!['status'] == 'active' ? AppColors.success : AppColors.warning,
                                          fontWeight: FontWeight.w600),
                                      ),
                                    ),
                                ]),
                                const SizedBox(height: 12),
                                _infoRow(Icons.location_on_outlined, _data!['neighborhood'] ?? ''),
                                _infoRow(Icons.business_outlined, _data!['locality'] ?? ''),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        Row(children: [
                          _statCard(Icons.verified_outlined, '${_data!['confirmations'] ?? 0}', 'تأكيد', AppColors.success),
                          const SizedBox(width: 8),
                          _statCard(Icons.star_outline, '${_data!['avg_rating'] ?? '—'}', 'تقييم', AppColors.gold),
                          const SizedBox(width: 8),
                          _statCard(Icons.near_me_outlined, '${_data!['nearby_within_500m'] ?? 0}', 'بالقرب', AppColors.blue),
                        ]),
                        const SizedBox(height: 16),
                        Row(children: [
                          Expanded(child: ElevatedButton.icon(onPressed: _confirm, icon: const Icon(Icons.check_circle_outline), label: const Text('تأكيد'), style: ElevatedButton.styleFrom(backgroundColor: AppColors.success))),
                          const SizedBox(width: 8),
                          Expanded(child: OutlinedButton.icon(onPressed: _flag, icon: const Icon(Icons.flag_outlined), label: const Text('إبلاغ'), style: OutlinedButton.styleFrom(foregroundColor: AppColors.error, side: const BorderSide(color: AppColors.error)))),
                          const SizedBox(width: 8),
                          Expanded(child: OutlinedButton.icon(onPressed: _vote, icon: const Icon(Icons.star_outline), label: const Text('تقييم'), style: OutlinedButton.styleFrom(foregroundColor: AppColors.gold, side: const BorderSide(color: AppColors.gold)))),
                        ]),
                        const SizedBox(height: 8),
                        Row(children: [
                          Expanded(child: OutlinedButton.icon(onPressed: _suggestEdit, icon: const Icon(Icons.edit_outlined), label: const Text('✏️ اقتراح تعديل'), style: OutlinedButton.styleFrom(foregroundColor: AppColors.blue, side: const BorderSide(color: AppColors.blue)))),
                        ]),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _infoRow(IconData icon, String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(textDirection: TextDirection.rtl, children: [
        Icon(icon, size: 18, color: AppColors.textGray),
        const SizedBox(width: 8),
        Text(text, style: TextStyle(color: AppColors.textDark)),
      ]),
    );
  }

  Widget _statCard(IconData icon, String value, String label, Color color) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
          child: Column(children: [
            Icon(icon, color: color, size: 24),
            const SizedBox(height: 6),
            Text(value, style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
            Text(label, style: TextStyle(fontSize: 11, color: AppColors.textGray)),
          ]),
        ),
      ),
    );
  }
}
