import 'package:shared_preferences/shared_preferences.dart';

/// Menyimpan token Sanctum secara lokal.
///
/// Untuk produksi sebaiknya diganti flutter_secure_storage.
class TokenStorage {
  static const _tokenKey = 'auth_token';

  Future<String?> readToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }
}
