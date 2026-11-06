import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../../core/storage/auth_storage.dart';
import '../../../core/di.dart' as di;
import '../../../core/theme/theme_provider.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Map<String, dynamic>? _data;
  List<dynamic> _achievements = [];
  List<dynamic> _quests = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    try {
      final api = di.sl<ApiClient>();
      final data = await api.getProfile();
      final achData = await api.getAchievements();
      final qData = await api.getQuests();
      if (mounted) setState(() {
        _data = data;
        _achievements = (achData['achievements'] as List?) ?? [];
        _quests = (qData['quests'] as List?) ?? [];
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _logout() async {
    await di.sl<ApiClient>().dio.post('/logout');
    await di.sl<AuthStorage>().logout();
    if (mounted) context.go('/login');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('حسابي'),
        actions: [
          IconButton(
            onPressed: () => context.push('/notifications'),
            icon: const Icon(Icons.notifications_none_rounded),
          ),
          IconButton(
            onPressed: () => themeProvider.toggle(),
            icon: Icon(themeProvider.mode == ThemeMode.dark ? Icons.light_mode_rounded : Icons.dark_mode_rounded),
          ),
          IconButton(onPressed: _logout, icon: const Icon(Icons.logout_rounded)),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadProfile,
              child: ListView(padding: const EdgeInsets.all(16), children: [
                if (_data != null) ...[
                  _buildUserCard(),
                  const SizedBox(height: 16),
                  _buildChallengeCard(),
      const SizedBox(height: 16),
      _buildStatsGrid(),
      const SizedBox(height: 16),
      _buildAchievements(),
      const SizedBox(height: 16),
      _buildSettingsCard(),
      const SizedBox(height: 16),
      _buildRecentActions(),
                ] else
                  _buildLoginPrompt(),
              ]),
            ),
    );
  }

  Widget _buildUserCard() {
    final user = _data!['user'] as Map? ?? {};
    final stats = _data!['stats'] as Map? ?? {};
    final level = stats['level'] ?? 1;
    final levelTitle = stats['level_title'] ?? 'مستكشف';
    final levelIcon = stats['level_icon'] ?? '🟢';
    final totalPoints = stats['total_points'] ?? 0;
    final nextPoints = stats['next_level_points'] ?? 200;
    final rank = _data!['rank'] ?? 0;
    final streak = stats['streak_days'] ?? 0;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(children: [
          Stack(children: [
            CircleAvatar(radius: 36, backgroundColor: AppColors.navy,
              child: Text((user['name'] as String? ?? '?')[0], style: const TextStyle(fontSize: 32, color: Colors.white, fontWeight: FontWeight.bold))),
            Positioned(bottom: 0, right: 0, child: Text(levelIcon, style: const TextStyle(fontSize: 18))),
          ]),
          const SizedBox(height: 10),
          Text(user['name'] ?? '', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textDark)),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            decoration: BoxDecoration(color: AppColors.blue.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20)),
            child: Text('مستوى $level • $levelTitle', style: TextStyle(fontSize: 12, color: AppColors.blue, fontWeight: FontWeight.w600)),
          ),
          const SizedBox(height: 12),
          // XP Bar
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Text('$totalPoints XP', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textDark)),
              Text('المستوى التالي: $nextPoints XP', style: const TextStyle(fontSize: 11, color: AppColors.textGray)),
            ]),
            const SizedBox(height: 4),
            ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: LinearProgressIndicator(
                value: (totalPoints as int) / (nextPoints as int),
                minHeight: 8,
                backgroundColor: AppColors.divider,
                valueColor: const AlwaysStoppedAnimation(AppColors.gold),
              ),
            ),
          ]),
          const SizedBox(height: 12),
          Row(mainAxisAlignment: MainAxisAlignment.spaceAround, children: [
            _miniStat('🏆', '#$rank', 'المتصدرين'),
            if (streak > 0) _miniStat('🔥', '$streak', 'أيام متتالية'),
          ]),
          const SizedBox(height: 12),
          // Trust score
          _buildTrustScore(),
          const SizedBox(height: 8),
          const SizedBox(height: 12),
                          SizedBox(
                            width: double.infinity,
                            child: OutlinedButton.icon(
                              onPressed: () => context.push('/leaderboard'),
                              icon: const Icon(Icons.emoji_events_outlined, color: AppColors.gold),
                              label: const Text('🏆 عرض المتصدرين'),
                              style: OutlinedButton.styleFrom(foregroundColor: AppColors.gold, side: const BorderSide(color: AppColors.gold)),
                            ),
                          ),
                          const SizedBox(height: 8),
                          SizedBox(
                            width: double.infinity,
                            child: OutlinedButton.icon(
                              onPressed: () => context.push('/territory'),
                              icon: const Icon(Icons.coronavirus_outlined, color: AppColors.navy),
                              label: const Text('👑 حروب المناطق'),
                              style: OutlinedButton.styleFrom(foregroundColor: AppColors.navy, side: const BorderSide(color: AppColors.navy)),
                            ),
                          ),
        ]),
      ),
    );
  }

  Widget _miniStat(String icon, String value, String label) {
    return Column(children: [
      Text(icon, style: const TextStyle(fontSize: 20)),
      Text(value, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textDark)),
      Text(label, style: const TextStyle(fontSize: 10, color: AppColors.textGray)),
    ]);
  }

  Widget _buildChallengeCard() {
    if (_quests.isEmpty) return const SizedBox.shrink();
    // Show first incomplete quest
    final q = _quests.firstWhere((q) => q['completed'] != true, orElse: () => _quests.first);
    final progress = ((q['progress'] ?? 0) as int).toDouble();
    final goal = ((q['goal_count'] ?? 1) as int).toDouble();

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(color: AppColors.gold.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(10)),
            child: Text(q['icon'] ?? '🏆', style: const TextStyle(fontSize: 24)),
          ),
          const SizedBox(width: 14),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(q['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.textDark)),
            const SizedBox(height: 6),
            ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: LinearProgressIndicator(
                value: goal > 0 ? (progress / goal).clamp(0, 1) : 0,
                minHeight: 6,
                backgroundColor: AppColors.divider,
                valueColor: AlwaysStoppedAnimation<Color>(q['completed'] == true ? AppColors.success : AppColors.gold),
              ),
            ),
            const SizedBox(height: 4),
            Text('${q['progress']}/${q['goal_count']} — +${q['xp_reward']} XP', style: TextStyle(fontSize: 11, color: AppColors.textGray)),
          ])),
        ]),
      ),
    );
  }

  Widget _buildTrustScore() {
    final trust = _data!['trust_score'] as Map? ?? {};
    final score = trust['score'] ?? 0;
    final level = trust['level'] ?? '🔴 جديد';
    final voteWeight = trust['vote_weight'] ?? 1;
    final factors = trust['factors'] as Map? ?? {};

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.blue.withValues(alpha: 0.05),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.blue.withValues(alpha: 0.1)),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          const Icon(Icons.shield_outlined, color: AppColors.blue, size: 18),
          const SizedBox(width: 6),
          Text('سمعة المستخدم', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.blue)),
          const Spacer(),
          Text('$level', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
        ]),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
            value: (score as int) / 100.0,
            minHeight: 6,
            backgroundColor: AppColors.divider,
            valueColor: const AlwaysStoppedAnimation(AppColors.blue),
          ),
        ),
        const SizedBox(height: 4),
        Row(children: [
          Text('$score/100', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.blue)),
          const Spacer(),
          Text('وزن التصويت: ×$voteWeight', style: TextStyle(fontSize: 10, color: AppColors.textGray)),
        ]),
      ]),
    );
  }

  Widget _buildStatsGrid() {
    final stats = _data!['stats'] as Map? ?? {};
    final items = [
      ('إضافات', stats['total_adds'] ?? 0, Icons.add_location_outlined, AppColors.blue),
      ('تأكيدات', stats['total_confirms'] ?? 0, Icons.check_circle_outline, AppColors.success),
      ('إبلاغات', stats['total_flags'] ?? 0, Icons.flag_outlined, AppColors.error),
      ('تقييمات', stats['total_votes'] ?? 0, Icons.star_outline, AppColors.gold),
    ];

    return Row(children: items.map((item) => Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 14),
          child: Column(children: [
            Icon(item.$3, color: item.$4, size: 22),
            const SizedBox(height: 6),
            Text('${item.$2}', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: item.$4)),
            Text(item.$1, style: const TextStyle(fontSize: 11, color: AppColors.textGray)),
          ]),
        ),
      ),
    )).toList());
  }

  Widget _buildAchievements() {
    if (_achievements.isEmpty) return const SizedBox.shrink();
    final earned = _achievements.where((a) => a['earned'] == true).toList();
    final unearned = _achievements.where((a) => a['earned'] != true).toList();

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('🏅 شاراتي (${earned.length}/${_achievements.length})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textDark)),
          const SizedBox(height: 12),
          Wrap(spacing: 10, runSpacing: 10,
            children: earned.map((a) => Tooltip(
              message: '${a['title']}\n${a['description']}',
              child: Container(
                width: 56, height: 56,
                decoration: BoxDecoration(
                  color: AppColors.gold.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Center(child: Text(a['icon'] ?? '🏅', style: const TextStyle(fontSize: 28))),
              ),
            )).toList() +
            unearned.take(4).map((a) => Tooltip(
              message: a['title'] ?? '',
              child: Container(
                width: 56, height: 56,
                decoration: BoxDecoration(
                  color: AppColors.divider,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Center(child: Text('🔒', style: const TextStyle(fontSize: 20))),
              ),
            )).toList(),
          ),
        ]),
      ),
    );
  }

  Widget _buildSettingsCard() {
    final isDark = themeProvider.mode == ThemeMode.dark;
    final streak = _data?['stats']?['streak_days'] ?? 0;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('الإعدادات', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textDark)),
          const SizedBox(height: 12),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Row(children: [
              Icon(isDark ? Icons.dark_mode : Icons.light_mode, color: AppColors.gold, size: 22),
              const SizedBox(width: 10),
              Text(isDark ? 'الوضع الليلي' : 'الوضع النهاري', style: const TextStyle(fontSize: 14)),
            ]),
            Switch(
              value: isDark,
              onChanged: (_) => themeProvider.toggle(),
              activeColor: AppColors.gold,
            ),
          ]),
        ]),
      ),
    );
  }

  Widget _buildRecentActions() {
    final actions = _data!['recent_actions'] as List? ?? [];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('آخر النشاطات', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textDark)),
        const SizedBox(height: 8),
        if (actions.isEmpty)
          const Card(child: Padding(padding: EdgeInsets.all(20), child: Center(child: Text('لا توجد نشاطات', style: TextStyle(color: AppColors.textLight))))),
        ...actions.take(10).map((a) => Card(
          child: ListTile(
            leading: Icon(_iconForAction(a['type'] ?? ''), color: _colorForAction(a['type'] ?? '')),
            title: Text(a['place_name'] ?? '', style: const TextStyle(fontSize: 14)),
            trailing: Text('+${a['points'] ?? 0}', style: const TextStyle(color: AppColors.gold, fontWeight: FontWeight.bold)),
            subtitle: Text(a['created_at'] ?? '', style: const TextStyle(fontSize: 11, color: AppColors.textGray)),
            dense: true,
          ),
        )),
      ],
    );
  }

  Widget _buildLoginPrompt() {
    return Center(
      child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        const Icon(Icons.person_outline, size: 64, color: AppColors.textLight),
        const SizedBox(height: 16),
        const Text('سجّل الدخول لترى ملفك', style: TextStyle(color: AppColors.textGray)),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: () => context.push('/login'), child: const Text('تسجيل دخول')),
      ]),
    );
  }

  IconData _iconForAction(String type) {
    switch (type) {
      case 'add': return Icons.add_location_outlined;
      case 'confirm': return Icons.check_circle_outlined;
      case 'flag': return Icons.flag_outlined;
      case 'vote': return Icons.star_outlined;
      default: return Icons.circle_outlined;
    }
  }

  Color _colorForAction(String type) {
    switch (type) {
      case 'add': return AppColors.blue;
      case 'confirm': return AppColors.success;
      case 'flag': return AppColors.error;
      case 'vote': return AppColors.gold;
      default: return AppColors.textGray;
    }
  }
}
