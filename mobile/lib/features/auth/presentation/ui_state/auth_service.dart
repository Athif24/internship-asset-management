import '../../../../app/di/api_bridge.dart';
import 'auth_user.dart';

/// Error auth dengan pesan siap tampil di ErrorBanner.
class AuthException implements Exception {
  const AuthException(this.message);

  final String message;

  @override
  String toString() => 'AuthException: $message';
}

class AuthInvalidCredentialsException extends AuthException {
  const AuthInvalidCredentialsException() : super('Email atau password salah.');
}

class AuthNetworkException extends AuthException {
  const AuthNetworkException() : super('Koneksi gagal. Coba lagi.');
}

/// Kontrak login yang dipakai widget UI.
abstract interface class AuthService {
  Future<AuthUser> signIn({required String email, required String password});
}

/// Implementasi login REST API melalui [ApiBridge].
///
/// Nama kelas dan kredensial demo dipertahankan supaya form login
/// tidak perlu diubah — kini terisi akun employee backend.
class StubAuthService implements AuthService {
  const StubAuthService({this.simulateNetworkError = false});

  static const String demoEmail = 'budi@example.com';
  static const String demoPassword = 'Password123!';

  final bool simulateNetworkError;

  @override
  Future<AuthUser> signIn({
    required String email,
    required String password,
  }) async {
    if (simulateNetworkError) {
      throw const AuthNetworkException();
    }

    final controller = ApiBridge.instance.authController;
    if (controller == null) {
      throw const AuthNetworkException();
    }

    final session = await controller.login(
      email: email.trim(),
      password: password,
    );

    if (session == null) {
      final error = controller.error;
      if (error != null && error.isNetworkError) {
        throw const AuthNetworkException();
      }
      throw const AuthInvalidCredentialsException();
    }

    return AuthUser(
      name: session.user.name,
      email: session.user.email,
      division: session.user.division ?? '-',
    );
  }
}
