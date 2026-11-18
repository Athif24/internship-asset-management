import 'package:flutter/material.dart';

import '../../features/auth/presentation/pages/login_page.dart';

/// Navigasi utama dipegang oleh UI teman:
/// LoginPage --(login sukses)--> AppShell (3 tab via IndexedStack).
class AppRoutes {
  static const login = '/login';

  static final Map<String, WidgetBuilder> routes = {
    login: (_) => const LoginPage(),
  };
}
