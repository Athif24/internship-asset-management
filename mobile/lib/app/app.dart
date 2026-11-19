import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/network/api_client.dart';
import '../core/network/api_endpoints.dart';
import '../core/storage/token_storage.dart';
import '../core/theme/app_theme.dart';
import '../features/assets/data/datasources/asset_remote_data_source.dart';
import '../features/assets/data/repositories/asset_repository_impl.dart';
import '../features/assets/presentation/asset_controller.dart';
import '../features/assets/presentation/ui_state/api_asset_repository.dart';
import '../features/assets/presentation/ui_state/asset_repository_scope.dart';
import '../features/auth/data/datasources/auth_remote_data_source.dart';
import '../features/auth/data/repositories/auth_repository_impl.dart';
import '../features/auth/data/storage/auth_user_cache.dart';
import '../features/auth/presentation/auth_controller.dart' as api_auth;
import '../features/auth/presentation/ui_state/auth_controller.dart' as ui_auth;
import '../features/auth/presentation/ui_state/auth_scope.dart';
import '../features/auth/presentation/ui_state/auth_user.dart';
import '../features/loans/data/datasources/loan_remote_data_source.dart';
import '../features/loans/data/repositories/loan_repository_impl.dart';
import '../features/loans/presentation/loan_controller.dart';
import 'config/api_config.dart';
import 'di/api_bridge.dart';
import 'routes/app_routes.dart';

/// Root widget. Menyuntikkan controller REST API sekaligus scope UI milik tim UI.
class App extends StatefulWidget {
  const App({super.key, required this.apiConfig});

  final ApiConfig apiConfig;

  @override
  State<App> createState() => _AppState();
}

class _AppState extends State<App> {
  late final api_auth.AuthController _authController;
  late final ui_auth.AuthController _uiAuthController;
  late final AssetController _assetController;
  late final LoanController _loanController;
  late final ApiAssetRepository _uiRepository;
  late final TokenStorage _tokenStorage;
  late final AuthUserCache _userCache;

  final GlobalKey<NavigatorState> _navigatorKey = GlobalKey<NavigatorState>();

  bool _authChecked = false;
  bool _startedWithSession = false;
  bool _dataLoaded = false;

  @override
  void initState() {
    super.initState();

    final endpoints = ApiEndpoints(widget.apiConfig.baseUrl);
    final apiClient = ApiClient(baseUrl: widget.apiConfig.baseUrl);
    _tokenStorage = TokenStorage();
    _userCache = AuthUserCache();

    _authController = api_auth.AuthController(
      AuthRepositoryImpl(
        AuthRemoteDataSource(apiClient, endpoints),
        _tokenStorage,
      ),
    );
    _uiAuthController = ui_auth.AuthController();
    _assetController = AssetController(
      AssetRepositoryImpl(AssetRemoteDataSource(apiClient, endpoints)),
    );
    _loanController = LoanController(
      LoanRepositoryImpl(LoanRemoteDataSource(apiClient, endpoints)),
    );

    final bridge = ApiBridge.instance;
    bridge.authController = _authController;
    bridge.assetController = _assetController;
    bridge.loanController = _loanController;

    _uiRepository = ApiAssetRepository();

    _authController.addListener(_onAuthChanged);
    _bootstrap();
  }

  /// Baca sesi lokal dulu supaya user tidak pernah melihat form login
  /// hanya karena aplikasi baru dibuka ulang.
  Future<void> _bootstrap() async {
    final cachedUser = await _userCache.read();
    _startedWithSession = cachedUser != null;

    if (cachedUser != null) {
      _uiAuthController.restoreSession(cachedUser);
      ApiBridge.instance.reload();
    }

    if (!mounted) {
      return;
    }
    setState(() => _authChecked = true);

    // Validasi token di background. Gagal jaringan tidak menggusur sesi cache.
    await _refreshSession();
  }

  Future<void> _refreshSession() async {
    await _authController.loadMe();
    if (!mounted) {
      return;
    }

    final user = _authController.user;
    if (user != null) {
      final uiUser = AuthUser(
        name: user.name,
        email: user.email,
        division: user.division ?? '-',
      );
      await _userCache.write(uiUser);
      _uiAuthController.restoreSession(uiUser);

      if (!_startedWithSession) {
        _navigatorKey.currentState?.pushReplacementNamed(AppRoutes.home);
      }
      return;
    }

    final error = _authController.error;
    if (error != null && error.isUnauthenticated) {
      await _tokenStorage.clear();
      await _userCache.clear();
      _uiAuthController.clearSession();

      if (_startedWithSession) {
        _navigatorKey.currentState?.pushReplacementNamed(AppRoutes.login);
      }
    }
  }

  void _onAuthChanged() {
    if (_authController.isLoggedIn && !_dataLoaded) {
      _dataLoaded = true;
      ApiBridge.instance.reload();
    } else if (!_authController.isLoggedIn) {
      _dataLoaded = false;
    }
  }

  @override
  void dispose() {
    _authController.removeListener(_onAuthChanged);
    _authController.dispose();
    _uiAuthController.dispose();
    _assetController.dispose();
    _loanController.dispose();
    _uiRepository.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (!_authChecked) {
      return MaterialApp(
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        home: const Scaffold(body: Center(child: CircularProgressIndicator())),
      );
    }

    return MultiProvider(
      providers: [
        ChangeNotifierProvider.value(value: _authController),
        ChangeNotifierProvider.value(value: _assetController),
        ChangeNotifierProvider.value(value: _loanController),
      ],
      child: AuthScope(
        controller: _uiAuthController,
        child: AssetRepositoryScope(
          repository: _uiRepository,
          child: MaterialApp(
            navigatorKey: _navigatorKey,
            title: 'Company Asset Management',
            debugShowCheckedModeBanner: false,
            theme: AppTheme.light,
            initialRoute: _uiAuthController.isLoggedIn
                ? AppRoutes.home
                : AppRoutes.login,
            routes: AppRoutes.routes,
          ),
        ),
      ),
    );
  }
}
