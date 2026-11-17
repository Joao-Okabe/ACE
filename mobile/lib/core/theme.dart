import 'package:flutter/material.dart';

import 'accessibility/accessibility_config.dart';

abstract final class AceColors {
  static const navy = Color(0xFF181847);
  static const navyDark = Color(0xFF101035);
  static const blue = Color(0xFF2C2C68);
  static const orange = Color(0xFFC9340D);
  static const beige = Color(0xFFF1E4D1);
  static const beigeStrong = Color(0xFFF1E4D1);
  static const canvas = Color(0xFFFAF7F2);
  static const success = Color(0xFF198754);
  static const warning = Color(0xFF795200);
  static const danger = Color(0xFFB3261E);
}

@immutable
class AcePalette extends ThemeExtension<AcePalette> {
  const AcePalette({
    required this.navy,
    required this.navyDark,
    required this.blue,
    required this.orange,
    required this.onOrange,
    required this.beige,
    required this.beigeStrong,
    required this.canvas,
    required this.surface,
    required this.onSurface,
    required this.muted,
    required this.success,
    required this.warning,
    required this.danger,
  });

  final Color navy;
  final Color navyDark;
  final Color blue;
  final Color orange;
  final Color onOrange;
  final Color beige;
  final Color beigeStrong;
  final Color canvas;
  final Color surface;
  final Color onSurface;
  final Color muted;
  final Color success;
  final Color warning;
  final Color danger;

  static AcePalette of(BuildContext context) {
    return Theme.of(context).extension<AcePalette>() ??
        paletteFor(ColorVisionMode.standard, Brightness.light);
  }

  @override
  AcePalette copyWith({
    Color? navy,
    Color? navyDark,
    Color? blue,
    Color? orange,
    Color? onOrange,
    Color? beige,
    Color? beigeStrong,
    Color? canvas,
    Color? surface,
    Color? onSurface,
    Color? muted,
    Color? success,
    Color? warning,
    Color? danger,
  }) {
    return AcePalette(
      navy: navy ?? this.navy,
      navyDark: navyDark ?? this.navyDark,
      blue: blue ?? this.blue,
      orange: orange ?? this.orange,
      onOrange: onOrange ?? this.onOrange,
      beige: beige ?? this.beige,
      beigeStrong: beigeStrong ?? this.beigeStrong,
      canvas: canvas ?? this.canvas,
      surface: surface ?? this.surface,
      onSurface: onSurface ?? this.onSurface,
      muted: muted ?? this.muted,
      success: success ?? this.success,
      warning: warning ?? this.warning,
      danger: danger ?? this.danger,
    );
  }

  @override
  AcePalette lerp(covariant AcePalette? other, double t) {
    if (other == null) return this;
    return AcePalette(
      navy: Color.lerp(navy, other.navy, t)!,
      navyDark: Color.lerp(navyDark, other.navyDark, t)!,
      blue: Color.lerp(blue, other.blue, t)!,
      orange: Color.lerp(orange, other.orange, t)!,
      onOrange: Color.lerp(onOrange, other.onOrange, t)!,
      beige: Color.lerp(beige, other.beige, t)!,
      beigeStrong: Color.lerp(beigeStrong, other.beigeStrong, t)!,
      canvas: Color.lerp(canvas, other.canvas, t)!,
      surface: Color.lerp(surface, other.surface, t)!,
      onSurface: Color.lerp(onSurface, other.onSurface, t)!,
      muted: Color.lerp(muted, other.muted, t)!,
      success: Color.lerp(success, other.success, t)!,
      warning: Color.lerp(warning, other.warning, t)!,
      danger: Color.lerp(danger, other.danger, t)!,
    );
  }
}

AcePalette paletteFor(ColorVisionMode mode, Brightness brightness) {
  final (navy, navyDark, blue, orange, onOrange) = switch (mode) {
    ColorVisionMode.standard => (
      const Color(0xFF181847),
      const Color(0xFF101035),
      const Color(0xFF2C2C68),
      const Color(0xFFC9340D),
      Colors.white,
    ),
    ColorVisionMode.protanopia => (
      const Color(0xFF1B3A5F),
      const Color(0xFF10253D),
      const Color(0xFF315E82),
      const Color(0xFFD98E04),
      const Color(0xFF10253D),
    ),
    ColorVisionMode.deuteranopia => (
      const Color(0xFF183B56),
      const Color(0xFF0E2A3F),
      const Color(0xFF2D607D),
      const Color(0xFFE69F00),
      const Color(0xFF102A3C),
    ),
    ColorVisionMode.tritanopia => (
      const Color(0xFF5A2A82),
      const Color(0xFF3C1C58),
      const Color(0xFF76499B),
      const Color(0xFFD55E00),
      const Color(0xFF25102D),
    ),
  };
  final dark = brightness == Brightness.dark;
  return AcePalette(
    navy: navy,
    navyDark: navyDark,
    blue: blue,
    orange: orange,
    onOrange: onOrange,
    beige: dark ? const Color(0xFF2B2B2B) : AceColors.beige,
    beigeStrong: dark ? const Color(0xFF343434) : AceColors.beigeStrong,
    canvas: dark ? const Color(0xFF121212) : AceColors.canvas,
    surface: dark ? const Color(0xFF1E1E1E) : Colors.white,
    onSurface: dark ? const Color(0xFFF5F5F5) : const Color(0xFF1D1B20),
    muted: dark ? const Color(0xFFD0D0D0) : const Color(0xFF535353),
    success: dark ? const Color(0xFF63D68A) : AceColors.success,
    warning: dark ? const Color(0xFFFFC857) : AceColors.warning,
    danger: dark ? const Color(0xFFFFB4AB) : AceColors.danger,
  );
}

