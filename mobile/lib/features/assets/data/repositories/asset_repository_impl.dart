import '../../domain/entities/asset.dart';
import '../../domain/entities/asset_category.dart';
import '../../domain/repositories/asset_repository.dart';
import '../datasources/asset_remote_data_source.dart';

class AssetRepositoryImpl implements AssetRepository {
  const AssetRepositoryImpl(this._remote);

  final AssetRemoteDataSource _remote;

  @override
  Future<List<Asset>> getAssets({
    String? status,
    int? categoryId,
    String? query,
  }) {
    return _remote.getAssets(
      status: status,
      categoryId: categoryId,
      query: query,
    );
  }

  @override
  Future<Asset> getAsset(int id) => _remote.getAsset(id);

  @override
  Future<List<AssetCategory>> getCategories() => _remote.getCategories();
}
