import 'package:flutter/material.dart';
import 'package:intl/date_symbol_data_local.dart';

import 'app.dart';
import 'core/accessibility/accessibility_controller.dart';
import 'core/session_controller.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final session = SessionController();
  final accessibility = AccessibilityController();
  runApp(AceApp(session: session, accessibility: accessibility));

  await Future.wait([
    initializeDateFormatting('pt_BR'),
    accessibility.initialize(),
    Future<void>.delayed(const Duration(milliseconds: 1200)),
  ]);
  await session.initialize();
}
