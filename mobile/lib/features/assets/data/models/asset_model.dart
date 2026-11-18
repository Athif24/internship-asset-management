import '../../domain/entities/asset.dart';

class AssetModel extends Asset {
  AssetModel({
    required super.id,
    required super.assetsCode,
    required super.name,
    required super.categoryId,
    required super.status,
    super.categoryName,
  });

  factory AssetModel.fromJson(Map<String, dynamic> json) {
    final category = json['category'];
    final categoryMap = category is Map<String, dynamic> ? category : null;

    return AssetModel(
      id: (json['id'] as num).toInt(),
      assetsCode: json['assets_code'] as String? ?? '',
      name: json['name'] as String? ?? '',
      categoryId: (json['category_id'] as num?)?.toInt() ?? 0,
      status: json['status'] as String? ?? 'AVAILABLE',
      categoryName: categoryMap?['name'] as String?,
    );
  }
}
