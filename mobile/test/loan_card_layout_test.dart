import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/assets/domain/entities/loan_item.dart';
import 'package:mobile/features/loans/presentation/utils/history_dates.dart';

void main() {
  const String kodeAset = 'AST-LPT-002';

  test('loanDateLine aset dipinjam diawali Sejak dan tanpa kode aset', () {
    final loan = LoanItem(
      assetCode: kodeAset,
      assetName: 'Laptop Lenovo ThinkPad T14',
      borrowDate: DateTime(2026, 11, 19, 10, 30),
    );

    final String line = loanDateLine(loan);

    expect(line, startsWith('Sejak '));
    expect(line, contains('19 Nov 2026, 10:30'));
    expect(line, isNot(contains('AST-')));
    expect(line, isNot(contains(kodeAset)));
  });

  test(
    'loanDateLine aset dikembalikan diawali Dikembalikan dan tanpa kode aset',
    () {
      final loan = LoanItem(
        assetCode: kodeAset,
        assetName: 'Laptop Lenovo ThinkPad T14',
        borrowDate: DateTime(2026, 11, 18, 8, 15),
        returnDate: DateTime(2026, 11, 19, 9, 0),
      );

      final String line = loanDateLine(loan);

      expect(line, startsWith('Dikembalikan '));
      expect(line, contains('19 Nov 2026, 09:00'));
      expect(line, isNot(contains('AST-')));
      expect(line, isNot(contains(kodeAset)));
    },
  );
}
