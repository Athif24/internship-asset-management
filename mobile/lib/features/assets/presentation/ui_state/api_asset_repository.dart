import '../../../../app/di/api_bridge.dart';
import '../../../loans/domain/entities/loan.dart' as api_loan;
import '../../domain/entities/asset.dart' as api_asset;
import '../../domain/entities/asset_item.dart';
import '../../domain/entities/asset_status.dart';
import '../../domain/entities/loan_item.dart';
import 'asset_repository.dart';

/// [AssetRepository] yang diisi dari controller REST API.
///
/// Widget UI tetap membaca `assets`, `loans`, dan `clock` seperti sebelumnya.
class ApiAssetRepository extends AssetRepository {
  ApiAssetRepository({super.clock})
    : super(assets: const [], loans: const []) {
    final bridge = ApiBridge.instance;
    bridge.assetController?.addListener(_sync);
    bridge.loanController?.addListener(_sync);
    refresh();
  }

  List<AssetItem> _assets = const <AssetItem>[];
  List<LoanItem> _loans = const <LoanItem>[];

  @override
  List<AssetItem> get assets => List<AssetItem>.unmodifiable(_assets);

  @override
  List<LoanItem> get loans => List<LoanItem>.unmodifiable(_loans);

  /// Samakan tampilan dengan data terakhir dari backend.
  void refresh() {
    final bridge = ApiBridge.instance;
    _assets =
        bridge.assetController?.assets.map(_toAssetItem).toList() ??
        const <AssetItem>[];
    _loans =
        bridge.loanController?.loans.map(_toLoanItem).toList() ??
        const <LoanItem>[];
    notifyListeners();
  }

  void _sync() => refresh();

  @override
  AssetItem? assetByCode(String code) {
    for (final item in _assets) {
      if (item.code == code) {
        return item;
      }
    }
    return null;
  }

  @override
  void borrowAsset(String code, {DateTime? now}) {
    // Peminjaman sudah diproses StubAssetBorrowService lewat ApiBridge.
    refresh();
  }

  @override
  void returnAsset(String code, {DateTime? now}) {
    // Pengembalian sudah diproses StubAssetReturnService lewat ApiBridge.
    refresh();
  }

  static AssetItem _toAssetItem(api_asset.Asset asset) {
    return AssetItem(
      id: asset.id.toString(),
      name: asset.name,
      code: asset.assetsCode,
      category: asset.categoryName ?? 'Lainnya',
      status: _status(asset.status),
    );
  }

  static LoanItem _toLoanItem(api_loan.Loan loan) {
    return LoanItem(
      assetCode: loan.assetsCode ?? '',
      assetName: loan.assetName ?? 'Aset #${loan.assetId}',
      borrowDate: loan.borrowedAt,
      returnDate: loan.returnedAt,
    );
  }

  static AssetStatus _status(String value) {
    return switch (value) {
      'AVAILABLE' => AssetStatus.available,
      'BORROWED' => AssetStatus.borrowed,
      'MAINTENANCE' => AssetStatus.maintenance,
      _ => AssetStatus.available,
    };
  }
}
