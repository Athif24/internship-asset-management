import 'package:flutter/foundation.dart';

import '../../../../app/di/api_bridge.dart';
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

  void signOut() {
    if (_user == null) {
      return;
    }
    _user = null;
    notifyListeners();
    ApiBridge.instance.logout();
  }
}
