import 'dart:convert';

import 'package:flutter/material.dart';

import '../core/app_scope.dart';
import '../core/data_helpers.dart';
import '../core/theme.dart';
import '../widgets/accessibility_panel.dart';
import '../widgets/common.dart';
import 'auth_screens.dart';
import 'competition_screens.dart';
import 'match_screens.dart';
import 'ranking_screens.dart';
import 'school_screens.dart';
import 'student_screens.dart';
import 'team_screens.dart';

class MenuScreen extends StatefulWidget {
  const MenuScreen({super.key});

  @override
  State<MenuScreen> createState() => _MenuScreenState();
}

class _MenuScreenState extends State<MenuScreen> {
  Map<String, dynamic> _athlete = {};

  @override
  void initState() {
    super.initState();
    _loadAthlete();
  }

  Future<void> _loadAthlete() async {
    final session = AppScope.read(context);
    if (session.profile != 'ALUNO' || session.studentId == null) return;
    try {
      final athlete = asMap(
        await session.client.get('/alunos/${session.studentId}'),
      );
      if (mounted) setState(() => _athlete = athlete);
    } catch (_) {
      // Mantém as iniciais como fallback se a foto não puder ser carregada.
    }
  }

  @override
  Widget build(BuildContext context) {
    final session = AppScope.of(context);
    final profile = session.profile;
    final athletePhoto = _athletePhoto();
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 30),
      children: [
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: AcePalette.of(context).navy,
            borderRadius: BorderRadius.circular(24),
          ),
          child: Row(
            children: [
              CircleAvatar(
                radius: 30,
                backgroundColor: Colors.white,
                backgroundImage: athletePhoto,
                child: athletePhoto == null
                    ? Text(
                        _initials(session.userName),
                        style: TextStyle(
                          color: AcePalette.of(context).navy,
                          fontWeight: FontWeight.w900,
                          fontSize: 18,
                        ),
                      )
                    : null,
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      session.userName,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 18,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      _profileLabel(profile),
                      style: const TextStyle(color: Colors.white70),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 22),
        const SectionHeader(title: 'Minha conta'),
        const SizedBox(height: 10),
        if (profile == 'ESCOLA')
          _MenuTile(
            icon: Icons.school_outlined,
            title: 'Perfil da escola',
            subtitle: 'Dados institucionais e endereço',
            onTap: () => pushPage(context, const SchoolProfileScreen()),
          ),
        if (profile == 'ALUNO' && session.studentId != null)
          _MenuTile(
            icon: Icons.person_outline,
            title: 'Meu perfil de atleta',
            subtitle: 'Dados, histórico e estatísticas',
            onTap: () => pushPage(
              context,
              StudentDetailScreen(studentId: session.studentId!),
            ),
          ),
        _MenuTile(
          icon: Icons.dns_outlined,
          title: 'Configuração da API',
          subtitle: session.client.baseUrl,
          onTap: () => pushPage(context, const ApiSettingsScreen()),
        ),
        const SizedBox(height: 20),
        const SectionHeader(title: 'Gestão esportiva'),
        const SizedBox(height: 10),
        if (session.hasAnyProfile({'ADMINISTRADOR', 'ESCOLA', 'ORGANIZADOR'}))
          _MenuTile(
            icon: Icons.directions_run,
            title: 'Alunos e atletas',
            subtitle: 'Cadastro, edição, histórico e estatísticas',
            onTap: () => pushPage(context, const StudentListScreen()),
          ),
        _MenuTile(
          icon: Icons.groups_2_outlined,
          title: 'Times',
          subtitle: 'Elencos, capitães, funções e escalações',
          onTap: () => pushPage(context, const TeamListScreen()),
        ),
        _MenuTile(
          icon: Icons.emoji_events_outlined,
          title: 'Competições',
          subtitle: 'Detalhes, inscrições, tabelas e classificação',
          onTap: () => pushPage(context, const CompetitionListScreen()),
        ),
        _MenuTile(
          icon: Icons.calendar_month_outlined,
          title: 'Calendário esportivo',
          subtitle: 'Agenda, horários e locais',
          onTap: () => pushPage(context, const CalendarScreen()),
        ),
        if (session.hasAnyProfile({'ADMINISTRADOR', 'ORGANIZADOR'}))
          _MenuTile(
            icon: Icons.location_city_outlined,
            title: 'Locais de competição',
            subtitle: 'Consultar e cadastrar locais',
            onTap: () => pushPage(context, const LocationsScreen()),
          ),
        if (session.hasAnyProfile({'ADMINISTRADOR', 'ORGANIZADOR', 'ARBITRO'}))
          _MenuTile(
            icon: Icons.sports_score_outlined,
            title: 'Controle de partidas',
            subtitle: 'Placar ao vivo, eventos e publicação',
            onTap: () => pushPage(context, const LiveMatchesScreen()),
          ),
        const SizedBox(height: 20),
        const SectionHeader(title: 'Consulta pública'),
        const SizedBox(height: 10),
        _MenuTile(
          icon: Icons.school_outlined,
          title: 'Escolas participantes',
          subtitle: 'Consultar escolas, equipes e atletas',
          onTap: () => pushPage(context, const SchoolListScreen()),
        ),
        _MenuTile(
          icon: Icons.leaderboard_outlined,
          title: 'Rankings',
          subtitle: 'Escolas, equipes e atletas',
          onTap: () => pushPage(context, const RankingsScreen()),
        ),
        _MenuTile(
          icon: Icons.history,
          title: 'Histórico de partidas',
          subtitle: 'Resultados e eventos já publicados',
          onTap: () => pushPage(context, const MatchHistoryScreen()),
        ),
        const SizedBox(height: 20),
        const SectionHeader(title: 'Aplicativo'),
        const SizedBox(height: 10),
        _MenuTile(
          icon: Icons.accessibility_new,
          title: 'Acessibilidade',
          subtitle: 'Fonte, tema e filtros para daltonismo',
          onTap: () => showAccessibilityPanel(context),
        ),
        _MenuTile(
          icon: Icons.info_outline,
          title: 'Sobre o ACE',
          subtitle: 'TCC — gestão esportiva escolar',
          onTap: () => pushPage(context, const AboutScreen()),
        ),
        _MenuTile(
          icon: Icons.logout,
          title: 'Sair da conta',
          subtitle: 'Encerrar a sessão neste dispositivo',
          danger: true,
          onTap: () => _logout(context),
        ),
      ],
    );
  }

  Future<void> _logout(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sair da conta?'),
        content: const Text(
          'Você precisará informar e-mail e senha para entrar novamente.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Sair'),
          ),
        ],
      ),
    );
    if (confirmed == true && context.mounted) {
      await AppScope.read(context).logout();
    }
  }

  String _profileLabel(String profile) => switch (profile) {
    'ADMINISTRADOR' => 'Administrador',
    'ESCOLA' => 'Representante da escola',
    'ALUNO' => 'Aluno / atleta',
    'ARBITRO' => 'Árbitro',
    'ORGANIZADOR' => 'Organizador',
    _ => profile,
  };

  String _initials(String name) {
    final parts = name
        .trim()
        .split(RegExp(r'\s+'))
        .where((part) => part.isNotEmpty)
        .toList();
    if (parts.isEmpty) return 'AE';
    if (parts.length == 1) {
      return (parts.first.length > 1
              ? parts.first.substring(0, 2)
              : parts.first)
          .toUpperCase();
    }
    return '${parts.first[0]}${parts.last[0]}'.toUpperCase();
  }

  MemoryImage? _athletePhoto() {
    final encoded = _athlete['foto_base64']?.toString();
    if (encoded == null || encoded.isEmpty) return null;
    try {
      return MemoryImage(base64Decode(encoded));
    } on FormatException {
      return null;
    }
  }
}

