import '../../core/network/api_exception.dart';
import '../../features/assets/presentation/asset_controller.dart' as assets;
import '../../features/auth/presentation/auth_controller.dart' as auth;
import '../../features/loans/presentation/loan_controller.dart' as loans;

/// Jembatan antara UI (scope/stub milik tim UI) dan controller REST API.
///
/// Dipasang sekali dari [App] supaya widget UI tidak perlu diubah.
class ApiBridge {
  ApiBridge._();

  static final ApiBridge instance = ApiBridge._();

  auth.AuthController? authController;
  assets.AssetController? assetController;
  loans.LoanController? loanController;

  /// Muat ulang daftar aset dan riwayat dari backend.
  Future<void> reload() async {
    final assetCtrl = assetController;
    final loanCtrl = loanController;
    if (assetCtrl == null || loanCtrl == null) {
      return;
    }

    await Future.wait([
      assetCtrl.loadAssets(),
      loanCtrl.loadHistory(),
    ]);
  }

  /// UI memakai `assets_code` (String), API memakai `id` (int).
  int assetIdForCode(String code) {
    final controller = assetController;
    if (controller == null) {
      throw StateError('AssetController belum siap.');
    }

    return controller.assets
        .firstWhere(
          (asset) => asset.assetsCode == code,
          orElse: () => throw StateError('Aset $code tidak ditemukan.'),
        )
        .id;
  }

  /// Pinjam aset. Melempar [ApiException] bila backend menolak.
  Future<void> borrow(String code) async {
    final controller = loanController;
    if (controller == null) {
      throw StateError('LoanController belum siap.');
    }

    await controller.borrow(assetIdForCode(code));
    final error = controller.error;
    if (error != null) {
      throw error;
    }

    await reload();
  }

  /// Kembalikan pinjaman aktif. Endpoint butuh loan_id, bukan asset_id.
  Future<void> returnAsset(String code) async {
    final controller = loanController;
    if (controller == null) {
      throw StateError('LoanController belum siap.');
    }

    final loan = controller.loans.firstWhere(
      (item) => item.assetsCode == code && item.isActive,
      orElse: () => throw StateError(
        'Pinjaman aktif untuk aset $code tidak ditemukan.',
      ),
    );

    await controller.returnLoan(loan.id);
    final error = controller.error;
    if (error != null) {
      throw error;
    }

    await reload();
  }

  /// Cabut token Sanctum saat tombol Logout ditekan.
  Future<void> logout() async {
    await authController?.logout();
  }
}
