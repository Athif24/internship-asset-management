import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/features/auth/data/storage/auth_user_cache.dart';
import 'package:mobile/features/auth/presentation/ui_state/auth_controller.dart';
import 'package:mobile/features/auth/presentation/ui_state/auth_user.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues(<String, Object>{});
  });

  test('AuthUserCache menyimpan, membaca, dan menghapus user', () async {
    const user = AuthUser(
      name: 'Budi Santoso',
      email: 'budi@example.com',
      division: 'IT',
    );

    final cache = AuthUserCache();
    await cache.write(user);

    final restored = await cache.read();
    expect(restored, isNotNull);
    expect(restored!.name, user.name);
    expect(restored.email, user.email);
    expect(restored.division, user.division);

    await cache.clear();
    expect(await cache.read(), isNull);
  });

  test('AuthController.restoreSession menandai login tanpa form', () {
    final controller = AuthController();
    expect(controller.isLoggedIn, isFalse);

    const user = AuthUser(
      name: 'Budi Santoso',
      email: 'budi@example.com',
      division: 'IT',
    );
    controller.restoreSession(user);

    expect(controller.isLoggedIn, isTrue);
    expect(controller.user?.email, 'budi@example.com');

    controller.clearSession();
    expect(controller.isLoggedIn, isFalse);
    expect(controller.user, isNull);
  });
}
