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
import '../features/auth/presentation/auth_controller.dart' as api_auth;
import '../features/auth/presentation/ui_state/auth_scope.dart';
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
  late final AssetController _assetController;
  late final LoanController _loanController;
  late final ApiAssetRepository _uiRepository;

  bool _dataLoaded = false;

  @override
  void initState() {
    super.initState();

    final endpoints = ApiEndpoints(widget.apiConfig.baseUrl);
    final apiClient = ApiClient(baseUrl: widget.apiConfig.baseUrl);
    final tokenStorage = TokenStorage();

    _authController = api_auth.AuthController(
      AuthRepositoryImpl(
        AuthRemoteDataSource(apiClient, endpoints),
        tokenStorage,
      ),
    );
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
    _authController.loadMe();
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
    _assetController.dispose();
    _loanController.dispose();
    _uiRepository.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider.value(value: _authController),
        ChangeNotifierProvider.value(value: _assetController),
        ChangeNotifierProvider.value(value: _loanController),
      ],
      child: AuthScope(
        child: AssetRepositoryScope(
          repository: _uiRepository,
          child: MaterialApp(
            title: 'Company Asset Management',
            debugShowCheckedModeBanner: false,
            theme: AppTheme.light,
            initialRoute: AppRoutes.login,
            routes: AppRoutes.routes,
          ),
        ),
      ),
    );
  }
}
