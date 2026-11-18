import 'dart:async';

import '../../../../app/di/api_bridge.dart';
import '../../../../core/network/api_exception.dart';

/// Kontrak pengembalian aset.
abstract interface class AssetReturnService {
  Future<void> returnAsset(String assetCode);
}

/// Implementasi pengembalian yang memanggil REST API melalui [ApiBridge].
///
/// Nama kelas dipertahankan supaya widget UI tidak perlu diubah.
class StubAssetReturnService implements AssetReturnService {
  const StubAssetReturnService({this.simulateReturnError = false});

  final bool simulateReturnError;

  @override
  Future<void> returnAsset(String assetCode) async {
    if (simulateReturnError) {
      throw StateError('Simulasi gagal mengembalikan aset');
    }

    try {
      await ApiBridge.instance.returnAsset(assetCode);
    } on ApiException catch (error) {
      throw StateError(error.message);
    }
  }
}
