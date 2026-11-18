import '../../../../core/storage/token_storage.dart';
import '../../domain/entities/app_user.dart';
import '../../domain/entities/auth_session.dart';
import '../../domain/repositories/auth_repository.dart';
import '../datasources/auth_remote_data_source.dart';

class AuthRepositoryImpl implements AuthRepository {
  const AuthRepositoryImpl(this._remote, this._tokenStorage);

  final AuthRemoteDataSource _remote;
  final TokenStorage _tokenStorage;

  @override
  Future<AuthSession> login({
    required String email,
    required String password,
  }) async {
    final session = await _remote.login(email: email, password: password);
    await _tokenStorage.saveToken(session.token);
    return session;
  }

  @override
  Future<AppUser> me() => _remote.me();

  @override
  Future<void> logout() async {
    try {
      await _remote.logout();
    } finally {
      await _tokenStorage.clear();
    }
  }
}
