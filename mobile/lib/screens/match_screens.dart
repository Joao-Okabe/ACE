import 'dart:async';

import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../core/app_scope.dart';
import '../core/data_helpers.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

class MatchHubScreen extends StatefulWidget {
  const MatchHubScreen({super.key});

  @override
  State<MatchHubScreen> createState() => _MatchHubScreenState();
}

class _MatchHubScreenState extends State<MatchHubScreen>
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
    return Column(
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
              Tab(text: 'Ao vivo'),
              Tab(text: 'Calendário'),
              Tab(text: 'Histórico'),
            ],
          ),
        ),
        Expanded(
          child: TabBarView(
            controller: _tabs,
            children: const [
              LiveMatchesScreen(embedded: true),
              MatchCalendarList(embedded: true),
              MatchHistoryScreen(embedded: true),
            ],
          ),
        ),
      ],
    );
  }
}

class LiveMatchesScreen extends StatefulWidget {
  const LiveMatchesScreen({this.embedded = false, super.key});
  final bool embedded;

  @override
  State<LiveMatchesScreen> createState() => _LiveMatchesScreenState();
}

class _LiveMatchesScreenState extends State<LiveMatchesScreen> {
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _items = [];
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _load();
    _timer = Timer.periodic(
      const Duration(seconds: 5),
      (_) => _load(silent: true),
    );
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) {
      setState(() {
        _loading = true;
        _error = null;
      });
    }
    try {
      final result = asList(
        await AppScope.read(context).client.get('/partidas/ao-vivo'),
      );
      if (mounted) setState(() => _items = result);
    } catch (error) {
      if (!silent) _error = error;
    } finally {
      if (!silent && mounted) setState(() => _loading = false);
    }
  }

  Widget _body() {
    if (_loading) {
      return const LoadingState(message: 'Buscando partidas ao vivo...');
    }
    if (_error != null) return ErrorState(error: _error!, onRetry: _load);
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          SurfaceCard(
            color: AcePalette.of(context).navy,
            child: Row(
              children: [
                const _LivePulse(),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Atualização automática',
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      Text(
                        'A cada 5 segundos • ${_items.length} partida(s)',
                        style: const TextStyle(color: Colors.white70),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  tooltip: 'Atualizar partidas ao vivo',
                  onPressed: _load,
                  icon: const Icon(Icons.refresh, color: Colors.white),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          if (_items.isEmpty)
            const EmptyState(
              title: 'Nenhuma partida ao vivo',
              message: 'As partidas iniciadas aparecerão aqui automaticamente.',
              icon: Icons.sports_score_outlined,
            )
          else
            ..._items.map(
              (match) => Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: MatchCard(
                  data: match,
                  onTap: () async {
                    await pushPage(
                      context,
                      LiveMatchScreen(matchId: textOf(match, ['id'])),
                    );
                    _load();
                  },
                ),
              ),
            ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final body = _body();
    if (widget.embedded) return body;
    return Scaffold(
      appBar: AppBar(title: const Text('Partidas ao vivo')),
      body: body,
    );
  }
}

class _LivePulse extends StatefulWidget {
  const _LivePulse();

  @override
  State<_LivePulse> createState() => _LivePulseState();
}

class _LivePulseState extends State<_LivePulse>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    );
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (MediaQuery.disableAnimationsOf(context)) {
      _controller
        ..stop()
        ..value = 1;
    } else if (!_controller.isAnimating) {
      _controller.repeat(reverse: true);
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final dot = Container(
      width: 14,
      height: 14,
      decoration: const BoxDecoration(
        color: Colors.redAccent,
        shape: BoxShape.circle,
      ),
    );
    if (MediaQuery.disableAnimationsOf(context)) return dot;
    return FadeTransition(
      opacity: Tween(begin: .45, end: 1.0).animate(_controller),
      child: dot,
    );
  }
}

class MatchCalendarList extends StatefulWidget {
  const MatchCalendarList({this.embedded = false, super.key});
  final bool embedded;

  @override
  State<MatchCalendarList> createState() => _MatchCalendarListState();
}

class _MatchCalendarListState extends State<MatchCalendarList> {
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _items = [];

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
      _items = asList(
        await AppScope.read(
          context,
        ).client.get('/calendario', query: {'limite': 100}),
      );
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Widget _body() {
    if (_loading) return const LoadingState();
    if (_error != null) return ErrorState(error: _error!, onRetry: _load);
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (_items.isEmpty)
            const EmptyState(
              title: 'Calendário vazio',
              message: 'Não há partidas agendadas.',
              icon: Icons.calendar_month_outlined,
            )
          else
            ..._items.map(
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
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final body = _body();
    if (widget.embedded) return body;
    return Scaffold(
      appBar: AppBar(title: const Text('Calendário')),
      body: body,
    );
  }
}

class MatchHistoryScreen extends StatefulWidget {
  const MatchHistoryScreen({this.embedded = false, super.key});
  final bool embedded;

  @override
  State<MatchHistoryScreen> createState() => _MatchHistoryScreenState();
}

class _MatchHistoryScreenState extends State<MatchHistoryScreen> {
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _items = [];

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
      _items = asList(
        await AppScope.read(
          context,
        ).client.get('/partidas/historico', query: {'limite': 100}),
      );
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Widget _body() {
    if (_loading) return const LoadingState(message: 'Carregando histórico...');
    if (_error != null) return ErrorState(error: _error!, onRetry: _load);
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (_items.isEmpty)
            const EmptyState(
              title: 'Sem partidas encerradas',
              message: 'O histórico será preenchido após os jogos.',
              icon: Icons.history,
            )
          else
            ..._items.map(
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
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final body = _body();
    if (widget.embedded) return body;
    return Scaffold(
      appBar: AppBar(title: const Text('Histórico de partidas')),
      body: body,
    );
  }
}

class LiveMatchScreen extends StatefulWidget {
  const LiveMatchScreen({required this.matchId, super.key});
  final String matchId;

  @override
  State<LiveMatchScreen> createState() => _LiveMatchScreenState();
}

class _LiveMatchScreenState extends State<LiveMatchScreen> {
  bool _loading = true;
  Object? _error;
  Map<String, dynamic> _match = {};
  Timer? _timer;
  bool _updating = false;

  bool get _canOperate => AppScope.read(
    context,
  ).hasAnyProfile({'ADMINISTRADOR', 'ORGANIZADOR', 'ARBITRO'});
  bool get _canAssignReferee =>
      AppScope.read(context).hasAnyProfile({'ADMINISTRADOR', 'ORGANIZADOR'});

  @override
  void initState() {
    super.initState();
    _load();
    _timer = Timer.periodic(
      const Duration(seconds: 3),
      (_) => _load(silent: true),
    );
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) {
      setState(() {
        _loading = true;
        _error = null;
      });
    }
    try {
      final result = asMap(
        await AppScope.read(
          context,
        ).client.get('/partidas/${widget.matchId}/ao-vivo'),
      );
      if (mounted) setState(() => _match = result);
    } catch (error) {
      if (!silent) _error = error;
    } finally {
      if (!silent && mounted) setState(() => _loading = false);
    }
  }

  Future<void> _runAction(
    Future<dynamic> Function() action,
    String success,
  ) async {
    if (_updating) return;
    setState(() => _updating = true);
    try {
      await action();
      if (mounted) showSuccess(context, success);
      await _load(silent: true);
    } catch (error) {
      if (mounted) showFailure(context, error);
    } finally {
      if (mounted) setState(() => _updating = false);
    }
  }

  Future<void> _start() => _runAction(
    () => AppScope.read(
      context,
    ).client.patch('/partidas/${widget.matchId}/iniciar'),
    'Partida iniciada.',
  );

  Future<void> _publish() => _runAction(
    () => AppScope.read(
      context,
    ).client.post('/partidas/${widget.matchId}/publicar'),
    'Resultado publicado.',
  );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Partida'),
        actions: [
          IconButton(
            tooltip: 'Atualizar partida',
            onPressed: _load,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: _loading
          ? const LoadingState(message: 'Carregando partida...')
          : _error != null
          ? ErrorState(error: _error!, onRetry: _load)
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
                children: [
                  _Scoreboard(match: _match),
                  const SizedBox(height: 14),
                  SurfaceCard(
                    child: Column(
                      children: [
                        KeyValueRow(
                          label: 'Competição',
                          value: textOf(_match, ['competicao']),
                          icon: Icons.emoji_events_outlined,
                        ),
                        KeyValueRow(
                          label: 'Fase',
                          value: textOf(_match, ['fase']),
                          icon: Icons.account_tree_outlined,
                        ),
                        KeyValueRow(
                          label: 'Data',
                          value: formatDate(
                            _match['data_hora'],
                            withTime: true,
                          ),
                          icon: Icons.calendar_month_outlined,
                        ),
                        KeyValueRow(
                          label: 'Local',
                          value: textOf(_match, [
                            'local',
                          ], fallback: 'A definir'),
                          icon: Icons.location_on_outlined,
                        ),
                      ],
                    ),
                  ),
                  if (_canOperate) ...[
                    const SizedBox(height: 18),
                    const SectionHeader(
                      title: 'Controle da partida',
                      subtitle:
                          'Ações disponíveis para arbitragem e organização.',
                    ),
                    const SizedBox(height: 10),
                    _ControlGrid(
                      match: _match,
                      busy: _updating,
                      onStart: _start,
                      onScore: () async {
                        await pushPage(
                          context,
                          MatchEventFormScreen(
                            match: _match,
                            type: MatchEventType.score,
                          ),
                        );
                        _load(silent: true);
                      },
                      onPenalty: () async {
                        await pushPage(
                          context,
                          MatchEventFormScreen(
                            match: _match,
                            type: MatchEventType.penalty,
                          ),
                        );
                        _load(silent: true);
                      },
                      onResult: () async {
                        await pushPage(
                          context,
                          MatchResultScreen(match: _match),
                        );
                        _load(silent: true);
                      },
                      onPublish: _publish,
                      onReferee: _canAssignReferee
                          ? () async {
                              await pushPage(
                                context,
                                AssignRefereeScreen(matchId: widget.matchId),
                              );
                              _load(silent: true);
                            }
                          : null,
                    ),
                  ],
                  const SizedBox(height: 20),
                  SectionHeader(
                    title: 'Arbitragem',
                    subtitle:
                        '${asList(_match['arbitragem']).length} profissional(is) designados',
                  ),
                  const SizedBox(height: 10),
                  if (asList(_match['arbitragem']).isEmpty)
                    SurfaceCard(
                      child: Text(
                        'Nenhum árbitro designado.',
                        style: TextStyle(
                          color: Theme.of(context).colorScheme.onSurfaceVariant,
                        ),
                      ),
                    )
                  else
                    SurfaceCard(
                      child: Column(
                        children: asList(_match['arbitragem'])
                            .map(
                              (referee) => ListTile(
                                contentPadding: EdgeInsets.zero,
                                leading: const CircleAvatar(
                                  child: Icon(Icons.sports_score_outlined),
                                ),
                                title: Text(
                                  textOf(referee, ['arbitro']),
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                subtitle: Text(statusLabel(referee['funcao'])),
                              ),
                            )
                            .toList(),
                      ),
                    ),
                  const SizedBox(height: 20),
                  SectionHeader(
                    title: 'Eventos da partida',
                    subtitle: '${asList(_match['eventos']).length} registro(s)',
                  ),
                  const SizedBox(height: 10),
                  if (asList(_match['eventos']).isEmpty)
                    const EmptyState(
                      title: 'Nenhum evento registrado',
                      message: 'Pontuações, faltas e cartões aparecerão aqui.',
                      icon: Icons.timeline,
                    )
                  else
                    ...asList(_match['eventos']).reversed.map(
                      (event) => Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: _EventTile(event: event),
                      ),
                    ),
                ],
              ),
            ),
    );
  }
}

