import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import '../../presentation/ui_state/auth_user.dart';

/// Cache lokal untuk data profil sesi login.
///
/// Token disimpan terpisah oleh TokenStorage. Cache ini hanya menyimpan
/// nama/email/divisi supaya aplikasi bisa langsung masuk ke halaman utama
/// tanpa menunggu `/api/me` saat dibuka ulang.
class AuthUserCache {
  static const _key = 'auth_user';

  Future<AuthUser?> read() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_key);
    if (raw == null || raw.isEmpty) {
      return null;
    }

    try {
      final map = jsonDecode(raw) as Map<String, dynamic>;
      return AuthUser(
        name: map['name'] as String? ?? '',
        email: map['email'] as String? ?? '',
        division: map['division'] as String? ?? '-',
      );
    } on FormatException {
      return null;
    }
  }

  Future<void> write(AuthUser user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(
      _key,
      jsonEncode(<String, dynamic>{
        'name': user.name,
        'email': user.email,
        'division': user.division,
      }),
    );
  }

  Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_key);
  }
}
