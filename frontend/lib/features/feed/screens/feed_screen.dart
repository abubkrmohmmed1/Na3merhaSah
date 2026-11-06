import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../../core/di.dart' as di;

class FeedScreen extends StatefulWidget {
  const FeedScreen({super.key});

  @override
  State<FeedScreen> createState() => _FeedScreenState();
}

class _FeedScreenState extends State<FeedScreen> {
  Map<String, dynamic>? _profile;
  List<dynamic> _quests = [];
  List<dynamic> _events = [];
  bool _loading = true;
  int _tab = 0;
  Position? _userLocation;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final api = di.sl<ApiClient>();
      
      // Get location for events
      try {
        _userLocation = await Geolocator.getCurrentPosition(
          locationSettings: const LocationSettings(accuracy: LocationAccuracy.low)
        );
      } catch (_) {}

      final data = await api.getProfile();
      final qData = await api.getQuests();
      final evData = await api.getEvents(
        lat: _userLocation?.latitude,
        lng: _userLocation?.longitude,
      );

      if (mounted) {
        setState(() {
          _profile = data;
          _quests = (qData['quests'] as List?) ?? [];
          _events = (evData['events'] as List?) ?? [];
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _claimQuest(String questId) async {
    try {
      final api = di.sl<ApiClient>();
      final res = await api.claimQuest(questId);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? '🎉 تم الاستلام!'), backgroundColor: AppColors.gold),
        );
        _load();
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('النشاطات والفعاليات')),
      body: Column(children: [
        Container(
          margin: const EdgeInsets.all(16),
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(color: AppColors.divider, borderRadius: BorderRadius.circular(10)),
          child: Row(children: [
            _buildTabItem(0, '🏆 إنجازاتي'),
            _buildTabItem(1, '🎯 الفعاليات'),
            _buildTabItem(2, '🌍 المجتمع'),
          ]),
        ),
        Expanded(child: _loading
            ? const Center(child: CircularProgressIndicator())
            : RefreshIndicator(onRefresh: _load,
                child: _buildCurrentTab())),
      ]),
    );
  }

  Widget _buildTabItem(int index, String title) {
    final active = _tab == index;
    return Expanded(child: GestureDetector(
      onTap: () => setState(() => _tab = index),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(color: active ? Colors.white : Colors.transparent, borderRadius: BorderRadius.circular(8)),
        child: Text(title, textAlign: TextAlign.center,
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: active ? AppColors.navy : AppColors.textGray)),
      ),
    ));
  }

  Widget _buildCurrentTab() {
    switch (_tab) {
      case 0: return _buildMyFeed();
      case 1: return _buildEventsList();
      case 2: return _buildCommunityFeed();
      default: return const SizedBox.shrink();
    }
  }

  Widget _buildMyFeed() {
    final actions = (_profile?['recent_actions'] as List?) ?? [];
    final stats = (_profile?['stats'] as Map?) ?? {};
    final rank = _profile?['rank'] ?? 0;

    return ListView(padding: const EdgeInsets.all(16), children: [
      Card(
        child: Padding(padding: const EdgeInsets.all(16), child: Row(children: [
          _summaryStat('${stats['total_points'] ?? 0}', 'XP', AppColors.gold),
          _summaryStat('${stats['total_confirms'] ?? 0}', 'تأكيد', AppColors.success),
          _summaryStat('${stats['total_adds'] ?? 0}', 'إضافة', AppColors.blue),
          _summaryStat('#$rank', 'الترتيب', AppColors.navy),
        ])),
      ),
      const SizedBox(height: 12),

      if (_quests.isNotEmpty) ...[
        const Text('🏆 مهامي', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textDark)),
        const SizedBox(height: 8),
        ..._quests.map((q) => _buildQuestCard(q)),
        const SizedBox(height: 12),
      ],

      const Text('نشاطاتي الأخيرة', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textDark)),
      const SizedBox(height: 8),

      if (actions.isEmpty)
        const Card(child: Padding(padding: EdgeInsets.all(32), child: Center(child: Text('لا توجد نشاطات بعد', style: TextStyle(color: AppColors.textLight)))))
      else
        ...actions.take(15).map((a) => _buildActionTile(a)),
    ]);
  }

  Widget _buildEventsList() {
    if (_events.isEmpty) {
      return const Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Text('🎯', style: TextStyle(fontSize: 48)),
          SizedBox(height: 12),
          Text('لا توجد فعاليات نشطة في منطقتك حالياً', style: TextStyle(color: AppColors.textGray)),
        ]),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _events.length,
      itemBuilder: (ctx, i) {
        final ev = _events[i];
        final distance = ev['distance'] != null ? (ev['distance'] as num) : null;
        final type = ev['type'] ?? 'announcement';
        
        return Card(
          clipBehavior: Clip.antiAlias,
          margin: const EdgeInsets.only(bottom: 16),
          child: InkWell(
            onTap: () => _handleEventTap(ev),
            child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
              if (ev['image_url'] != null)
                Image.network(ev['image_url'], height: 120, fit: BoxFit.cover, errorBuilder: (_, __, ___) => Container(height: 100, color: AppColors.divider)),
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                    _badgeForType(type),
                    if (distance != null) Text('${(distance / 1000).toStringAsFixed(1)} كم بعيد', style: const TextStyle(fontSize: 11, color: AppColors.textGray)),
                  ]),
                  const SizedBox(height: 8),
                  Text(ev['title'] ?? '', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textDark)),
                  const SizedBox(height: 4),
                  Text(ev['description'] ?? '', style: const TextStyle(fontSize: 12, color: AppColors.textGray), maxLines: 2, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 12),
                  Row(children: [
                    if (ev['reward_points'] > 0) ...[
                      const Icon(Icons.stars, color: AppColors.gold, size: 16),
                      const SizedBox(width: 4),
                      Text('+${ev['reward_points']} XP', style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.gold, fontSize: 13)),
                      const SizedBox(width: 16),
                    ],
                    const Icon(Icons.calendar_today, size: 14, color: AppColors.textLight),
                    const SizedBox(width: 4),
                    Text(ev['starts_at'].toString().split(' ')[0], style: const TextStyle(fontSize: 11, color: AppColors.textLight)),
                  ]),
                ]),
              ),
            ]),
          ),
        );
      },
    );
  }

  void _handleEventTap(Map<String, dynamic> ev) {
    _showEventDetails(ev);
  }

  void _showEventDetails(Map<String, dynamic> ev) {
    final bool isBoundary = ev['type'] == 'challenge' && 
        ((ev['target_layer'] == 'الأحياء' || ev['target_layer'] == 'القرى والمدن') || 
         (ev['title'] ?? '').toString().contains('حدود') || 
         (ev['description'] ?? '').toString().contains('حدود'));

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.only(left: 24, right: 24, top: 24, bottom: 32),
        child: Column(
          mainAxisSize: MainAxisSize.min, 
          crossAxisAlignment: CrossAxisAlignment.stretch, 
          children: [
            Align(
              alignment: Alignment.center,
              child: Container(width: 40, height: 5, decoration: BoxDecoration(color: Colors.grey[300], borderRadius: BorderRadius.circular(10))),
            ),
            const SizedBox(height: 20),
            Text(
              ev['title'] ?? '', 
              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.navy), 
              textAlign: TextAlign.right
            ),
            const SizedBox(height: 12),
            Text(
              ev['description'] ?? '', 
              style: const TextStyle(fontSize: 14, color: Colors.black87, height: 1.5), 
              textAlign: TextAlign.right
            ),
            const SizedBox(height: 24),
            
            // Start Challenge / Event Button
            ElevatedButton(
              onPressed: () {
                Navigator.pop(ctx);
                if (isBoundary) {
                  final layer = ev['target_layer'] ?? 'الأحياء';
                  context.push('/add?layer=${Uri.encodeComponent(layer)}&polygon=true&challenge=true');
                } else {
                  // Standard challenge or competition
                  final lat = ev['latitude'];
                  final lng = ev['longitude'];
                  if (lat != null && lng != null) {
                    context.push('/map?lat=$lat&lng=$lng');
                  } else {
                    context.push('/map');
                  }
                }
              },
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 16),
                backgroundColor: isBoundary ? AppColors.gold : AppColors.navy,
                foregroundColor: isBoundary ? AppColors.navy : Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                elevation: 2,
              ),
              child: Text(
                isBoundary 
                    ? '⚡ ابدأ تحدي رسم الحدود الآن' 
                    : (ev['type'] == 'challenge' ? '⚡ ابدأ التحدي الآن' : '🗺️ المشاركة في الفعالية'),
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(height: 12),
            
            // Cancel / Got it Button
            TextButton(
              onPressed: () => Navigator.pop(ctx), 
              child: const Text('إغلاق التفاصيل', style: TextStyle(color: Colors.grey, fontSize: 13)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _badgeForType(String type) {
    Color bg = Colors.blue[50]!;
    Color text = Colors.blue[800]!;
    String label = 'إعلان';
    switch (type) {
      case 'competition': bg = Colors.orange[50]!; text = Colors.orange[800]!; label = '🏆 مسابقة'; break;
      case 'challenge': bg = Colors.purple[50]!; text = Colors.purple[800]!; label = '⚡ تحدي'; break;
      case 'community': bg = Colors.green[50]!; text = Colors.green[800]!; label = '🤝 مجتمعي'; break;
    }
    return Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2), decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(12)), child: Text(label, style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: text)));
  }

  Widget _buildCommunityFeed() {
    return FutureBuilder<List>(
      future: _loadCommunityFeed(),
      builder: (ctx, snap) {
        if (snap.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
        final feed = snap.data ?? [];
        if (feed.isEmpty) return const Center(child: Text('لا توجد نشاطات في المجتمع', style: TextStyle(color: AppColors.textLight)));

        return ListView.separated(
          padding: const EdgeInsets.all(16),
          itemCount: feed.length,
          separatorBuilder: (_, __) => const SizedBox(height: 6),
          itemBuilder: (_, i) {
            final item = feed[i];
            return _buildCommunityActionCard(item);
          },
        );
      },
    );
  }

  Future<List> _loadCommunityFeed() async {
    try {
      final api = di.sl<ApiClient>();
      final data = await api.getFeed();
      return data['feed'] as List? ?? [];
    } catch (_) { return []; }
  }

  // --- Helper UI components ---

  Widget _summaryStat(String val, String label, Color color) {
    return Expanded(child: Column(children: [
      Text(val, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
      Text(label, style: const TextStyle(fontSize: 10, color: AppColors.textGray)),
    ]));
  }

  Widget _buildQuestCard(dynamic q) {
    return Card(child: Padding(padding: const EdgeInsets.all(14), child: Row(children: [
      Text(q['icon'] ?? '📋', style: const TextStyle(fontSize: 24)),
      const SizedBox(width: 12),
      Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(q['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
        const SizedBox(height: 6),
        LinearProgressIndicator(value: (q['progress'] ?? 0) / (q['goal_count'] ?? 1).toDouble(), minHeight: 6, backgroundColor: AppColors.divider, valueColor: AlwaysStoppedAnimation(q['completed'] == true ? AppColors.success : AppColors.gold)),
        const SizedBox(height: 4),
        Row(children: [
          Text('${q['progress']}/${q['goal_count']}', style: const TextStyle(fontSize: 11, color: AppColors.textGray)),
          const Spacer(),
          if (q['completed'] == true && q['claimed'] != true)
            GestureDetector(onTap: () => _claimQuest(q['id']), child: Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4), decoration: BoxDecoration(color: AppColors.gold, borderRadius: BorderRadius.circular(8)), child: Text('استلم +${q['xp_reward']} XP', style: const TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.bold))))
          else if (q['claimed'] == true) const Text('✅ تم الاستلام', style: TextStyle(fontSize: 10, color: AppColors.success))
          else Text('+${q['xp_reward']} XP', style: const TextStyle(fontSize: 10, color: AppColors.gold)),
        ]),
      ])),
    ])));
  }

  Widget _buildActionTile(dynamic a) {
    return Card(child: ListTile(
      leading: CircleAvatar(backgroundColor: _colorForAction(a['type'] ?? '').withValues(alpha: 0.1), child: Icon(_iconForAction(a['type'] ?? ''), color: _colorForAction(a['type'] ?? ''), size: 20)),
      title: Text('${_actionText(a['type'] ?? '')} ${a['place_name'] ?? ''}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
      trailing: Text('+${a['points'] ?? 0}', style: const TextStyle(color: AppColors.gold, fontWeight: FontWeight.bold, fontSize: 13)),
      subtitle: Text(a['created_at'] ?? '', style: const TextStyle(fontSize: 11, color: AppColors.textGray)),
      dense: true,
    ));
  }

  Widget _buildCommunityActionCard(dynamic item) {
    return Card(child: ListTile(
      leading: CircleAvatar(backgroundColor: _colorForAction(item['action_type'] ?? '').withValues(alpha: 0.1), child: Icon(_iconForAction(item['action_type'] ?? ''), color: _colorForAction(item['action_type'] ?? ''), size: 18)),
      title: Text('${item['user_name'] ?? ''} ${_actionText(item['action_type'] ?? '')}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
      subtitle: Text(item['place_name'] ?? '', style: const TextStyle(fontSize: 11, color: AppColors.textGray)),
      trailing: Column(mainAxisAlignment: MainAxisAlignment.center, crossAxisAlignment: CrossAxisAlignment.end, children: [
        Text('+${item['points'] ?? 0}', style: const TextStyle(color: AppColors.gold, fontWeight: FontWeight.bold, fontSize: 12)),
        Text(item['time_ago'] ?? '', style: const TextStyle(fontSize: 9, color: AppColors.textLight)),
      ]),
      dense: true,
    ));
  }

  String _actionText(String t) {
    switch (t) {
      case 'add': return 'أضاف';
      case 'confirm': return 'أكد';
      case 'flag': return 'أبلغ عن';
      case 'vote': return 'قيّم';
      default: return 'تفاعل مع';
    }
  }

  IconData _iconForAction(String t) {
    switch (t) {
      case 'add': return Icons.add_location_outlined;
      case 'confirm': return Icons.check_circle_outlined;
      case 'flag': return Icons.flag_outlined;
      case 'vote': return Icons.star_outlined;
      default: return Icons.circle_outlined;
    }
  }

  Color _colorForAction(String t) {
    switch (t) {
      case 'add': return AppColors.blue;
      case 'confirm': return AppColors.success;
      case 'flag': return AppColors.error;
      case 'vote': return AppColors.gold;
      default: return AppColors.textGray;
    }
  }
}
