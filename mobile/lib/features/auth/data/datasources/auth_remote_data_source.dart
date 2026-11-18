import '../../../../core/network/api_client.dart';
import '../../../../core/network/api_endpoints.dart';
import '../../domain/entities/app_user.dart';
import '../models/auth_session_model.dart';
import '../models/login_request_model.dart';

class AuthRemoteDataSource {
  const AuthRemoteDataSource(this._api, this._endpoints);

  final ApiClient _api;
  final ApiEndpoints _endpoints;

  Future<AuthSessionModel> login({
    required String email,
    required String password,
  }) async {
    final request = LoginRequestModel(email: email, password: password);
    final data = await _api.post(
      _endpoints.login,
      body: request.toJson(),
      authenticated: false,
    );

    return AuthSessionModel.fromData(data as Map<String, dynamic>);
  }

  Future<AppUser> me() async {
    final data = await _api.get(_endpoints.me) as Map<String, dynamic>;

    return AppUser(
      id: data['id'] as int,
      name: data['name'] as String? ?? '',
      email: data['email'] as String? ?? '',
      role: data['role'] as String? ?? 'USER',
      division: data['division'] as String?,
    );
  }

  Future<void> logout() => _api.post(_endpoints.logout);
}
