import 'package:flutter/material.dart';

import '../../domain/entities/loan.dart';

/// Placeholder untuk tim UI.
class LoanTile extends StatelessWidget {
  const LoanTile({super.key, required this.loan, this.onReturn});

  final Loan loan;
  final VoidCallback? onReturn;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      title: Text(loan.assetName ?? 'Aset #${loan.assetId}'),
      subtitle: Text('${loan.status} • ${loan.borrowedAt.toLocal()}'),
      trailing: loan.isActive && onReturn != null
          ? IconButton(
              onPressed: onReturn,
              icon: const Icon(Icons.assignment_return),
            )
          : null,
    );
  }
}
