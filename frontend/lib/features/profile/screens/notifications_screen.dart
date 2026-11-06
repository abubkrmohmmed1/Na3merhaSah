import 'package:flutter/material.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../../core/di.dart' as di;

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _loading = true;
  List<dynamic> _notifications = [];

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    try {
      final api = di.sl<ApiClient>();
      final res = await api.dio.get('/mobile/notifications');
      if (mounted) {
        setState(() {
          _notifications = res.data['notifications'] ?? [];
          _loading = false;
        });
        // Mark as read after loading
        if (_notifications.any((n) => n['read_at'] == null)) {
          api.dio.post('/mobile/notifications/read');
        }
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('التنبيهات 🔔'),
        centerTitle: true,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadNotifications,
              child: _notifications.isEmpty
                  ? _buildEmptyState()
                  : ListView.separated(
                      padding: const EdgeInsets.all(16),
                      itemCount: _notifications.length,
                      separatorBuilder: (ctx, i) => const SizedBox(height: 12),
                      itemBuilder: (ctx, i) => _buildNotificationCard(_notifications[i]),
                    ),
            ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(mainAxisAlignment: MainAxisAlignment.center, children: const [
        Icon(Icons.notifications_none_rounded, size: 80, color: AppColors.textLight),
        SizedBox(height: 16),
        Text('لا توجد تنبيهات حالياً', style: TextStyle(color: AppColors.textGray)),
      ]),
    );
  }

  Widget _buildNotificationCard(Map<String, dynamic> n) {
    final type = n['type'];
    final isUnread = n['read_at'] == null;
    
    IconData icon = Icons.info_outline_rounded;
    Color color = AppColors.blue;

    if (type == 'success') { icon = Icons.check_circle_outline_rounded; color = AppColors.success; }
    else if (type == 'reward') { icon = Icons.emoji_events_outlined; color = AppColors.gold; }
    else if (type == 'event') { icon = Icons.campaign_outlined; color = AppColors.navy; }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: isUnread ? Border.all(color: color.withValues(alpha: 0.3), width: 1.5) : null,
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 10, offset: const Offset(0, 4))],
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.all(16),
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(color: color.withValues(alpha: 0.1), shape: BoxShape.circle),
          child: Icon(icon, color: color),
        ),
        title: Text(n['title'] ?? '', style: TextStyle(fontWeight: isUnread ? FontWeight.bold : FontWeight.normal, fontSize: 14, color: AppColors.textDark)),
        subtitle: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const SizedBox(height: 4),
          Text(n['message'] ?? '', style: const TextStyle(fontSize: 12, color: AppColors.textGray)),
          const SizedBox(height: 8),
          Text(n['created_at'] ?? '', style: const TextStyle(fontSize: 10, color: AppColors.textLight)),
        ]),
        onTap: () {
          // Handle navigation if payload exists
          final payload = n['payload'] as Map?;
          if (payload != null && payload.containsKey('event_id')) {
             // context.push('/event/${payload['event_id']}/leaderboard');
          }
        },
      ),
    );
  }
}
