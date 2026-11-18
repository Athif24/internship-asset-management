import 'package:flutter/material.dart';

/// Placeholder untuk tim UI.
///
/// Contoh: `context.read<LoanController>().loadHistory();`
class LoanHistoryPage extends StatelessWidget {
  const LoanHistoryPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Riwayat Transaksi')),
      body: const Center(
        child: Text('TODO UI: daftar riwayat pinjam/kembalikan.'),
      ),
    );
  }
}
