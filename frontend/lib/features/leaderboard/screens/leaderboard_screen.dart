import 'package:flutter/material.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../../core/di.dart' as di;

class LeaderboardScreen extends StatefulWidget {
  const LeaderboardScreen({super.key});

  @override
  State<LeaderboardScreen> createState() => _LeaderboardScreenState();
}

class _LeaderboardScreenState extends State<LeaderboardScreen> {
  String _period = 'week';
  List<dynamic>? _leaders;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final api = di.sl<ApiClient>();
      final data = await _getLeaderboard(api);
      if (mounted) setState(() { _leaders = data; _loading = false; });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<List<dynamic>> _getLeaderboard(ApiClient api) async {
    try {
      final dio = api.dio;
      final res = await dio.get('/mobile/leaderboard', queryParameters: {'period': _period});
      final data = res.data as Map<String, dynamic>;
      return (data['leaderboard'] as List?) ?? [];
    } catch (_) {
      return [];
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('🏆 لوحة الشرف')),
      body: Column(children: [
        // Period tabs
        Container(
          margin: const EdgeInsets.all(16),
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(color: AppColors.divider, borderRadius: BorderRadius.circular(10)),
          child: Row(children: [
            _periodTab('أسبوعي', 'week'),
            _periodTab('شهري', 'month'),
            _periodTab('كل الوقت', 'all'),
          ].toList()),
        ),
        Expanded(
          child: _loading
              ? const Center(child: CircularProgressIndicator())
              : _leaders == null || _leaders!.isEmpty
                  ? const Center(child: Text('لا توجد بيانات', style: TextStyle(color: AppColors.textGray)))
                  : ListView.builder(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      itemCount: _leaders!.length,
                      itemBuilder: (ctx, i) {
                        final item = _leaders![i];
                        final rank = item['rank'] ?? (i + 1);
                        final name = item['name'] ?? 'مستخدم';
                        final points = item['points'] ?? 0;
                        final levelIcon = item['level']?['icon'] ?? '🟢';
                        final levelTitle = item['level']?['title'] ?? 'مستكشف';
                        final isMe = item['is_me'] ?? false;

                        return Card(
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: rank == 1 ? AppColors.gold : rank == 2 ? Colors.grey[300] : rank == 3 ? Colors.brown[100] : AppColors.divider,
                              child: rank <= 3
                                  ? Text(['🥇', '🥈', '🥉'][rank - 1], style: const TextStyle(fontSize: 18))
                                  : Text('$rank', style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textGray, fontSize: 14)),
                            ),
                            title: Text(name, style: TextStyle(fontWeight: isMe ? FontWeight.bold : FontWeight.w600)),
                            subtitle: Text('$levelIcon $levelTitle', style: const TextStyle(fontSize: 12)),
                            trailing: Text('$points XP', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.gold, fontSize: 15)),
                            selected: isMe,
                            selectedTileColor: AppColors.blue.withValues(alpha: 0.05),
                          ),
                        );
                      },
                    ),
        ),
      ]),
    );
  }

  Widget _periodTab(String label, String value) {
    final active = _period == value;
    return Expanded(
      child: GestureDetector(
        onTap: () { setState(() => _period = value); _load(); },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: active ? Colors.white : Colors.transparent,
            borderRadius: BorderRadius.circular(8),
            boxShadow: active ? [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4)] : [],
          ),
          child: Text(label, textAlign: TextAlign.center,
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: active ? AppColors.navy : AppColors.textGray)),
        ),
      ),
    );
  }
}
