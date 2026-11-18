import 'dart:async';

import '../../../../app/di/api_bridge.dart';
import '../../../../core/network/api_exception.dart';

/// Exception untuk kondisi konflik (HTTP 409 pada implementasi nyata).
class AssetBorrowConflictException implements Exception {
  const AssetBorrowConflictException(this.assetCode);

  final String assetCode;

  @override
  String toString() => 'AssetBorrowConflictException: $assetCode';
}

/// Kontrak peminjaman aset.
abstract interface class AssetBorrowService {
  Future<void> borrow(String assetCode);
}

/// Implementasi peminjaman yang memanggil REST API melalui [ApiBridge].
///
/// Nama kelas dipertahankan supaya widget UI tidak perlu diubah.
class StubAssetBorrowService implements AssetBorrowService {
  const StubAssetBorrowService({
    this.simulateConflict = false,
    this.simulateGenericError = false,
  });

  final bool simulateConflict;
  final bool simulateGenericError;

  @override
  Future<void> borrow(String assetCode) async {
    if (simulateConflict) {
      throw AssetBorrowConflictException(assetCode);
    }
    if (simulateGenericError) {
      throw StateError('Simulasi error umum');
    }

    try {
      await ApiBridge.instance.borrow(assetCode);
    } on ApiException catch (error) {
      if (error.isConflict || error.isForbidden) {
        throw AssetBorrowConflictException(assetCode);
      }
      throw StateError(error.message);
    }
  }
}
