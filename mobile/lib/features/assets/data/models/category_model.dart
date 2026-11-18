import '../../domain/entities/asset_category.dart';

class CategoryModel extends AssetCategory {
  CategoryModel({
    required super.id,
    required super.name,
    required super.assetsCount,
    required super.availableCount,
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String? ?? '',
      assetsCount: (json['assets_count'] as num?)?.toInt() ?? 0,
      availableCount: (json['available_count'] as num?)?.toInt() ?? 0,
    );
  }
}
