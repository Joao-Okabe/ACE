import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'accessibility_config.dart';

class AccessibilityController extends ChangeNotifier {
  static const _fontScaleKey = 'accessibility_font_scale';
  static const _themeModeKey = 'accessibility_theme_mode';
  static const _colorVisionKey = 'accessibility_color_vision';

  SharedPreferences? _preferences;
  bool _initialized = false;
  AccessibilityFontScale _fontScale = AccessibilityFontScale.normal;
  AccessibilityThemeMode _themeMode = AccessibilityThemeMode.light;
  ColorVisionMode _colorVisionMode = ColorVisionMode.standard;

  bool get initialized => _initialized;
  AccessibilityFontScale get fontScale => _fontScale;
  AccessibilityThemeMode get themeMode => _themeMode;
  ColorVisionMode get colorVisionMode => _colorVisionMode;

  Future<void> initialize() async {
    if (_initialized) return;
    try {
      _preferences = await SharedPreferences.getInstance();
      _fontScale = _readEnum(
        _preferences?.getString(_fontScaleKey),
        AccessibilityFontScale.values,
        AccessibilityFontScale.normal,
      );
      _themeMode = _readEnum(
        _preferences?.getString(_themeModeKey),
        AccessibilityThemeMode.values,
        AccessibilityThemeMode.light,
      );
      _colorVisionMode = _readEnum(
        _preferences?.getString(_colorVisionKey),
        ColorVisionMode.values,
        ColorVisionMode.standard,
      );
    } catch (_) {
      // Mantém os padrões se o armazenamento local estiver indisponível.
    } finally {
      _initialized = true;
      notifyListeners();
    }
  }

  Future<void> setFontScale(AccessibilityFontScale value) async {
    if (_fontScale == value) return;
    _fontScale = value;
    notifyListeners();
    await _saveString(_fontScaleKey, value.name);
  }

  Future<void> setThemeMode(AccessibilityThemeMode value) async {
    if (_themeMode == value) return;
    _themeMode = value;
    notifyListeners();
    await _saveString(_themeModeKey, value.name);
  }

  Future<void> setColorVisionMode(ColorVisionMode value) async {
    if (_colorVisionMode == value) return;
    _colorVisionMode = value;
    notifyListeners();
    await _saveString(_colorVisionKey, value.name);
  }

  Future<void> reset() async {
    final changed =
        _fontScale != AccessibilityFontScale.normal ||
        _themeMode != AccessibilityThemeMode.light ||
        _colorVisionMode != ColorVisionMode.standard;
    _fontScale = AccessibilityFontScale.normal;
    _themeMode = AccessibilityThemeMode.light;
    _colorVisionMode = ColorVisionMode.standard;
    if (changed) notifyListeners();
    final preferences = _preferences ??= await SharedPreferences.getInstance();
    await Future.wait([
      preferences.setString(_fontScaleKey, _fontScale.name),
      preferences.setString(_themeModeKey, _themeMode.name),
      preferences.setString(_colorVisionKey, _colorVisionMode.name),
    ]);
  }

  Future<void> _saveString(String key, String value) async {
    final preferences = _preferences ??= await SharedPreferences.getInstance();
    await preferences.setString(key, value);
  }

  T _readEnum<T extends Enum>(String? stored, List<T> values, T fallback) {
    if (stored == null) return fallback;
    for (final value in values) {
      if (value.name == stored) return value;
    }
    return fallback;
  }
}
