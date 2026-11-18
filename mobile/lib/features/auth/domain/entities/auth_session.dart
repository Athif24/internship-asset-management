import 'app_user.dart';

class AuthSession {
  const AuthSession({
    required this.user,
    required this.token,
    required this.tokenType,
  });

  final AppUser user;
  final String token;
  final String tokenType;
}
