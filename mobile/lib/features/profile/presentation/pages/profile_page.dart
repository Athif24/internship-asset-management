import 'package:flutter/material.dart';

/// Placeholder untuk tim UI.
///
/// Data profil tersedia lewat AuthController.user / AuthController.loadMe().
class ProfilePage extends StatelessWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: const Center(
        child: Text('TODO UI: profil employee + tombol logout.'),
      ),
    );
  }
}
