import 'package:flutter/material.dart';

import '../core/app_scope.dart';
import '../core/data_helpers.dart';
import '../widgets/common.dart';
import 'student_screens.dart';
import 'team_screens.dart';

class RankingsScreen extends StatefulWidget {
  const RankingsScreen({this.embedded = false, super.key});

  final bool embedded;

  @override
  State<RankingsScreen> createState() => _RankingsScreenState();
}

class _RankingsScreenState extends State<RankingsScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabs;

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final body = Column(
      children: [
        Material(
          color: Theme.of(context).colorScheme.surface,
          child: TabBar(
            controller: _tabs,
            labelColor: Theme.of(context).colorScheme.primary,
            unselectedLabelColor: Theme.of(
              context,
            ).colorScheme.onSurfaceVariant,
            indicatorColor: Theme.of(context).colorScheme.primary,
            tabs: const [
              Tab(text: 'Escolas'),
              Tab(text: 'Equipes'),
              Tab(text: 'Atletas'),
            ],
          ),
        ),
        Expanded(
          child: TabBarView(
            controller: _tabs,
            children: const [
              RankingList(type: RankingType.schools),
              RankingList(type: RankingType.teams),
              RankingList(type: RankingType.individual),
            ],
          ),
        ),
      ],
    );
    if (widget.embedded) return body;
    return Scaffold(
      appBar: AppBar(title: const Text('Rankings')),
      body: body,
    );
  }
}

enum RankingType { schools, teams, individual }

class RankingList extends StatefulWidget {
  const RankingList({required this.type, super.key});
  final RankingType type;

  @override
  State<RankingList> createState() => _RankingListState();
}

class _RankingListState extends State<RankingList> {
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _items = [];
  List<Map<String, dynamic>> _competitions = [];
  List<Map<String, dynamic>> _modalities = [];
  String? _competitionId;
  String? _modalityId;

  @override
  void initState() {
    super.initState();
    _loadFilters();
    _load();
  }

  String get _path => switch (widget.type) {
    RankingType.schools => '/rankings/escolas',
    RankingType.teams => '/rankings/equipes',
    RankingType.individual => '/rankings/individual',
  };

  Future<void> _loadFilters() async {
    try {
      final results = await Future.wait([
        AppScope.read(
          context,
        ).client.get('/competicoes', query: {'limite': 100}),
        AppScope.read(context).client.get('/modalidades'),
      ]);
      if (mounted) {
        setState(() {
          _competitions = asList(results[0]);
          _modalities = asList(results[1]);
        });
      }
    } catch (_) {}
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      _items = asList(
        await AppScope.read(context).client.get(
          _path,
          query: {
            'competicaoId': _competitionId,
            'modalidadeId': _modalityId,
            'limite': 100,
          },
        ),
      );
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const LoadingState(message: 'Calculando ranking...');
    if (_error != null) return ErrorState(error: _error!, onRetry: _load);
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        children: [
          DropdownButtonFormField<String>(
            initialValue: _competitionId,
            decoration: const InputDecoration(
              labelText: 'Competição',
              prefixIcon: Icon(Icons.emoji_events_outlined),
            ),
            items: [
              const DropdownMenuItem(
                value: null,
                child: Text('Todas as competições'),
              ),
              ..._competitions.map(
                (item) => DropdownMenuItem(
                  value: textOf(item, ['id']),
                  child: Text(textOf(item, ['nome'])),
                ),
              ),
            ],
            onChanged: (value) {
              setState(() => _competitionId = value);
              _load();
            },
          ),
          const SizedBox(height: 10),
          DropdownButtonFormField<String>(
            initialValue: _modalityId,
            decoration: const InputDecoration(
              labelText: 'Modalidade',
              prefixIcon: Icon(Icons.sports),
            ),
            items: [
              const DropdownMenuItem(
                value: null,
                child: Text('Todas as modalidades'),
              ),
              ..._modalities.map(
                (item) => DropdownMenuItem(
                  value: textOf(item, ['id']),
                  child: Text(textOf(item, ['nome'])),
                ),
              ),
            ],
            onChanged: (value) {
              setState(() => _modalityId = value);
              _load();
            },
          ),
          const SizedBox(height: 16),
          if (_items.isEmpty)
            const EmptyState(
              title: 'Ranking ainda sem dados',
              message:
                  'Os resultados publicados alimentarão esta classificação.',
              icon: Icons.leaderboard_outlined,
            )
          else
            ...List.generate(
              _items.length,
              (index) => Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: _RankingCard(
                  type: widget.type,
                  position: index + 1,
                  data: _items[index],
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _RankingCard extends StatelessWidget {
  const _RankingCard({
    required this.type,
    required this.position,
    required this.data,
  });
  final RankingType type;
  final int position;
  final Map<String, dynamic> data;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final title = switch (type) {
      RankingType.schools => textOf(data, ['escola', 'nome']),
      RankingType.teams => textOf(data, ['time', 'time_nome']),
      RankingType.individual => textOf(data, ['atleta', 'nome']),
    };
    final subtitle = switch (type) {
      RankingType.schools =>
        '${intOf(data, ['jogos'])} jogos • ${intOf(data, ['vitorias'])} vitórias',
      RankingType.teams =>
        '${textOf(data, ['escola'])} • ${intOf(data, ['jogos'])} jogos',
      RankingType.individual =>
        '${textOf(data, ['escola'])} • ${intOf(data, ['partidas'])} partidas',
    };
    final points = intOf(data, ['pontos']);
    return Semantics(
      label: '$positionº lugar. $title. $points pontos. $subtitle.',
      button: type != RankingType.schools,
      excludeSemantics: true,
      child: SurfaceCard(
        onTap: () {
          if (type == RankingType.teams) {
            final id = textOf(data, ['time_id'], fallback: '');
            if (id.isNotEmpty) pushPage(context, TeamDetailScreen(teamId: id));
          }
          if (type == RankingType.individual) {
            final id = textOf(data, ['aluno_id'], fallback: '');
            if (id.isNotEmpty) {
              pushPage(context, StudentDetailScreen(studentId: id));
            }
          }
        },
        child: Row(
          children: [
            Container(
              width: 50,
              height: 50,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: position <= 3
                    ? scheme.secondaryContainer
                    : scheme.primaryContainer,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Text(
                '$positionº',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 17,
                  color: position <= 3
                      ? scheme.onSecondaryContainer
                      : scheme.onPrimaryContainer,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    subtitle,
                    style: TextStyle(
                      color: scheme.onSurfaceVariant,
                      fontSize: 12,
                    ),
                  ),
                  if (type == RankingType.individual) ...[
                    const SizedBox(height: 5),
                    Text(
                      '${intOf(data, ['assistencias'])} assistências • Nota ${doubleOf(data, ['media_nota']).toStringAsFixed(1)}',
                      style: TextStyle(color: scheme.primary, fontSize: 12),
                    ),
                  ],
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  '$points',
                  style: TextStyle(
                    fontWeight: FontWeight.w900,
                    fontSize: 22,
                    color: scheme.primary,
                  ),
                ),
                Text(
                  'pontos',
                  style: TextStyle(
                    fontSize: 12,
                    color: scheme.onSurfaceVariant,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
