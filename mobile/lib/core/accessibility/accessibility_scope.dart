import 'package:flutter/widgets.dart';

import 'accessibility_controller.dart';

class AccessibilityScope extends InheritedNotifier<AccessibilityController> {
  const AccessibilityScope({
    required AccessibilityController controller,
    required super.child,
    super.key,
  }) : super(notifier: controller);

  static AccessibilityController of(BuildContext context) {
    final scope = context
        .dependOnInheritedWidgetOfExactType<AccessibilityScope>();
    assert(scope != null, 'AccessibilityScope não encontrado.');
    return scope!.notifier!;
  }

  static AccessibilityController read(BuildContext context) {
    final element = context
        .getElementForInheritedWidgetOfExactType<AccessibilityScope>();
    final scope = element?.widget as AccessibilityScope?;
    assert(scope != null, 'AccessibilityScope não encontrado.');
    return scope!.notifier!;
  }
}
