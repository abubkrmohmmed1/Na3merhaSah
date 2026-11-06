import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

final ThemeProvider themeProvider = ThemeProvider();

class ThemeProvider extends ChangeNotifier {
  static const _key = 'theme_mode';
  ThemeMode _mode = ThemeMode.light;

  ThemeMode get mode => _mode;

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_key);
    if (saved == 'dark') {
      _mode = ThemeMode.dark;
    } else {
      _mode = ThemeMode.light;
    }
    notifyListeners();
  }

  Future<void> toggle() async {
    final prefs = await SharedPreferences.getInstance();
    if (_mode == ThemeMode.light) {
      _mode = ThemeMode.dark;
      await prefs.setString(_key, 'dark');
    } else {
      _mode = ThemeMode.light;
      await prefs.setString(_key, 'light');
    }
    notifyListeners();
  }

  Future<void> setDark(bool dark) async {
    final prefs = await SharedPreferences.getInstance();
    _mode = dark ? ThemeMode.dark : ThemeMode.light;
    await prefs.setString(_key, dark ? 'dark' : 'light');
    notifyListeners();
  }
}
