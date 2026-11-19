import 'package:flutter/foundation.dart';

import '../../../../app/di/api_bridge.dart';
import '../../data/storage/auth_user_cache.dart';
import 'auth_service.dart';
import 'auth_user.dart';

/// Sesi login UI. Login memakai [AuthService] (kini REST API), dan logout
/// juga mencabut token Sanctum lewat [ApiBridge].
class AuthController extends ChangeNotifier {
  AuthController({AuthService? service, AuthUser? initialUser})
    : _service = service ?? const StubAuthService(),
      _user = initialUser;

  final AuthService _service;
  AuthUser? _user;

  AuthUser? get user => _user;
  bool get isLoggedIn => _user != null;

  Future<void> signIn({required String email, required String password}) async {
    final AuthUser user = await _service.signIn(
      email: email,
      password: password,
    );
    _user = user;
    notifyListeners();
  }

  /// Dipakai saat aplikasi dibuka ulang: mengembalikan sesi dari cache
  /// tanpa melewati form login.
  void restoreSession(AuthUser user) {
    _user = user;
    notifyListeners();
  }

  /// Hapus sesi UI tanpa memanggil backend (dipakai saat token terbukti 401).
  void clearSession() {
    _user = null;
    notifyListeners();
  }

  Future<void> signOut() async {
    if (_user == null) {
      return;
    }

    clearSession();
    await AuthUserCache().clear();

    try {
      await ApiBridge.instance.logout();
    } catch (error) {
      // TOKEN sudah dihapus di repository meski request logout gagal.
      if (kDebugMode) {
        debugPrint('[AUTH] logout error: $error');
      }
    }
  }
}
