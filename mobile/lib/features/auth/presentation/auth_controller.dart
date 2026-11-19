import 'package:flutter/foundation.dart';

import '../../../../core/network/api_exception.dart';
import '../domain/entities/app_user.dart';
import '../domain/entities/auth_session.dart';
import '../domain/repositories/auth_repository.dart';

class AuthController extends ChangeNotifier {
  AuthController(this._repository);

  final AuthRepository _repository;

  AppUser? _user;
  ApiException? _error;
  bool _loading = false;

  AppUser? get user => _user;
  ApiException? get error => _error;
  bool get loading => _loading;
  bool get isLoggedIn => _user != null;

  Future<AuthSession?> login({
    required String email,
    required String password,
  }) async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      final session = await _repository.login(
        email: email,
        password: password,
      );
      _user = session.user;
      return session;
    } on ApiException catch (e) {
      _error = e;
      return null;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<void> loadMe() async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      _user = await _repository.me();
    } on ApiException catch (e) {
      _error = e;
      if (e.isUnauthenticated) _user = null;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    try {
      await _repository.logout();
    } finally {
      _user = null;
      notifyListeners();
    }
  }
}
