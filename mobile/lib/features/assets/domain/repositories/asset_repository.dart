import '../entities/asset.dart';
import '../entities/asset_category.dart';

abstract interface class AssetRepository {
  Future<List<Asset>> getAssets({
    String? status,
    int? categoryId,
    String? query,
  });

  Future<Asset> getAsset(int id);

  Future<List<AssetCategory>> getCategories();
}
