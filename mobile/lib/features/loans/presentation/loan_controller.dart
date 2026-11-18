import 'package:flutter/foundation.dart';

import '../../../../core/network/api_exception.dart';
import '../domain/entities/loan.dart';
import '../domain/repositories/loan_repository.dart';

class LoanController extends ChangeNotifier {
  LoanController(this._repository);

  final LoanRepository _repository;

  List<Loan> _loans = const [];
  Loan? _lastTransaction;
  ApiException? _error;
  bool _loading = false;

  List<Loan> get loans => _loans;
  Loan? get lastTransaction => _lastTransaction;
  ApiException? get error => _error;
  bool get loading => _loading;

  Future<Loan?> borrow(int assetId) => _run(() => _repository.borrow(assetId));

  Future<Loan?> returnLoan(int loanId) =>
      _run(() => _repository.returnLoan(loanId));

  Future<void> loadHistory({String? status, int? assetId}) async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      _loans = await _repository.history(
        status: status,
        assetId: assetId,
      );
    } on ApiException catch (e) {
      _error = e;
      _loans = const [];
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<Loan?> _run(Future<Loan> Function() action) async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      final loan = await action();
      _lastTransaction = loan;
      return loan;
    } on ApiException catch (e) {
      // AST-03/AST-04/AST-06/AST-07/AST-09 masuk ke sini.
      // Jangan pernah menganggap transaksi berhasil.
      _error = e;
      return null;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }
}
