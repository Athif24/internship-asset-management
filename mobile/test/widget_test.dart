import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/network/api_exception.dart';

void main() {
  test('ApiException menyimpan kode aturan bisnis', () {
    final exception = ApiException(
      statusCode: 409,
      code: 'AST-03',
      message: 'Aset sedang dipinjam.',
    );

    expect(exception.statusCode, 409);
    expect(exception.code, 'AST-03');
    expect(exception.message, 'Aset sedang dipinjam.');
  });
}
