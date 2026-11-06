import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/theme/app_colors.dart';
import '../../core/di.dart';
import '../../core/network/api_client.dart';
import '../../features/map/screens/map_screen.dart';
import '../../features/auth/screens/login_screen.dart';
import '../../features/auth/screens/signup_screen.dart';
import '../../features/add_place/screens/add_place_screen.dart';
import '../../features/feed/screens/feed_screen.dart';
import '../../features/profile/screens/profile_screen.dart';
import '../../features/profile/screens/notifications_screen.dart';
import '../../features/leaderboard/screens/leaderboard_screen.dart';
import '../../features/leaderboard/screens/territory_screen.dart';
import '../../features/place_detail/screens/place_detail_screen.dart';
import '../../features/events/screens/events_screen.dart';
import '../../features/events/screens/event_leaderboard_screen.dart';
import '../../core/widgets/empty_screen.dart';

final GlobalKey<NavigatorState> _rootNavigator = GlobalKey<NavigatorState>();

final goRouter = GoRouter(
  navigatorKey: _rootNavigator,
  initialLocation: '/map',
  routes: [
    ShellRoute(
      builder: (context, state, child) => AppShell(child: child),
      routes: [
        GoRoute(
          path: '/map',
          pageBuilder: (context, state) {
             final eventId = state.uri.queryParameters['event_id'];
             return NoTransitionPage(
              child: MapScreen(eventId: eventId != null ? int.tryParse(eventId) : null),
            );
          },
        ),
        GoRoute(
          path: '/search',
          pageBuilder: (context, state) => const NoTransitionPage(child: MapScreen(isSearchMode: true)),
        ),
        GoRoute(
          path: '/add',
          pageBuilder: (context, state) {
            final layer = state.uri.queryParameters['layer'];
            final polygon = state.uri.queryParameters['polygon'] == 'true';
            final walking = state.uri.queryParameters['walking'] == 'true';
            return NoTransitionPage(
              child: AddPlaceScreen(
                preSelectedLayer: layer,
                startInPolygonMode: polygon,
                forceWalkingMode: walking,
              ),
            );
          },
        ),
        GoRoute(
          path: '/feed',
          pageBuilder: (context, state) => const NoTransitionPage(child: FeedScreen()),
        ),
        GoRoute(
          path: '/events',
          pageBuilder: (context, state) => const NoTransitionPage(child: EventsScreen()),
        ),
        GoRoute(
          path: '/profile',
          pageBuilder: (context, state) => const NoTransitionPage(child: ProfileScreen()),
        ),
      ],
    ),
    GoRoute(path: '/place/:id', builder: (context, state) => PlaceDetailScreen(placeId: state.pathParameters['id']!)),
    GoRoute(path: '/event/:id/leaderboard', builder: (context, state) => EventLeaderboardScreen(eventId: int.parse(state.pathParameters['id']!))),
    GoRoute(path: '/login', builder: (context, state) => const LoginScreen()),
    GoRoute(path: '/signup', builder: (context, state) => const SignupScreen()),
    GoRoute(path: '/leaderboard', builder: (context, state) => const LeaderboardScreen()),
    GoRoute(path: '/territory', builder: (context, state) => const TerritoryScreen()),
    GoRoute(path: '/notifications', builder: (context, state) => const NotificationsScreen()),
  ],
);

// AppShell logic remains same as before...
class AppShell extends StatefulWidget {
  final Widget child;
  const AppShell({super.key, required this.child});
  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> {
  // ... (keeping previous logic)
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: widget.child,
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex(context),
        onTap: (i) {
          switch (i) {
            case 0: context.go('/map'); break;
            case 1: context.go('/search'); break;
            case 2: context.go('/add'); break;
            case 3: context.go('/events'); break;
            case 4: context.go('/profile'); break;
          }
        },
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.map_outlined), label: 'خريطة'),
          BottomNavigationBarItem(icon: Icon(Icons.search_outlined), label: 'بحث'),
          BottomNavigationBarItem(icon: Icon(Icons.add_circle_outline, color: Color(0xFFEAB308)), label: 'إضافة'),
          BottomNavigationBarItem(icon: Icon(Icons.emoji_events_outlined), label: 'فعاليات'),
          BottomNavigationBarItem(icon: Icon(Icons.person_outline), label: 'حساب'),
        ],
      ),
    );
  }

  int _currentIndex(BuildContext context) {
    final location = GoRouterState.of(context).uri.toString();
    if (location.startsWith('/map')) return 0;
    if (location.startsWith('/search')) return 1;
    if (location.startsWith('/add')) return 2;
    if (location.startsWith('/events')) return 3;
    if (location.startsWith('/profile')) return 4;
    return 0;
  }
}