class _Scoreboard extends StatelessWidget {
  const _Scoreboard({required this.match});
  final Map<String, dynamic> match;

  @override
  Widget build(BuildContext context) {
    final palette = AcePalette.of(context);
    final home = textOf(match, ['time_casa']);
    final away = textOf(match, ['time_visitante']);
    final homeScore = intOf(match, ['placar_casa']);
    final awayScore = intOf(match, ['placar_visitante']);
    return Semantics(
      liveRegion: true,
      label:
          '$home, $homeScore pontos. $away, $awayScore pontos. '
          'Partida ${statusLabel(match['status'])}.',
      excludeSemantics: true,
      child: Container(
        padding: const EdgeInsets.all(22),
        decoration: BoxDecoration(
          gradient: LinearGradient(colors: [palette.navyDark, palette.navy]),
          borderRadius: BorderRadius.circular(24),
        ),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const _LivePulse(),
                const SizedBox(width: 8),
                StatusBadge(match['status']),
                if (boolOf(match, ['publicado'])) ...[
                  const SizedBox(width: 8),
                  const Chip(
                    label: Text('Publicado'),
                    avatar: Icon(Icons.public, size: 16),
                  ),
                ],
              ],
            ),
            const SizedBox(height: 22),
            Row(
              children: [
                Expanded(
                  child: Column(
                    children: [
                      const Icon(
                        Icons.shield_outlined,
                        color: Colors.white,
                        size: 42,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        textOf(match, ['time_casa']),
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 14,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(18),
                  ),
                  child: Text(
                    '${intOf(match, ['placar_casa'])} × ${intOf(match, ['placar_visitante'])}',
                    style: TextStyle(
                      fontSize: 34,
                      fontWeight: FontWeight.w900,
                      color: palette.navyDark,
                    ),
                  ),
                ),
                Expanded(
                  child: Column(
                    children: [
                      const Icon(
                        Icons.shield_outlined,
                        color: Colors.white,
                        size: 42,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        textOf(match, ['time_visitante']),
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 18),
            Text(
              'Atualizado ${formatDate(match['atualizado_em'], withTime: true)}',
              style: const TextStyle(color: Colors.white60, fontSize: 12),
            ),
          ],
        ),
      ),
    );
  }
}

class _ControlGrid extends StatelessWidget {
  const _ControlGrid({
    required this.match,
    required this.busy,
    required this.onStart,
    required this.onScore,
    required this.onPenalty,
    required this.onResult,
    required this.onPublish,
    this.onReferee,
  });

  final Map<String, dynamic> match;
  final bool busy;
  final VoidCallback onStart;
  final VoidCallback onScore;
  final VoidCallback onPenalty;
  final VoidCallback onResult;
  final VoidCallback onPublish;
  final VoidCallback? onReferee;

  @override
  Widget build(BuildContext context) {
    final status = match['status']?.toString();
    final actions = <(String, IconData, VoidCallback?)>[
      if (status == 'AGENDADA') ('Iniciar', Icons.play_arrow_rounded, onStart),
      if (status == 'EM_ANDAMENTO')
        ('Pontuação', Icons.add_circle_outline, onScore),
      if (status == 'EM_ANDAMENTO')
        ('Falta/cartão', Icons.warning_amber_rounded, onPenalty),
      if (status == 'EM_ANDAMENTO' || status == 'AGENDADA')
        ('Encerrar', Icons.stop_circle_outlined, onResult),
      if ((status == 'ENCERRADA' || status == 'EM_ANDAMENTO') &&
          !boolOf(match, ['publicado']))
        ('Publicar', Icons.public, onPublish),
      if (onReferee != null &&
          (status == 'AGENDADA' || status == 'EM_ANDAMENTO'))
        ('Arbitragem', Icons.sports_score_outlined, onReferee),
    ];
    if (actions.isEmpty) {
      return SurfaceCard(
        child: Text(
          'Não há ações disponíveis para o status atual.',
          style: TextStyle(
            color: Theme.of(context).colorScheme.onSurfaceVariant,
          ),
        ),
      );
    }
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 10,
        crossAxisSpacing: 10,
        childAspectRatio: 2.2,
      ),
      itemCount: actions.length,
      itemBuilder: (_, index) {
        final action = actions[index];
        return FilledButton.tonalIcon(
          onPressed: busy ? null : action.$3,
          icon: Icon(action.$2),
          label: Text(action.$1),
        );
      },
    );
  }
}

class _EventTile extends StatelessWidget {
  const _EventTile({required this.event});
  final Map<String, dynamic> event;

