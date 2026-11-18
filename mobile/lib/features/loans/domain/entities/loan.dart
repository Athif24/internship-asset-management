class Loan {
  const Loan({
    required this.id,
    required this.assetId,
    required this.borrowerId,
    required this.borrowedAt,
    this.returnedAt,
    required this.status,
    this.assetName,
    this.assetsCode,
    this.borrowerName,
  });

  /// ID transaksi pinjaman. Dipakai untuk endpoint return.
  final int id;

  /// Di database backend kolomnya `assets_id`; request pinjam memakai `asset_id`.
  final int assetId;
  final int borrowerId;
  final DateTime borrowedAt;
  final DateTime? returnedAt;
  final String status;
  final String? assetName;
  final String? assetsCode;
  final String? borrowerName;

  bool get isActive => returnedAt == null;
}
