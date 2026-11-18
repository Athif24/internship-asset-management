class AssetCategory {
  const AssetCategory({
    required this.id,
    required this.name,
    required this.assetsCount,
    required this.availableCount,
  });

  final int id;
  final String name;
  final int assetsCount;
  final int availableCount;
}
