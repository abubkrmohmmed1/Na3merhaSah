import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'core/theme/app_theme.dart';
import 'core/theme/theme_provider.dart';
import 'core/di.dart' as di;
import 'core/router/app_router.dart';
import 'features/auth/bloc/auth_bloc.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await di.initDependencies();
  await themeProvider.load();
  runApp(const NamerhaApp());
}

class NamerhaApp extends StatefulWidget {
  const NamerhaApp({super.key});

  @override
  State<NamerhaApp> createState() => _NamerhaAppState();
}

class _NamerhaAppState extends State<NamerhaApp> {
  @override
  void initState() {
    super.initState();
    themeProvider.addListener(_onThemeChange);
  }

  @override
  void dispose() {
    themeProvider.removeListener(_onThemeChange);
    super.dispose();
  }

  void _onThemeChange() => setState(() {});

  @override
  Widget build(BuildContext context) {
    GoogleFonts.config.allowRuntimeFetching = true;

    return BlocProvider(
      create: (_) => AuthBloc()..add(const CheckAuthEvent()),
      child: MaterialApp.router(
        title: 'نعمرها صح',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        darkTheme: AppTheme.dark,
        themeMode: themeProvider.mode,
        routerConfig: goRouter,
      ),
    );
  }
}
