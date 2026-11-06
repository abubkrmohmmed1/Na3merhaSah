import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/network/api_client.dart';
import '../../../core/di.dart' as di;
import 'package:geolocator/geolocator.dart';

class TerritoryScreen extends StatefulWidget {
  final String? neighborhoodId;
  const TerritoryScreen({super.key, this.neighborhoodId});

  @override
  State<TerritoryScreen> createState() => _TerritoryScreenState();
}

class _TerritoryScreenState extends State<TerritoryScreen> {
  List<dynamic> _territories = [];
  Map<String, dynamic>? _selectedTerritory;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final api = di.sl<ApiClient>();
      double? lat;
      double? lng;
      
      try {
        LocationPermission permission = await Geolocator.checkPermission();
        if (permission == LocationPermission.always || permission == LocationPermission.whileInUse) {
          final pos = await Geolocator.getCurrentPosition(
            locationSettings: const LocationSettings(accuracy: LocationAccuracy.medium),
          );
          lat = pos.latitude;
          lng = pos.longitude;
        }
      } catch (_) {}

      final data = await api.getTerritoryHighlights(lat: lat, lng: lng);
      if (mounted) {
        setState(() {
          _territories = data['territories'] as List? ?? [];
          _loading = false;
        });
        if (widget.neighborhoodId != null) {
          _loadDetails(widget.neighborhoodId!);
        }
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _loadDetails(String id) async {
    try {
      final api = di.sl<ApiClient>();
      final data = await api.getTerritoryLeaderboard(id);
      if (mounted) setState(() => _selectedTerritory = data);
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('👑 حروب المناطق')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _selectedTerritory != null
              ? _buildDetailView()
              : _buildListView(),
    );
  }

  Widget _buildListView() {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(padding: const EdgeInsets.all(16), children: [
        const Text('الأحياء النشطة هذا الأسبوع', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textDark)),
        const SizedBox(height: 8),
        if (_territories.isEmpty)
          const Card(child: Padding(padding: EdgeInsets.all(32), child: Center(
            child: Column(children: [
              Text('👑', style: TextStyle(fontSize: 48)),
              SizedBox(height: 12),
              Text('لا توجد منافسة نشطة هذا الأسبوع', style: TextStyle(color: AppColors.textGray)),
            ]),
          )))
        else
          ..._territories.map((t) => Card(
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: AppColors.gold.withValues(alpha: 0.15),
                child: const Text('👑', style: TextStyle(fontSize: 18)),
              ),
              title: Text(t['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)),
              subtitle: t['ruler'] != null
                  ? Text('الحاكم: ${t['ruler']} ${t['ruler_level'] ?? ''} · ${t['total_actions']} نشاط', style: const TextStyle(fontSize: 12))
                  : Text('${t['total_actions']} نشاط · لا يوجد حاكم بعد', style: const TextStyle(fontSize: 12)),
              trailing: const Icon(Icons.chevron_left),
              onTap: () => _loadDetails(t['neighborhood_id']),
            ),
          )),
      ]),
    );
  }

  Widget _buildDetailView() {
    final hood = _selectedTerritory!;
    final leaderboard = hood['leaderboard'] as List? ?? [];

    return RefreshIndicator(
      onRefresh: () async => _loadDetails(widget.neighborhoodId ?? ''),
      child: ListView(padding: const EdgeInsets.all(16), children: [
        Card(
          child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
            const Text('👑', style: TextStyle(fontSize: 48)),
            const SizedBox(height: 8),
            Text(hood['neighborhood_name'] ?? '', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppColors.textDark)),
            const SizedBox(height: 4),
            if (hood['ruler'] != null)
              Text('🥇 الحاكم: ${hood['ruler']}', style: TextStyle(fontSize: 14, color: AppColors.gold, fontWeight: FontWeight.w600)),
            const SizedBox(height: 12),
            Text('${hood['total_contributors'] ?? 0} مساهم · ${hood['total_actions'] ?? 0} نشاط', style: TextStyle(color: AppColors.textGray)),
          ])),
        ),
        const SizedBox(height: 16),
        const Text('🏆 المتصدرين', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textDark)),
        const SizedBox(height: 8),
        if (leaderboard.isEmpty)
          const Card(child: Padding(padding: EdgeInsets.all(24), child: Center(child: Text('لا توجد نشاطات بعد', style: TextStyle(color: AppColors.textLight)))))
        else
          ...leaderboard.map((u) => Card(
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: u['rank'] == 1 ? AppColors.gold : u['rank'] == 2 ? Colors.grey[300] : u['rank'] == 3 ? Colors.brown[100] : AppColors.divider,
                child: Text(u['rank'] <= 3 ? ['🥇', '🥈', '🥉'][u['rank'] - 1] : '${u['rank']}',
                  style: TextStyle(fontSize: u['rank'] <= 3 ? 18 : 13, fontWeight: FontWeight.bold, color: AppColors.textGray)),
              ),
              title: Text(u['user_name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)),
              subtitle: Text('${u['actions']} تأكيد · ${u['points']} XP', style: const TextStyle(fontSize: 12)),
            ),
          )),
        const SizedBox(height: 16),
        ElevatedButton(
          onPressed: () => setState(() => _selectedTerritory = null),
          child: const Text('← كل المناطق'),
        ),
      ]),
    );
  }
}
