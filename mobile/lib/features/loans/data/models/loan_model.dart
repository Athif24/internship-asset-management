import '../../domain/entities/loan.dart';

class LoanModel extends Loan {
  LoanModel({
    required super.id,
    required super.assetId,
    required super.borrowerId,
    required super.borrowedAt,
    super.returnedAt,
    required super.status,
    super.assetName,
    super.assetsCode,
    super.borrowerName,
  });

  factory LoanModel.fromJson(Map<String, dynamic> json) {
    final asset = json['asset'];
    final assetMap = asset is Map<String, dynamic> ? asset : null;
    final borrower = json['borrower'];
    final borrowerMap = borrower is Map<String, dynamic> ? borrower : null;

    return LoanModel(
      id: (json['id'] as num).toInt(),
      assetId: (json['assets_id'] as num).toInt(),
      borrowerId: (json['borrower_id'] as num).toInt(),
      borrowedAt: DateTime.parse(json['borrowed_at'] as String),
      returnedAt: json['returned_at'] == null
          ? null
          : DateTime.parse(json['returned_at'] as String),
      status: json['status'] as String? ?? 'BORROWED',
      assetName: assetMap?['name'] as String?,
      assetsCode: assetMap?['assets_code'] as String?,
      borrowerName: borrowerMap?['name'] as String?,
    );
  }
}
