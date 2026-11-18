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
  List<Map<String, dynamic>> _sports = [];
  List<Map<String, dynamic>> _schools = [];
  List<Map<String, dynamic>> _metrics = [];
  String? _competitionId;
  String? _modalityId;
  String? _sportId;
  String? _schoolId;
  String? _metricId;
  String _scope = 'geral';

  @override
  void initState() {
    super.initState();
    _loadFilters();
  }

  String get _path => switch (widget.type) {
    RankingType.schools => '/rankings/escolas',
    RankingType.teams => '/rankings/equipes',
    RankingType.individual => '/rankings/atletas',
  };

  Future<void> _loadFilters() async {
    try {
      final results = await Future.wait([
        AppScope.read(
          context,
        ).client.get('/competicoes', query: {'limite': 100}),
        AppScope.read(context).client.get('/modalidades'),
        AppScope.read(context).client.get('/esportes'),
        AppScope.read(context).client.get('/escolas'),
      ]);
      if (mounted) {
        setState(() {
          _competitions = asList(results[0]);
          _modalities = asList(results[1]);
          _sports = asList(results[2]);
          _schools = asList(results[3]);
          if (_sports.isNotEmpty)
            _sportId = textOf(_sports.first, ['cd_esporte', 'id']);
        });
      }
    } catch (_) {}
    await _loadMetrics();
    await _load();
  }

  Future<void> _loadMetrics() async {
    if (_sportId == null) return;
    try {
      final values = await AppScope.read(
        context,
      ).client.get('/esportes/$_sportId/metricas-atleta');
      if (mounted)
        setState(() {
          _metrics = asList(values);
          _metricId = _metrics.isEmpty
              ? null
              : textOf(_metrics.first, ['id', 'cd_metrica_atleta']);
        });
    } catch (_) {}
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      if (widget.type == RankingType.individual &&
          (_sportId == null ||
              _metricId == null ||
              (_scope == 'escola' && _schoolId == null) ||
              (_scope == 'competicao' && _competitionId == null))) {
        _items = [];
        return;
      }
      _items = asList(
        await AppScope.read(context).client.get(
          _path,
          query: {
            'competicaoId': _competitionId,
            'modalidadeId': _modalityId,
            'esporteId': _sportId,
            'escolaId': _scope == 'escola' ? _schoolId : null,
            'escopo': _scope,
            'metricaId': widget.type == RankingType.individual
                ? _metricId
                : null,
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
            initialValue: _sportId,
            decoration: const InputDecoration(
              labelText: 'Esporte',
              prefixIcon: Icon(Icons.sports),
            ),
            items: _sports
                .map(
                  (item) => DropdownMenuItem(
                    value: textOf(item, ['cd_esporte', 'id']),
                    child: Text(textOf(item, ['nm_esporte', 'nome'])),
                  ),
                )
                .toList(),
            onChanged: (value) async {
              setState(() {
                _sportId = value;
                _metricId = null;
              });
              await _loadMetrics();
              await _load();
            },
          ),
          if (widget.type == RankingType.individual) ...[
            const SizedBox(height: 10),
            DropdownButtonFormField<String>(
              initialValue: _metricId,
              decoration: const InputDecoration(
                labelText: 'Métrica',
                prefixIcon: Icon(Icons.query_stats),
              ),
              items: _metrics
                  .map(
                    (item) => DropdownMenuItem(
                      value: textOf(item, ['id', 'cd_metrica_atleta']),
                      child: Text(textOf(item, ['nome'])),
                    ),
                  )
                  .toList(),
              onChanged: (value) {
                setState(() => _metricId = value);
                _load();
              },
            ),
            const SizedBox(height: 10),
            DropdownButtonFormField<String>(
              initialValue: _scope,
              decoration: const InputDecoration(
                labelText: 'Escopo',
                prefixIcon: Icon(Icons.filter_alt_outlined),
              ),
              items: const [
                DropdownMenuItem(
                  value: 'geral',
                  child: Text('Geral no esporte'),
                ),
                DropdownMenuItem(value: 'esporte', child: Text('Esporte')),
                DropdownMenuItem(value: 'escola', child: Text('Escola')),
                DropdownMenuItem(
                  value: 'competicao',
                  child: Text('Competição'),
                ),
              ],
              onChanged: (value) {
                setState(() => _scope = value ?? 'geral');
                _load();
              },
            ),
            if (_scope == 'escola') ...[
              const SizedBox(height: 10),
              DropdownButtonFormField<String>(
                initialValue: _schoolId,
                decoration: const InputDecoration(
                  labelText: 'Escola',
                  prefixIcon: Icon(Icons.school_outlined),
                ),
                items: _schools
                    .map(
                      (item) => DropdownMenuItem(
                        value: textOf(item, ['cd_escola', 'id']),
                        child: Text(textOf(item, ['nm_escola', 'nome'])),
                      ),
                    )
                    .toList(),
                onChanged: (value) {
                  setState(() => _schoolId = value);
                  _load();
                },
              ),
            ],
          ],
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
                  value: textOf(item, ['id', 'cd_competicao']),
                  child: Text(textOf(item, ['nome', 'nm_competicao'])),
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
        '${textOf(data, ['time'])} • ${intOf(data, ['jogos'])} partidas',
    };
    final points = intOf(data, ['pontos', 'valor']);
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
                      '${textOf(data, ['metrica_nome', 'metrica'])}: ${doubleOf(data, ['valor']).toStringAsFixed(0)}',
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
                  type == RankingType.individual
                      ? textOf(data, ['metrica_nome'], fallback: 'métrica')
                      : 'pontos',
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