  @override
  Widget build(BuildContext context) {
    final palette = AcePalette.of(context);
    final type = event['tipo']?.toString() ?? '';
    final icon = switch (type) {
      'PONTUACAO' => Icons.sports_soccer,
      'CARTAO_AMARELO' => Icons.rectangle,
      'CARTAO_VERMELHO' => Icons.rectangle,
      'FALTA' => Icons.warning_amber,
      _ => Icons.flag_outlined,
    };
    final color = switch (type) {
      'PONTUACAO' => palette.success,
      'CARTAO_AMARELO' => Colors.amber.shade700,
      'CARTAO_VERMELHO' => palette.danger,
      _ => palette.orange,
    };
    return SurfaceCard(
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: color.withValues(alpha: .12),
            child: Icon(icon, color: color),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  statusLabel(type),
                  style: const TextStyle(fontWeight: FontWeight.w800),
                ),
                Text(
                  '${textOf(event, ['time'])}${textOf(event, ['atleta'], fallback: '').isNotEmpty ? ' • ${textOf(event, ['atleta'])}' : ''}',
                  style: TextStyle(
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                  ),
                ),
                if (textOf(event, ['observacao'], fallback: '').isNotEmpty)
                  Text(
                    textOf(event, ['observacao']),
                    style: TextStyle(
                      fontSize: 12,
                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                    ),
                  ),
              ],
            ),
          ),
          if (event['minuto'] != null)
            Text(
              '${event['minuto']}′',
              style: TextStyle(
                fontWeight: FontWeight.w900,
                color: Theme.of(context).colorScheme.primary,
              ),
            ),
        ],
      ),
    );
  }
}

