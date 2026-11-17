import 'package:flutter/widgets.dart';

import 'session_controller.dart';

class AppScope extends InheritedNotifier<SessionController> {
  const AppScope({
    required SessionController controller,
    required super.child,
    super.key,
  }) : super(notifier: controller);

  static SessionController of(BuildContext context) {
    final scope = context.dependOnInheritedWidgetOfExactType<AppScope>();
    assert(scope != null, 'AppScope não encontrado.');
    return scope!.notifier!;
  }

  static SessionController read(BuildContext context) {
    final element = context.getElementForInheritedWidgetOfExactType<AppScope>();
    final scope = element?.widget as AppScope?;
    assert(scope != null, 'AppScope não encontrado.');
    return scope!.notifier!;
  }
}
