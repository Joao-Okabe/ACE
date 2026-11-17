import 'dart:convert';

import 'package:flutter/material.dart';

import '../core/app_scope.dart';
import '../core/data_helpers.dart';
import '../core/theme.dart';
import '../widgets/common.dart';
import 'competition_screens.dart';
import 'match_screens.dart';
import 'ranking_screens.dart';
import 'student_screens.dart';
import 'team_screens.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({this.onNavigate, super.key});

  final ValueChanged<int>? onNavigate;

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  bool _loading = true;
  Object? _error;
  Map<String, dynamic> _dashboard = {};
  Map<String, dynamic> _athlete = {};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final session = AppScope.read(context);
      _dashboard = asMap(await session.client.get('/dashboard'));
      if (session.profile == 'ALUNO' && session.studentId != null) {
        _athlete = asMap(
          await session.client.get('/alunos/${session.studentId}'),
        );
      }
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const LoadingState(message: 'Carregando o painel...');
    if (_error != null) return ErrorState(error: _error!, onRetry: _load);

    final session = AppScope.of(context);
    final summary = asMap(_dashboard['resumo']);
    final live = asList(_dashboard['partidasAoVivo']);
    final upcoming = asList(_dashboard['proximosJogos']);
    final ranking = asList(_dashboard['rankingEscolas']);
    final athletePhoto = _athletePhoto();
    final palette = AcePalette.of(context);

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        children: [
          Container(
            padding: const EdgeInsets.all(22),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [palette.navy, palette.blue],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(24),
            ),
            child: Row(
              children: [
                if (session.profile == 'ALUNO' && athletePhoto != null)
                  CircleAvatar(
                    radius: 36,
                    backgroundColor: palette.beige,
                    backgroundImage: athletePhoto,
                  )
                else
                  Container(
                    width: 64,
                    height: 64,
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const AceLogo(),
                  ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Bem-vindo,',
                        style: TextStyle(color: Colors.white70),
                      ),
                      Text(
                        session.userName,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _profileLabel(session.profile),
                        style: const TextStyle(color: Colors.white70),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            childAspectRatio: MediaQuery.textScalerOf(context).scale(1) > 1.3
                ? 1.05
                : 1.32,
            children: [
              MetricCard(
                label: 'Jogos hoje',
                value: intOf(summary, ['jogos_hoje', 'jogosHoje']),
                icon: Icons.event_available,
                onTap: () => widget.onNavigate?.call(1),
              ),
              MetricCard(
                label: 'Competições',
                value: intOf(summary, ['competicoes']),
                icon: Icons.emoji_events_outlined,
                onTap: () => pushPage(context, const CompetitionListScreen()),
              ),
              MetricCard(
                label: 'Times',
                value: intOf(summary, ['times']),
                icon: Icons.groups_2_outlined,
                onTap: () => widget.onNavigate?.call(2),
              ),
              MetricCard(
                label: 'Atletas',
                value: intOf(summary, ['atletas']),
                icon: Icons.directions_run,
                onTap:
                    session.hasAnyProfile({
                      'ADMINISTRADOR',
                      'ESCOLA',
                      'ORGANIZADOR',
                    })
                    ? () => pushPage(context, const StudentListScreen())
                    : null,
              ),
            ],
          ),
          const SizedBox(height: 24),
          SectionHeader(
            title: 'Ações rápidas',
            subtitle: 'Atalhos baseados no seu perfil',
            action: IconButton(
              tooltip: 'Atualizar painel',
              onPressed: _load,
              icon: const Icon(Icons.refresh),
            ),
          ),
          const SizedBox(height: 12),
          _QuickActions(profile: session.profile),
          const SizedBox(height: 24),
          SectionHeader(
            title: 'Partidas em andamento',
            subtitle: live.isEmpty
                ? 'Nenhuma partida ao vivo agora'
                : '${live.length} partida(s) ao vivo',
            action: TextButton(
              onPressed: () => widget.onNavigate?.call(1),
              child: const Text('Ver todas'),
            ),
          ),
          const SizedBox(height: 12),
          if (live.isEmpty)
            const SurfaceCard(
              child: EmptyState(
                title: 'Sem partidas ao vivo',
                message: 'Quando uma partida começar, o placar aparecerá aqui.',
                icon: Icons.sports_score_outlined,
              ),
            )
          else
            ...live
                .take(3)
                .map(
                  (match) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: MatchCard(
                      data: match,
                      onTap: () => pushPage(
                        context,
                        LiveMatchScreen(matchId: textOf(match, ['id'])),
                      ),
                    ),
                  ),
                ),
          const SizedBox(height: 12),
          SectionHeader(
            title: 'Próximos jogos',
            action: TextButton(
              onPressed: () => pushPage(context, const CalendarScreen()),
              child: const Text('Calendário'),
            ),
          ),
          const SizedBox(height: 12),
          if (upcoming.isEmpty)
            const SurfaceCard(
              child: EmptyState(
                title: 'Agenda vazia',
                message: 'Não há partidas futuras cadastradas.',
                icon: Icons.calendar_month_outlined,
              ),
            )
          else
            ...upcoming
                .take(4)
                .map(
                  (match) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: MatchCard(
                      data: match,
                      onTap: () => pushPage(
                        context,
                        LiveMatchScreen(matchId: textOf(match, ['id'])),
                      ),
                    ),
                  ),
                ),
          const SizedBox(height: 12),
          SectionHeader(
            title: 'Ranking das escolas',
            action: TextButton(
              onPressed: () => widget.onNavigate?.call(3),
              child: const Text('Ranking completo'),
            ),
          ),
          const SizedBox(height: 12),
          SurfaceCard(
            child: ranking.isEmpty
                ? const EmptyState(
                    title: 'Ranking ainda sem dados',
                    message:
                        'Os pontos aparecem após a publicação dos resultados.',
                    icon: Icons.leaderboard_outlined,
                  )
                : Column(
                    children: [
                      for (var i = 0; i < ranking.length; i++) ...[
                        _RankingPreviewRow(position: i + 1, data: ranking[i]),
                        if (i < ranking.length - 1) const Divider(height: 20),
                      ],
                    ],
                  ),
          ),
        ],
      ),
    );
  }

  String _profileLabel(String profile) => switch (profile) {
    'ADMINISTRADOR' => 'Administrador do sistema',
    'ESCOLA' => 'Representante da escola',
    'ALUNO' => 'Aluno / atleta',
    'ARBITRO' => 'Equipe de arbitragem',
    'ORGANIZADOR' => 'Organizador de competições',
    _ => profile,
  };

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

