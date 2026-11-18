import '../../../../core/network/api_client.dart';
import '../../../../core/network/api_endpoints.dart';
import '../models/asset_model.dart';
import '../models/category_model.dart';

class AssetRemoteDataSource {
  const AssetRemoteDataSource(this._api, this._endpoints);

  final ApiClient _api;
  final ApiEndpoints _endpoints;

  Future<List<AssetModel>> getAssets({
    String? status,
    int? categoryId,
    String? query,
  }) async {
    final params = <String, String>{};
    if (status != null && status.isNotEmpty) params['status'] = status;
    if (categoryId != null) params['category_id'] = '$categoryId';
    if (query != null && query.isNotEmpty) params['q'] = query;

    final data = await _api.get(
      _endpoints.assets,
      query: params.isEmpty ? null : params,
    ) as Map<String, dynamic>;

    final items = data['assets'] as List<dynamic>? ?? const [];

    return items
        .map((item) => AssetModel.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  Future<AssetModel> getAsset(int id) async {
    final data = await _api.get(_endpoints.asset(id)) as Map<String, dynamic>;
    return AssetModel.fromJson(data['asset'] as Map<String, dynamic>);
  }

  Future<List<CategoryModel>> getCategories() async {
    final data = await _api.get(_endpoints.assetCategories)
        as Map<String, dynamic>;
    final items = data['categories'] as List<dynamic>? ?? const [];

    return items
        .map((item) => CategoryModel.fromJson(item as Map<String, dynamic>))
        .toList();
  }
}
