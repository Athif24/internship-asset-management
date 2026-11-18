import '../../domain/entities/app_user.dart';
import '../../domain/entities/auth_session.dart';

class AuthSessionModel extends AuthSession {
  AuthSessionModel({
    required super.user,
    required super.token,
    required super.tokenType,
  });

  /// [data] adalah nilai `data` dari envelope response POST /api/login.
  factory AuthSessionModel.fromData(Map<String, dynamic> data) {
    final user = data['user'] as Map<String, dynamic>;

    return AuthSessionModel(
      user: AppUser(
        id: user['id'] as int,
        name: user['name'] as String? ?? '',
        email: user['email'] as String? ?? '',
        role: user['role'] as String? ?? 'USER',
        division: user['division'] as String?,
      ),
      token: data['token'] as String,
      tokenType: data['token_type'] as String? ?? 'Bearer',
    );
  }
}
