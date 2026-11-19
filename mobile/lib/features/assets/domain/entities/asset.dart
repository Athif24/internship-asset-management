class Asset {
  const Asset({
    required this.id,
    required this.assetsCode,
    required this.name,
    required this.categoryId,
    required this.status,
    this.categoryName,
    this.purchasePrice,
    this.purchaseYear,
  });

  final int id;

  /// Nama field mengikuti database backend: assets_code (bukan asset_code).
  final String assetsCode;
  final String name;
  final int categoryId;
  final String status;
  final String? categoryName;
  final double? purchasePrice;
  final int? purchaseYear;

  bool get isAvailable => status == 'AVAILABLE';
  bool get isBorrowed => status == 'BORROWED';
  bool get isMaintenance => status == 'MAINTENANCE';
}