enum MatchEventType { score, penalty }

class MatchEventFormScreen extends StatefulWidget {
  const MatchEventFormScreen({
    required this.match,
    required this.type,
    super.key,
  });
  final Map<String, dynamic> match;
  final MatchEventType type;

  @override
  State<MatchEventFormScreen> createState() => _MatchEventFormScreenState();
}

class _MatchEventFormScreenState extends State<MatchEventFormScreen> {
  String? _teamId;
  String? _athleteId;
  String _penaltyType = 'FALTA';
  final _points = TextEditingController(text: '1');
  final _minute = TextEditingController();
  final _observation = TextEditingController();
  List<Map<String, dynamic>> _athletes = [];
  bool _loadingAthletes = false;
  bool _busy = false;

  @override
  void dispose() {
    _points.dispose();
    _minute.dispose();
    _observation.dispose();
    super.dispose();
  }

  Future<void> _loadAthletes(String teamId) async {
    setState(() {
      _teamId = teamId;
      _athleteId = null;
      _loadingAthletes = true;
    });
    try {
      final team = asMap(
        await AppScope.read(context).client.get('/times/$teamId'),
      );
      if (mounted) setState(() => _athletes = asList(team['atletas']));
    } catch (error) {
      if (mounted) showFailure(context, error);
    } finally {
      if (mounted) setState(() => _loadingAthletes = false);
    }
  }

