import 'asset_status.dart';

/// Representasi data satu aset (digunakan oleh data dummy maupun widget).
class AssetItem {
  const AssetItem({
    required this.id,
    required this.name,
    required this.code,
    required this.category,
    required this.status,
    this.purchasePrice,
    this.purchaseYear,
  });

  final String id;
  final String name;
  final String code;
  final String category;
  final AssetStatus status;
  final double? purchasePrice;
  final int? purchaseYear;

  /// Salinan dengan sebagian field diganti (dipakai saat refresh status).
  AssetItem copyWith({
    String? id,
    String? name,
    String? code,
    String? category,
    AssetStatus? status,
    double? purchasePrice,
    int? purchaseYear,
  }) {
    return AssetItem(
      id: id ?? this.id,
      name: name ?? this.name,
      code: code ?? this.code,
      category: category ?? this.category,
      status: status ?? this.status,
      purchasePrice: purchasePrice ?? this.purchasePrice,
      purchaseYear: purchaseYear ?? this.purchaseYear,
    );
  }
}
