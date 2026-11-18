import 'package:flutter/material.dart';

/// Placeholder untuk tim UI.
///
/// Gunakan [AuthController.login] lewat `context.read<AuthController>()`.
class LoginPage extends StatelessWidget {
  const LoginPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Login')),
      body: const Center(
        child: Text('TODO UI: form login (email, password) + error state.'),
      ),
    );
  }
}
