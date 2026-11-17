import 'package:flutter/material.dart';

import '../core/app_scope.dart';
import '../widgets/accessibility_panel.dart';
import 'dashboard_screen.dart';
import 'match_screens.dart';
import 'menu_screen.dart';
import 'ranking_screens.dart';
import 'team_screens.dart';

class MainShell extends StatefulWidget {
  const MainShell({super.key});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _index = 0;

  late final List<Widget> _pages = [
    DashboardScreen(onNavigate: _select),
    const MatchHubScreen(),
    const TeamListScreen(embedded: true),
    const RankingsScreen(embedded: true),
    const MenuScreen(),
  ];

  void _select(int index) {
    if (index < 0 || index >= _pages.length) return;
    setState(() => _index = index);
  }

  @override
  Widget build(BuildContext context) {
    final session = AppScope.of(context);
    return Scaffold(
      appBar: AppBar(
        title: Tooltip(
          message: 'Voltar ao início',
          child: Semantics(
            button: true,
            label: 'ACE. Voltar ao início',
            child: InkWell(
              onTap: () => _select(0),
              borderRadius: BorderRadius.circular(8),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 2),
                child: Image.asset(
                  'assets/images/ace_wordmark_orange.png',
                  height: 50,
                  width: 126,
                  fit: BoxFit.contain,
                  errorBuilder: (_, _, _) => const Text('ACE!'),
                ),
              ),
            ),
          ),
        ),
        actions: [
          if (_index == 0)
            Padding(
              padding: const EdgeInsets.only(right: 12),
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: .12),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    _shortProfile(session.profile),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
      floatingActionButton: const AccessibilityButton(onSurface: true),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      body: IndexedStack(index: _index, children: _pages),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: _select,
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home),
            label: 'Início',
          ),
          NavigationDestination(
            icon: Icon(Icons.sports_soccer_outlined),
            selectedIcon: Icon(Icons.sports_soccer),
            label: 'Jogos',
          ),
          NavigationDestination(
            icon: Icon(Icons.groups_2_outlined),
            selectedIcon: Icon(Icons.groups_2),
            label: 'Times',
          ),
          NavigationDestination(
            icon: Icon(Icons.leaderboard_outlined),
            selectedIcon: Icon(Icons.leaderboard),
            label: 'Rankings',
          ),
          NavigationDestination(
            icon: Icon(Icons.menu),
            selectedIcon: Icon(Icons.menu_open),
            label: 'Mais',
          ),
        ],
      ),
    );
  }

  String _shortProfile(String profile) => switch (profile) {
    'ADMINISTRADOR' => 'ADMIN',
    'ORGANIZADOR' => 'ORGANIZADOR',
    'ESCOLA' => 'ESCOLA',
    'ALUNO' => 'ATLETA',
    'ARBITRO' => 'ÁRBITRO',
    _ => profile,
  };
}
