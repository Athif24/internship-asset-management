import 'package:flutter/material.dart';

import '../../features/assets/presentation/pages/asset_detail_page.dart';
import '../../features/assets/presentation/pages/asset_list_page.dart';
import '../../features/auth/presentation/pages/login_page.dart';
import '../../features/loans/presentation/pages/loan_history_page.dart';
import '../../features/profile/presentation/pages/profile_page.dart';

class AppRoutes {
  static const login = '/login';
  static const assets = '/assets';
  static const assetDetail = '/asset-detail';
  static const history = '/history';
  static const profile = '/profile';

  static final Map<String, WidgetBuilder> routes = {
    login: (_) => const LoginPage(),
    assets: (_) => const AssetListPage(),
    assetDetail: (context) {
      final args = ModalRoute.of(context)?.settings.arguments;
      final id = args is int ? args : 0;
      return AssetDetailPage(assetId: id);
    },
    history: (_) => const LoanHistoryPage(),
    profile: (_) => const ProfilePage(),
  };
}
