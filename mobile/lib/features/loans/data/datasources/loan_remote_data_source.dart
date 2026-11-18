import '../../../../core/network/api_client.dart';
import '../../../../core/network/api_endpoints.dart';
import '../models/loan_model.dart';

class LoanRemoteDataSource {
  const LoanRemoteDataSource(this._api, this._endpoints);

  final ApiClient _api;
  final ApiEndpoints _endpoints;

  Future<LoanModel> borrow(int assetId) async {
    final data = await _api.post(
      _endpoints.assetLoans,
      body: {'asset_id': assetId},
    ) as Map<String, dynamic>;

    return LoanModel.fromJson(data['loan'] as Map<String, dynamic>);
  }

  Future<LoanModel> returnLoan(int loanId) async {
    final data = await _api.post(_endpoints.returnLoan(loanId))
        as Map<String, dynamic>;

    return LoanModel.fromJson(data['loan'] as Map<String, dynamic>);
  }

  Future<List<LoanModel>> history({String? status, int? assetId}) async {
    final params = <String, String>{};
    if (status != null && status.isNotEmpty) params['status'] = status;
    if (assetId != null) params['asset_id'] = '$assetId';

    final data = await _api.get(
      _endpoints.loanHistory,
      query: params.isEmpty ? null : params,
    ) as Map<String, dynamic>;

    final items = data['loans'] as List<dynamic>? ?? const [];

    return items
        .map((item) => LoanModel.fromJson(item as Map<String, dynamic>))
        .toList();
  }
}
