/// Daftar endpoint sesuai routes/api.php di backend Laravel.
class ApiEndpoints {
  const ApiEndpoints(this.baseUrl);

  final String baseUrl;

  String get login => '$baseUrl/login';
  String get logout => '$baseUrl/logout';
  String get me => '$baseUrl/me';
  String get divisions => '$baseUrl/divisions';

  String get assetCategories => '$baseUrl/asset-categories';
  String get assets => '$baseUrl/assets';
  String asset(int id) => '$baseUrl/assets/$id';

  String get assetLoans => '$baseUrl/asset-loans';
  String get loanHistory => '$baseUrl/asset-loans/history';
  String returnLoan(int loanId) => '$baseUrl/asset-loans/$loanId/return';

  /// Endpoint ini hanya untuk role ADMIN di backend.
  String get dashboardAssets => '$baseUrl/dashboard/assets';
}
