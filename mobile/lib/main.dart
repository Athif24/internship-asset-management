import 'package:flutter/material.dart';

import 'app/app.dart';
import 'app/config/api_config.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  runApp(App(apiConfig: ApiConfig.fromEnvironment()));
}
