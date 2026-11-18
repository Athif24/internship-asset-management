import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/network/api_client.dart';
import '../core/network/api_endpoints.dart';
import '../core/storage/token_storage.dart';
import '../features/assets/data/datasources/asset_remote_data_source.dart';
import '../features/assets/data/repositories/asset_repository_impl.dart';
import '../features/assets/presentation/asset_controller.dart';
import '../features/auth/data/datasources/auth_remote_data_source.dart';
import '../features/auth/data/repositories/auth_repository_impl.dart';
import '../features/auth/presentation/auth_controller.dart';
import '../features/loans/data/datasources/loan_remote_data_source.dart';
import '../features/loans/data/repositories/loan_repository_impl.dart';
import '../features/loans/presentation/loan_controller.dart';
import 'config/api_config.dart';
import 'routes/app_routes.dart';
import 'theme/app_theme.dart';

/// Root widget. Menyuntikkan dependency sekali di sini sehingga
/// tim UI tinggal memanggil context.read<...Controller>() dari page mana pun.
class App extends StatelessWidget {
  const App({super.key, required this.apiConfig});

  final ApiConfig apiConfig;

  @override
  Widget build(BuildContext context) {
    final endpoints = ApiEndpoints(apiConfig.baseUrl);
    final apiClient = ApiClient(baseUrl: apiConfig.baseUrl);
    final tokenStorage = TokenStorage();

    final authRepository = AuthRepositoryImpl(
      AuthRemoteDataSource(apiClient, endpoints),
      tokenStorage,
    );
    final assetRepository = AssetRepositoryImpl(
      AssetRemoteDataSource(apiClient, endpoints),
    );
    final loanRepository = LoanRepositoryImpl(
      LoanRemoteDataSource(apiClient, endpoints),
    );

    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthController(authRepository)),
        ChangeNotifierProvider(create: (_) => AssetController(assetRepository)),
        ChangeNotifierProvider(create: (_) => LoanController(loanRepository)),
      ],
      child: MaterialApp(
        title: 'Company Asset Management',
        theme: AppTheme.light,
        initialRoute: AppRoutes.login,
        routes: AppRoutes.routes,
      ),
    );
  }
}
