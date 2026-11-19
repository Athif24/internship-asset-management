import 'package:flutter/foundation.dart';

import '../../../../app/di/api_bridge.dart';
import '../../../../core/network/api_exception.dart';
import '../../data/storage/auth_user_cache.dart';
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

class AuthForbiddenException extends AuthException {
  const AuthForbiddenException()
    : super('Akun ADMIN tidak dapat login melalui mobile.');
}

class AuthValidationException extends AuthException {
  const AuthValidationException()
    : super('Input tidak valid. Periksa email dan password.');
}

class AuthThrottledException extends AuthException {
  const AuthThrottledException()
    : super('Terlalu banyak percobaan. Coba lagi nanti.');
}

class AuthServerException extends AuthException {
  const AuthServerException()
    : super('Terjadi kesalahan pada server. Coba lagi.');
}

class AuthInvalidResponseException extends AuthException {
  const AuthInvalidResponseException() : super('Respons server tidak valid.');
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

    // Debug saja: cukup panjang password untuk mendeteksi spasi tambahan,
    // tanpa pernah menampilkan isi password di log.
    if (kDebugMode) {
      debugPrint(
        '[AUTH] login email="${email.trim()}" '
        'passwordLength=${password.length} trimmedLength=${password.trim().length}',
      );
    }

    final controller = ApiBridge.instance.authController;
    if (controller == null) {
      throw const AuthNetworkException();
    }

    try {
      final session = await controller.login(
        email: email.trim(),
        password: password,
      );

      if (session == null) {
        throw _authExceptionFor(controller.error);
      }

      final user = AuthUser(
        name: session.user.name,
        email: session.user.email,
        division: session.user.division ?? '-',
      );
      await AuthUserCache().write(user);
      return user;
    } on AuthException {
      rethrow;
    } catch (error, stackTrace) {
      if (kDebugMode) {
        debugPrint('[AUTH] login error tak terduga: $error');
        debugPrint(stackTrace.toString());
      }
      throw const AuthException('Login gagal. Coba lagi.');
    }
  }
}

/// Terjemahkan [ApiException] menjadi pesan yang aman untuk UI.
///
/// Sebelumnya semua error non-jaringan dianggap salah kredensial, sehingga
/// 403/422/500/INVALID_RESPONSE tampil identik dengan 401.
AuthException _authExceptionFor(ApiException? error) {
  if (error == null) {
    return const AuthException('Login gagal. Coba lagi.');
  }

  final AuthException mapped = switch (error.code) {
    'NETWORK_ERROR' => const AuthNetworkException(),
    'INVALID_RESPONSE' => const AuthInvalidResponseException(),
    _ => switch (error.statusCode) {
      403 => const AuthForbiddenException(),
      422 => const AuthValidationException(),
      429 => const AuthThrottledException(),
      401 => AuthException(error.message),
      >= 500 => const AuthServerException(),
      _ => AuthException(
        error.message.isEmpty ? 'Login gagal. Coba lagi.' : error.message,
      ),
    },
  };

  if (!kDebugMode) {
    return mapped;
  }

  // Di debug, sertakan kode/status supaya error asli terbaca di console
  // dan di ErrorBanner tanpa mengubah widget UI.
  return AuthException('${mapped.message} [${error.code}/${error.statusCode}]');
}