class _MenuTile extends StatelessWidget {
  const _MenuTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
    this.danger = false,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;
  final bool danger;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final color = danger ? scheme.error : scheme.primary;
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: SurfaceCard(
        onTap: onTap,
        child: Row(
          children: [
            Container(
              width: 46,
              height: 46,
              decoration: BoxDecoration(
                color: color.withValues(alpha: .10),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: color),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontWeight: FontWeight.w800,
                      color: danger ? color : null,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    subtitle,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: scheme.onSurfaceVariant,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
            Icon(
              Icons.chevron_right,
              color: danger ? color : scheme.onSurfaceVariant,
            ),
          ],
        ),
      ),
    );
  }
}

class AboutScreen extends StatelessWidget {
  const AboutScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Sobre o ACE'),
        actions: const [AccessibilityButton()],
      ),
      body: PageBody(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Column(
                children: [
                  Container(
                    width: 100,
                    height: 100,
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.surface,
                      borderRadius: BorderRadius.circular(28),
                    ),
                    child: const AceLogo(),
                  ),
                  const SizedBox(height: 14),
                  Text(
                    'ACE',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  Text(
                    'Sistema de Gestão Esportiva Escolar',
                    style: TextStyle(
                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 26),
            const SurfaceCard(
              child: Text(
                'Aplicativo mobile desenvolvido em Flutter e Dart para acompanhar e administrar competições escolares. Ele reúne autenticação, escolas, atletas, times, inscrições, calendário, partidas ao vivo, resultados e rankings em uma única experiência.',
                style: TextStyle(height: 1.5),
              ),
            ),
            const SizedBox(height: 14),
            const SurfaceCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Recursos implementados',
                    style: TextStyle(fontWeight: FontWeight.w900),
                  ),
                  SizedBox(height: 12),
                  _FeatureLine('Autenticação e recuperação de senha'),
                  _FeatureLine('Gestão de escola, alunos e times'),
                  _FeatureLine('Calendário, locais e inscrições'),
                  _FeatureLine('Partidas ao vivo, pontuações e penalidades'),
                  _FeatureLine('Resultados, classificações e rankings'),
                  _FeatureLine('Permissões e menus conforme o perfil'),
                ],
              ),
            ),
            const SizedBox(height: 14),
            Center(
              child: Text(
                'Projeto de TCC • ACE • 2026',
                style: TextStyle(
                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _FeatureLine extends StatelessWidget {
  const _FeatureLine(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 9),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            Icons.check_circle,
            color: AcePalette.of(context).success,
            size: 19,
          ),
          const SizedBox(width: 9),
          Expanded(child: Text(text)),
        ],
      ),
    );
  }
}
