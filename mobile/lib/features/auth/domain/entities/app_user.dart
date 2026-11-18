class AppUser {
  const AppUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.division,
  });

  final int id;
  final String name;
  final String email;
  final String role;
  final String? division;

  bool get isEmployee => role == 'USER';
  bool get isAdmin => role == 'ADMIN';
}