class _QuickActions extends StatelessWidget {
  const _QuickActions({required this.profile});
  final String profile;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final actions = <_QuickAction>[
      if (profile == 'ESCOLA' || profile == 'ADMINISTRADOR')
        _QuickAction(
          'Cadastrar atleta',
          Icons.person_add_alt_1,
          () => pushPage(context, const StudentFormScreen()),
        ),
      if (profile == 'ESCOLA' || profile == 'ADMINISTRADOR')
        _QuickAction(
          'Criar time',
          Icons.group_add_outlined,
          () => pushPage(context, const TeamFormScreen()),
        ),
      if (profile == 'ARBITRO' ||
          profile == 'ADMINISTRADOR' ||
          profile == 'ORGANIZADOR')
        _QuickAction(
          'Controlar partida',
          Icons.sports_score,
          () => pushPage(context, const LiveMatchesScreen()),
        ),
      _QuickAction(
        'Competições',
        Icons.emoji_events_outlined,
        () => pushPage(context, const CompetitionListScreen()),
      ),
      _QuickAction(
        'Calendário',
        Icons.calendar_month_outlined,
        () => pushPage(context, const CalendarScreen()),
      ),
      _QuickAction(
        'Rankings',
        Icons.leaderboard_outlined,
        () => pushPage(context, const RankingsScreen()),
      ),
    ];
    return SizedBox(
      height: 108,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: actions.length,
        separatorBuilder: (_, _) => const SizedBox(width: 10),
        itemBuilder: (context, index) {
          final action = actions[index];
          return Semantics(
            button: true,
            label: action.label,
            hint: 'Abrir ${action.label}',
            excludeSemantics: true,
            child: InkWell(
              onTap: action.onTap,
              borderRadius: BorderRadius.circular(18),
              child: Container(
                width: 118,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: scheme.surface,
                  borderRadius: BorderRadius.circular(18),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(action.icon, color: scheme.secondary),
                    const Spacer(),
                    Text(
                      action.label,
                      maxLines: 2,
                      style: const TextStyle(fontWeight: FontWeight.w700),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

class _QuickAction {
  const _QuickAction(this.label, this.icon, this.onTap);
  final String label;
  final IconData icon;
  final VoidCallback onTap;
}

class _RankingPreviewRow extends StatelessWidget {
  const _RankingPreviewRow({required this.position, required this.data});
  final int position;
  final Map<String, dynamic> data;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Row(
      children: [
        CircleAvatar(
          backgroundColor: position <= 3
              ? scheme.secondaryContainer
              : scheme.primaryContainer,
          foregroundColor: position <= 3
              ? scheme.onSecondaryContainer
              : scheme.onPrimaryContainer,
          child: Text(
            '$positionº',
            style: const TextStyle(fontWeight: FontWeight.w900),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            textOf(data, ['escola', 'nome']),
            style: const TextStyle(fontWeight: FontWeight.w700),
          ),
        ),
        Text(
          '${intOf(data, ['pontos'])} pts',
          style: TextStyle(fontWeight: FontWeight.w900, color: scheme.primary),
        ),
      ],
    );
  }
}
