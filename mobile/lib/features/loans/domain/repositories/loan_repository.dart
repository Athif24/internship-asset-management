import '../entities/loan.dart';

abstract interface class LoanRepository {
  Future<Loan> borrow(int assetId);

  /// [loanId] adalah ID pinjaman, bukan ID aset.
  Future<Loan> returnLoan(int loanId);

  Future<List<Loan>> history({String? status, int? assetId});
}