ThemeData buildAceTheme({
  ColorVisionMode colorVisionMode = ColorVisionMode.standard,
  Brightness brightness = Brightness.light,
}) {
  final palette = paletteFor(colorVisionMode, brightness);
  final dark = brightness == Brightness.dark;
  final generated = ColorScheme.fromSeed(
    seedColor: palette.navy,
    brightness: brightness,
  );
  final scheme = generated.copyWith(
    primary: dark ? generated.primary : palette.navy,
    onPrimary: dark ? generated.onPrimary : Colors.white,
    secondary: palette.orange,
    onSecondary: palette.onOrange,
    tertiary: dark ? generated.tertiary : palette.blue,
    onTertiary: dark ? generated.onTertiary : Colors.white,
    primaryContainer: palette.beigeStrong,
    onPrimaryContainer: dark ? palette.onSurface : palette.navy,
    secondaryContainer: dark
        ? const Color(0xFF4A2B20)
        : palette.orange.withValues(alpha: .14),
    onSecondaryContainer: dark ? const Color(0xFFFFDBCF) : palette.navy,
    tertiaryContainer: palette.beige,
    onTertiaryContainer: dark ? palette.onSurface : palette.navy,
    surface: palette.surface,
    onSurface: palette.onSurface,
    onSurfaceVariant: palette.muted,
    error: palette.danger,
    onError: dark ? const Color(0xFF690005) : Colors.white,
    outline: dark ? const Color(0xFF8E8E8E) : const Color(0xFF747474),
    outlineVariant: dark ? const Color(0xFF464646) : const Color(0xFFD3D3D3),
  );
  final base = ThemeData(
    useMaterial3: true,
    brightness: brightness,
    colorScheme: scheme,
    fontFamily: 'Roboto',
    visualDensity: VisualDensity.standard,
    materialTapTargetSize: MaterialTapTargetSize.padded,
  );
  final textTheme = base.textTheme
      .apply(bodyColor: palette.onSurface, displayColor: palette.onSurface)
      .copyWith(
        bodySmall: TextStyle(fontSize: 13, color: palette.muted),
        labelSmall: TextStyle(fontSize: 12, color: palette.muted),
      );
  return base.copyWith(
    extensions: [palette],
    scaffoldBackgroundColor: palette.canvas,
    textTheme: textTheme,
    focusColor: palette.orange,
    dividerColor: scheme.outlineVariant,
    appBarTheme: AppBarTheme(
      centerTitle: false,
      elevation: 0,
      backgroundColor: palette.navy,
      foregroundColor: Colors.white,
      iconTheme: const IconThemeData(color: Colors.white),
      actionsIconTheme: const IconThemeData(color: Colors.white),
    ),
    cardTheme: CardThemeData(
      elevation: 0,
      color: palette.surface,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
    ),
    dialogTheme: DialogThemeData(backgroundColor: palette.surface),
    bottomSheetTheme: BottomSheetThemeData(
      backgroundColor: palette.surface,
      modalBackgroundColor: palette.surface,
      showDragHandle: true,
    ),
    listTileTheme: ListTileThemeData(
      textColor: palette.onSurface,
      iconColor: scheme.primary,
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: palette.surface,
      labelStyle: TextStyle(color: scheme.onSurfaceVariant),
      hintStyle: TextStyle(color: scheme.onSurfaceVariant),
      helperStyle: TextStyle(color: scheme.onSurfaceVariant),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: BorderSide(color: scheme.outlineVariant),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: BorderSide(color: palette.orange, width: 2),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: BorderSide(color: palette.danger, width: 1.5),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: BorderSide(color: palette.danger, width: 2),
      ),
      errorMaxLines: 3,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: palette.orange,
        foregroundColor: palette.onOrange,
        disabledBackgroundColor: scheme.onSurface.withValues(alpha: .12),
        disabledForegroundColor: scheme.onSurface.withValues(alpha: .45),
        minimumSize: const Size.fromHeight(52),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        textStyle: const TextStyle(fontWeight: FontWeight.w700),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: scheme.primary,
        side: BorderSide(color: scheme.primary),
        minimumSize: const Size.fromHeight(48),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      ),
    ),
    iconButtonTheme: IconButtonThemeData(
      style: IconButton.styleFrom(minimumSize: const Size(48, 48)),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: scheme.primary,
        minimumSize: const Size(48, 48),
      ),
    ),
    navigationBarTheme: NavigationBarThemeData(
      backgroundColor: palette.surface,
      indicatorColor: scheme.primaryContainer,
      iconTheme: WidgetStateProperty.resolveWith((states) {
        return IconThemeData(
          color: states.contains(WidgetState.selected)
              ? scheme.secondary
              : scheme.onSurface,
        );
      }),
      labelTextStyle: WidgetStateProperty.resolveWith((states) {
        return TextStyle(
          color: scheme.onSurface,
          fontSize: 12,
          fontWeight: states.contains(WidgetState.selected)
              ? FontWeight.w800
              : FontWeight.w600,
        );
      }),
    ),
    snackBarTheme: SnackBarThemeData(
      behavior: SnackBarBehavior.floating,
      backgroundColor: scheme.inverseSurface,
      contentTextStyle: TextStyle(color: scheme.onInverseSurface),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
    ),
    floatingActionButtonTheme: FloatingActionButtonThemeData(
      backgroundColor: palette.orange,
      foregroundColor: palette.onOrange,
    ),
    progressIndicatorTheme: ProgressIndicatorThemeData(color: palette.orange),
    textSelectionTheme: TextSelectionThemeData(
      cursorColor: palette.orange,
      selectionColor: palette.orange.withValues(alpha: .28),
      selectionHandleColor: palette.orange,
    ),
  );
}
