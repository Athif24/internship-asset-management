import '../../domain/entities/loan.dart';
import '../../domain/repositories/loan_repository.dart';
import '../datasources/loan_remote_data_source.dart';

class LoanRepositoryImpl implements LoanRepository {
  const LoanRepositoryImpl(this._remote);

  final LoanRemoteDataSource _remote;

  @override
  Future<Loan> borrow(int assetId) => _remote.borrow(assetId);

  @override
  Future<Loan> returnLoan(int loanId) => _remote.returnLoan(loanId);

  @override
  Future<List<Loan>> history({String? status, int? assetId}) {
    return _remote.history(status: status, assetId: assetId);
  }
}
