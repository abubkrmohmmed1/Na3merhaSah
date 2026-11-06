import 'package:flutter/material.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../../core/di.dart' as di;

class EventLeaderboardScreen extends StatefulWidget {
  final int eventId;
  const EventLeaderboardScreen({super.key, required this.eventId});

  @override
  State<EventLeaderboardScreen> createState() => _EventLeaderboardScreenState();
}

class _EventLeaderboardScreenState extends State<EventLeaderboardScreen> {
  bool _loading = true;
  Map<String, dynamic>? _data;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      final api = di.sl<ApiClient>();
      final res = await api.dio.get('/mobile/events/${widget.eventId}/leaderboard');
      if (mounted) {
        setState(() {
          _data = res.data;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isResolved = _data?['status'] == 'resolved';
    final event = _data?['event'];
    final list = isResolved ? (_data?['winners'] as List?) : (_data?['leaderboard'] as List?);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(isResolved ? 'نتائج الفعالية 🏆' : 'المتصدرون حالياً ⚡'),
        centerTitle: true,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: CustomScrollView(
                slivers: [
                  if (event != null)
                    SliverToBoxAdapter(
                      child: Container(
                        padding: const EdgeInsets.all(20),
                        margin: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10)],
                        ),
                        child: Column(children: [
                          Text(event['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: AppColors.navy)),
                          const SizedBox(height: 8),
                          Text(
                            isResolved ? 'انتهت هذه الفعالية وتم توزيع المكافآت' : 'شارك الآن لتصعد في الترتيب وتكسب الجائزة!',
                            style: TextStyle(fontSize: 13, color: isResolved ? AppColors.success : AppColors.textGray),
                            textAlign: TextAlign.center,
                          ),
                          if (event['reward_points'] != null)
                            Padding(
                              padding: const EdgeInsets.only(top: 12),
                              child: Chip(
                                label: Text('الجائزة: ${event['reward_points']} XP', style: const TextStyle(fontWeight: FontWeight.bold)),
                                backgroundColor: AppColors.gold.withValues(alpha: 0.2),
                                side: BorderSide.none,
                              ),
                            ),
                        ]),
                      ),
                    ),
                  
                  if (list == null || list.isEmpty)
                    const SliverFillRemaining(child: Center(child: Text('لا توجد بيانات مشاركة حالياً')))
                  else
                    SliverList(
                      delegate: SliverChildBuilderDelegate(
                        (ctx, i) => _buildRankItem(list[i], i + 1, isResolved),
                        childCount: list.length,
                      ),
                    ),
                ],
              ),
            ),
    );
  }

  Widget _buildRankItem(dynamic item, int rank, bool isResolved) {
    final isMe = item['is_me'] == true;
    final value = item['value'] ?? item['metric_value'] ?? 0;
    final unit = item['unit'] ?? (item['reward_points'] != null ? 'XP' : '');
    final name = item['name'] ?? 'مستكشف مجهول';

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      decoration: BoxDecoration(
        color: isMe ? AppColors.navy.withValues(alpha: 0.05) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: isMe ? Border.all(color: AppColors.navy, width: 1.5) : null,
      ),
      child: ListTile(
        leading: _buildRankBadge(rank),
        title: Text(name, style: TextStyle(fontWeight: isMe ? FontWeight.bold : FontWeight.normal, color: AppColors.textDark)),
        subtitle: isResolved && item['reward_points'] != null 
            ? Text('المكافأة: ${item['reward_points']} XP', style: const TextStyle(color: AppColors.gold, fontWeight: FontWeight.bold, fontSize: 12))
            : null,
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text('$value', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.navy)),
            Text(unit, style: const TextStyle(fontSize: 10, color: AppColors.textGray)),
          ],
        ),
      ),
    );
  }

  Widget _buildRankBadge(int rank) {
    if (rank <= 3) {
      String icon = '🥇';
      if (rank == 2) icon = '🥈';
      if (rank == 3) icon = '🥉';
      return Text(icon, style: const TextStyle(fontSize: 24));
    }
    return Container(
      width: 30,
      height: 30,
      decoration: BoxDecoration(color: Colors.grey[200], shape: BoxShape.circle),
      child: Center(child: Text('#$rank', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textGray))),
    );
  }
}
