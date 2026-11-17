import 'package:flutter/material.dart';

enum AccessibilityFontScale {
  small('Pequena', .9),
  normal('Padrão', 1),
  large('Grande', 1.15);

  const AccessibilityFontScale(this.label, this.factor);

  final String label;
  final double factor;
}

enum AccessibilityThemeMode {
  light('Claro', Icons.light_mode_outlined),
  dark('Escuro', Icons.dark_mode_outlined);

  const AccessibilityThemeMode(this.label, this.icon);

  final String label;
  final IconData icon;
}

enum ColorVisionMode {
  standard('Padrão'),
  protanopia('Protanopia'),
  deuteranopia('Deuteranopia'),
  tritanopia('Tritanopia');

  const ColorVisionMode(this.label);

  final String label;
}

/// Parâmetros de acessibilidade usados em todo o aplicativo ACE.
abstract final class AccessibilityConfig {
  static const double minimumTouchTarget = 48;
  static const double minimumReadableFontSize = 12;
  static const Duration standardAnimationDuration = Duration(milliseconds: 250);

  static Duration animationDuration(BuildContext context) {
    return MediaQuery.disableAnimationsOf(context)
        ? Duration.zero
        : standardAnimationDuration;
  }
}
