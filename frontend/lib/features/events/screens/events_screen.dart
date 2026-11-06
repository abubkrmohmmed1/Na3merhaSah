import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../../core/di.dart' as di;

class EventsScreen extends StatefulWidget {
  const EventsScreen({super.key});

  @override
  State<EventsScreen> createState() => _EventsScreenState();
}

class _EventsScreenState extends State<EventsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  List<dynamic> _activeEvents = [];
  List<dynamic> _pastEvents = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadEvents();
  }

  Future<void> _loadEvents() async {
    setState(() => _loading = true);
    try {
      Position? pos;
      try { pos = await Geolocator.getCurrentPosition(); } catch (_) {}

      final api = di.sl<ApiClient>();
      
      final resActive = await api.dio.get('/mobile/events', queryParameters: {
        'tab': 'active',
        if (pos != null) 'lat': pos.latitude,
        if (pos != null) 'lng': pos.longitude,
      });

      final resPast = await api.dio.get('/mobile/events', queryParameters: {
        'tab': 'past',
        if (pos != null) 'lat': pos.latitude,
        if (pos != null) 'lng': pos.longitude,
      });

      if (mounted) {
        setState(() {
          _activeEvents = resActive.data['events'] ?? [];
          _pastEvents = resPast.data['events'] ?? [];
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الفعاليات والتحديات 🏆'),
        centerTitle: true,
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'فعاليات نشطة'),
            Tab(text: 'فعاليات سابقة'),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabController,
              children: [
                _buildEventList(_activeEvents, true),
                _buildEventList(_pastEvents, false),
              ],
            ),
    );
  }

  Widget _buildEventList(List<dynamic> events, bool isActive) {
    if (events.isEmpty) return _buildEmptyState(isActive);
    return RefreshIndicator(
      onRefresh: _loadEvents,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: events.length,
        itemBuilder: (ctx, i) => _buildEventCard(events[i], isActive),
      ),
    );
  }

  Widget _buildEmptyState(bool isActive) {
    return Center(
      child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        Icon(isActive ? Icons.event_available_outlined : Icons.history, size: 80, color: AppColors.textLight),
        const SizedBox(height: 16),
        Text(isActive ? 'لا توجد فعاليات نشطة حالياً' : 'لا توجد فعاليات سابقة', style: const TextStyle(color: AppColors.textGray)),
      ]),
    );
  }

  Widget _buildEventCard(Map<String, dynamic> event, bool isActive) {
    final type = event['type'];
    final reward = event['reward_points'] ?? 0;
    final isResolved = event['is_resolved'] == true || event['is_resolved'] == 1;
    
    Color color = AppColors.gold;
    if (type == 'competition') color = AppColors.blue;
    else if (type == 'community') color = AppColors.success;

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(event['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textDark)),
              Text(_getTypeLabel(type), style: TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.w600)),
            ])),
            if (reward > 0)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(color: AppColors.gold.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(8)),
                child: Text('+$reward XP', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.navy)),
              ),
          ]),
          const SizedBox(height: 12),
          Text(event['description'] ?? '', style: const TextStyle(fontSize: 13, color: AppColors.textGray)),
          const SizedBox(height: 16),
          Row(children: [
            if (isActive) ...[
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => _handleJoin(event),
                  icon: const Icon(Icons.play_arrow_rounded),
                  label: const Text('المشاركة'),
                  style: ElevatedButton.styleFrom(backgroundColor: AppColors.navy, foregroundColor: Colors.white),
                ),
              ),
              const SizedBox(width: 8),
            ],
            Expanded(
              child: OutlinedButton.icon(
                onPressed: () => context.push('/event/${event['id']}/leaderboard'),
                icon: Icon(isActive ? Icons.leaderboard_outlined : Icons.emoji_events_outlined),
                label: Text(isActive ? 'المتصدرون' : 'النتائج'),
                style: OutlinedButton.styleFrom(foregroundColor: AppColors.navy, side: const BorderSide(color: AppColors.navy)),
              ),
            ),
          ]),
        ]),
      ),
    );
  }

  String _getTypeLabel(String? type) {
    switch (type) {
      case 'competition': return 'مسابقة كبرى';
      case 'challenge': return 'تحدي ميداني';
      case 'community': return 'نشاط مجتمعي';
      case 'announcement': return 'إعلان عام';
      default: return 'فعالية';
    }
  }

  String _getRemainingTime(String? endsAt) {
    if (endsAt == null) return 'مستمر';
    final end = DateTime.parse(endsAt);
    final diff = end.difference(DateTime.now());
    if (diff.isNegative) return 'منتهية';
    if (diff.inDays > 0) return 'ينتهي خلال ${diff.inDays} يوم';
    return 'ينتهي خلال ${diff.inHours} ساعة';
  }

  void _handleJoin(Map<String, dynamic> event) {
    final type = event['type'];
    final executionMode = event['execution_mode'];
    final targetLayer = event['target_layer'];
    final eventId = event['id'];

    if (type == 'announcement') {
      _showAnnouncementDetail(event);
      return;
    }

    // Logic for boundary challenges
    if (targetLayer == 'الأحياء' || targetLayer == 'القرى والمدن') {
      context.push('/add?layer=$targetLayer&polygon=true&walking=${event['execution_mode'] == 'walking_gps'}');
      return;
    }

    // Default to map with event context
    context.go('/map?event_id=$eventId');
  }

  void _showAnnouncementDetail(Map<String, dynamic> event) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(event['title'] ?? ''),
        content: Text(event['description'] ?? ''),
        actions: [TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('إغلاق'))],
      ),
    );
  }
}

// Dummy LatLng since we don't have latlong package in scope, but we use google_maps_flutter LatLng in real app
class LatLng {
  final double latitude;
  final double longitude;
  const LatLng(this.latitude, this.longitude);
}
