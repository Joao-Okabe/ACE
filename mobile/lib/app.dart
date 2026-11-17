import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import 'core/app_scope.dart';
import 'core/accessibility/accessibility_config.dart';
import 'core/accessibility/accessibility_controller.dart';
import 'core/accessibility/accessibility_scope.dart';
import 'core/session_controller.dart';
import 'core/theme.dart';
import 'core/update_checker.dart';
import 'screens/auth_screens.dart';
import 'screens/shell_screen.dart';

class AceApp extends StatelessWidget {
  const AceApp({required this.session, required this.accessibility, super.key});

  final SessionController session;
  final AccessibilityController accessibility;

  @override
  Widget build(BuildContext context) {
    return AccessibilityScope(
      controller: accessibility,
      child: AppScope(
        controller: session,
        child: ListenableBuilder(
          listenable: accessibility,
          builder: (context, _) => MaterialApp(
            title: 'ACE',
            debugShowCheckedModeBanner: false,
            theme: buildAceTheme(
              colorVisionMode: accessibility.colorVisionMode,
            ),
            darkTheme: buildAceTheme(
              colorVisionMode: accessibility.colorVisionMode,
              brightness: Brightness.dark,
            ),
            themeMode: accessibility.themeMode == AccessibilityThemeMode.dark
                ? ThemeMode.dark
                : ThemeMode.light,
            locale: const Locale('pt', 'BR'),
            supportedLocales: const [Locale('pt', 'BR')],
            localizationsDelegates: const [
              GlobalMaterialLocalizations.delegate,
              GlobalWidgetsLocalizations.delegate,
              GlobalCupertinoLocalizations.delegate,
            ],
            builder: (context, child) {
              final mediaQuery = MediaQuery.of(context);
              final systemScale = mediaQuery.textScaler.scale(1);
              final combinedScale =
                  (systemScale * accessibility.fontScale.factor)
                      .clamp(.8, 3.2)
                      .toDouble();
              return MediaQuery(
                data: mediaQuery.copyWith(
                  textScaler: TextScaler.linear(combinedScale),
                ),
                child: child ?? const SizedBox.shrink(),
              );
            },
            home: const UpdateGate(child: SessionGate()),
          ),
        ),
      ),
    );
  }
}

class SessionGate extends StatelessWidget {
  const SessionGate({super.key});

  @override
  Widget build(BuildContext context) {
    final session = AppScope.of(context);
    final accessibility = AccessibilityScope.of(context);
    if (!session.initialized || !accessibility.initialized) {
      return const _AceSplashScreen();
    }
    return AnimatedSwitcher(
      duration: AccessibilityConfig.animationDuration(context),
      child: session.isAuthenticated
          ? const MainShell(key: ValueKey('shell'))
          : const LoginScreen(key: ValueKey('login')),
    );
  }
}

class _AceSplashScreen extends StatelessWidget {
  const _AceSplashScreen();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AcePalette.of(context).navy,
      body: const SafeArea(
        child: Center(
          child: FractionallySizedBox(
            widthFactor: .88,
            child: Image(
              image: AssetImage('assets/images/ace_logo.png'),
              fit: BoxFit.contain,
            ),
          ),
        ),
      ),
    );
  }
}