  Future<void> _save() async {
    if (_teamId == null) {
      showFailure(context, const ApiFailure('Selecione o time.'));
      return;
    }
    setState(() => _busy = true);
    try {
      final base = <String, Object?>{
        'timeId': _teamId,
        'alunoId': _athleteId,
        'minuto': int.tryParse(_minute.text),
        'observacao': _observation.text.trim(),
      };
      if (widget.type == MatchEventType.score) {
        await AppScope.read(context).client.post(
          '/partidas/${textOf(widget.match, ['id'])}/pontuacoes',
          body: {...base, 'pontos': int.tryParse(_points.text) ?? 1},
        );
      } else {
        await AppScope.read(context).client.post(
          '/partidas/${textOf(widget.match, ['id'])}/penalidades',
          body: {...base, 'tipo': _penaltyType},
        );
      }
      if (!mounted) return;
      showSuccess(
        context,
        widget.type == MatchEventType.score
            ? 'Pontuação registrada.'
            : 'Penalidade registrada.',
      );
      Navigator.pop(context, true);
    } catch (error) {
      if (mounted) showFailure(context, error);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final homeId = textOf(widget.match, ['time_casa_id']);
    final awayId = textOf(widget.match, ['time_visitante_id']);
    return Scaffold(
      appBar: AppBar(
        title: Text(
          widget.type == MatchEventType.score
              ? 'Registrar pontuação'
              : 'Registrar falta/penalidade',
        ),
      ),
      body: PageBody(
        child: Column(
          children: [
            MatchCard(data: widget.match),
            const SizedBox(height: 18),
            DropdownButtonFormField<String>(
              initialValue: _teamId,
              decoration: const InputDecoration(
                labelText: 'Time',
                prefixIcon: Icon(Icons.shield_outlined),
              ),
              items: [
                DropdownMenuItem(
                  value: homeId,
                  child: Text(textOf(widget.match, ['time_casa'])),
                ),
                DropdownMenuItem(
                  value: awayId,
                  child: Text(textOf(widget.match, ['time_visitante'])),
                ),
              ],
              onChanged: (value) {
                if (value != null) _loadAthletes(value);
              },
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: _athleteId,
              decoration: InputDecoration(
                labelText: 'Atleta (opcional)',
                prefixIcon: const Icon(Icons.person_outline),
                suffixIcon: _loadingAthletes
                    ? const Padding(
                        padding: EdgeInsets.all(12),
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : null,
              ),
              items: [
                const DropdownMenuItem(
                  value: null,
                  child: Text('Sem atleta específico'),
                ),
                ..._athletes.map(
                  (athlete) => DropdownMenuItem(
                    value: textOf(athlete, ['aluno_id']),
                    child: Text(textOf(athlete, ['nome'])),
                  ),
                ),
              ],
              onChanged: (value) => setState(() => _athleteId = value),
            ),
            const SizedBox(height: 12),
            if (widget.type == MatchEventType.score)
              AppTextFormField(
                controller: _points,
                label: 'Pontos',
                keyboardType: TextInputType.number,
                prefixIcon: Icons.add_circle_outline,
              )
            else
              DropdownButtonFormField<String>(
                initialValue: _penaltyType,
                decoration: const InputDecoration(
                  labelText: 'Tipo',
                  prefixIcon: Icon(Icons.warning_amber),
                ),
                items: const [
                  DropdownMenuItem(value: 'FALTA', child: Text('Falta')),
                  DropdownMenuItem(
                    value: 'CARTAO_AMARELO',
                    child: Text('Cartão amarelo'),
                  ),
                  DropdownMenuItem(
                    value: 'CARTAO_VERMELHO',
                    child: Text('Cartão vermelho'),
                  ),
                  DropdownMenuItem(
                    value: 'PENALIDADE',
                    child: Text('Penalidade'),
                  ),
                ],
                onChanged: (value) =>
                    setState(() => _penaltyType = value ?? 'FALTA'),
              ),
            const SizedBox(height: 12),
            AppTextFormField(
              controller: _minute,
              label: 'Minuto da partida',
              keyboardType: TextInputType.number,
              prefixIcon: Icons.timer_outlined,
            ),
            const SizedBox(height: 12),
            AppTextFormField(
              controller: _observation,
              label: 'Observação',
              prefixIcon: Icons.notes_outlined,
              maxLines: 3,
            ),
            const SizedBox(height: 18),
            FilledButton.icon(
              onPressed: _busy ? null : _save,
              icon: const Icon(Icons.save_outlined),
              label: Text(_busy ? 'Registrando...' : 'Registrar'),
            ),
          ],
        ),
      ),
    );
  }
}

class MatchResultScreen extends StatefulWidget {
  const MatchResultScreen({required this.match, super.key});
  final Map<String, dynamic> match;

  @override
  State<MatchResultScreen> createState() => _MatchResultScreenState();
}

class _MatchResultScreenState extends State<MatchResultScreen> {
  late final TextEditingController _home;
  late final TextEditingController _away;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _home = TextEditingController(
      text: intOf(widget.match, ['placar_casa']).toString(),
    );
    _away = TextEditingController(
      text: intOf(widget.match, ['placar_visitante']).toString(),
    );
  }

  @override
  void dispose() {
    _home.dispose();
    _away.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final home = int.tryParse(_home.text);
    final away = int.tryParse(_away.text);
    if (home == null || away == null || home < 0 || away < 0) {
      showFailure(context, const ApiFailure('Informe placares válidos.'));
      return;
    }
    setState(() => _busy = true);
    try {
      await AppScope.read(context).client.put(
        '/partidas/${textOf(widget.match, ['id'])}/resultado',
        body: {'placarCasa': home, 'placarVisitante': away},
      );
      if (!mounted) return;
      showSuccess(context, 'Resultado registrado e partida encerrada.');
      Navigator.pop(context, true);
    } catch (error) {
      if (mounted) showFailure(context, error);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Registrar resultado')),
      body: PageBody(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SectionHeader(
              title: 'Placar final',
              subtitle: 'Ao salvar, a partida será encerrada.',
            ),
            const SizedBox(height: 22),
            Row(
              children: [
                Expanded(
                  child: Column(
                    children: [
                      Text(
                        textOf(widget.match, ['time_casa']),
                        textAlign: TextAlign.center,
                        style: const TextStyle(fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 10),
                      AppTextFormField(
                        controller: _home,
                        label: 'Casa',
                        keyboardType: TextInputType.number,
                      ),
                    ],
                  ),
                ),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 14),
                  child: Text(
                    '×',
                    style: TextStyle(fontSize: 28, fontWeight: FontWeight.w900),
                  ),
                ),
                Expanded(
                  child: Column(
                    children: [
                      Text(
                        textOf(widget.match, ['time_visitante']),
                        textAlign: TextAlign.center,
                        style: const TextStyle(fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 10),
                      AppTextFormField(
                        controller: _away,
                        label: 'Visitante',
                        keyboardType: TextInputType.number,
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 22),
            FilledButton.icon(
              onPressed: _busy ? null : _save,
              icon: const Icon(Icons.sports_score),
              label: Text(_busy ? 'Encerrando...' : 'Salvar resultado final'),
            ),
          ],
        ),
      ),
    );
  }
}

class AssignRefereeScreen extends StatefulWidget {
  const AssignRefereeScreen({required this.matchId, super.key});
  final String matchId;

  @override
  State<AssignRefereeScreen> createState() => _AssignRefereeScreenState();
}

class _AssignRefereeScreenState extends State<AssignRefereeScreen> {
  final _refereeId = TextEditingController(
    text: '00000000-0000-0000-0000-000000000030',
  );
  String _role = 'PRINCIPAL';
  bool _busy = false;

  @override
  void dispose() {
    _refereeId.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (_refereeId.text.trim().isEmpty) {
      showFailure(context, const ApiFailure('Informe o ID do árbitro.'));
      return;
    }
    setState(() => _busy = true);
    try {
      await AppScope.read(context).client.post(
        '/partidas/${widget.matchId}/arbitragem',
        body: {'arbitroId': _refereeId.text.trim(), 'funcao': _role},
      );
      if (!mounted) return;
      showSuccess(context, 'Arbitragem registrada.');
      Navigator.pop(context, true);
    } catch (error) {
      if (mounted) showFailure(context, error);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Registrar arbitragem')),
      body: PageBody(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SurfaceCard(
              color: Theme.of(context).colorScheme.primaryContainer,
              child: Text(
                'A API atual ainda não possui uma rota de listagem de árbitros. O ID do árbitro de demonstração já está preenchido.',
                style: TextStyle(
                  color: Theme.of(context).colorScheme.onPrimaryContainer,
                ),
              ),
            ),
            const SizedBox(height: 16),
            AppTextFormField(
              controller: _refereeId,
              label: 'ID do árbitro',
              prefixIcon: Icons.badge_outlined,
              required: true,
              validator: requiredField,
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: _role,
              decoration: const InputDecoration(
                labelText: 'Função',
                prefixIcon: Icon(Icons.sports_score_outlined),
              ),
              items: const [
                DropdownMenuItem(
                  value: 'PRINCIPAL',
                  child: Text('Árbitro principal'),
                ),
                DropdownMenuItem(
                  value: 'ASSISTENTE',
                  child: Text('Assistente'),
                ),
                DropdownMenuItem(value: 'MESARIO', child: Text('Mesário')),
              ],
              onChanged: (value) =>
                  setState(() => _role = value ?? 'PRINCIPAL'),
            ),
            const SizedBox(height: 18),
            FilledButton.icon(
              onPressed: _busy ? null : _save,
              icon: const Icon(Icons.save_outlined),
              label: Text(_busy ? 'Salvando...' : 'Registrar arbitragem'),
            ),
          ],
        ),
      ),
    );
  }
}
