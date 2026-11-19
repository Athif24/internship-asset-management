import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/assets/data/models/asset_model.dart';
import 'package:mobile/features/assets/domain/entities/asset_item.dart';
import 'package:mobile/features/assets/domain/entities/asset_status.dart';
import 'package:mobile/features/assets/presentation/detail/widgets/info_card.dart';

void main() {
  test('AssetModel membaca purchase_price dan purchase_year dari JSON', () {
    final model = AssetModel.fromJson(<String, dynamic>{
      'id': 1,
      'assets_code': 'AST-LPT-001',
      'name': 'Laptop Dell Latitude 5420',
      'category_id': 1,
      'status': 'AVAILABLE',
      'category': <String, dynamic>{'name': 'Laptop'},
      'purchase_price': '12500000.00',
      'purchase_year': 2022,
    });

    expect(model.assetsCode, 'AST-LPT-001');
    expect(model.purchasePrice, 12500000.0);
    expect(model.purchaseYear, 2022);
  });

  test('formatPurchasePrice memakai format Rupiah tanpa spasi', () {
    expect(formatPurchasePrice(12500000), 'Rp12.500.000');
    expect(formatPurchasePrice(6250000), 'Rp6.250.000');
    expect(formatPurchasePrice(null), '-');
  });

  test('copyWith mempertahankan harga dan tahun beli', () {
    const item = AssetItem(
      id: '1',
      name: 'Laptop',
      code: 'AST-001',
      category: 'Laptop',
      status: AssetStatus.available,
      purchasePrice: 12500000,
      purchaseYear: 2022,
    );

    final updated = item.copyWith(status: AssetStatus.borrowed);

    expect(updated.status, AssetStatus.borrowed);
    expect(updated.purchasePrice, 12500000);
    expect(updated.purchaseYear, 2022);
  });
}
