import 'package:flutter/material.dart';

import '../core/app_scope.dart';
import '../core/data_helpers.dart';
import '../core/theme.dart';
import '../widgets/common.dart';
import 'match_screens.dart';

class CompetitionListScreen extends StatefulWidget {
  const CompetitionListScreen({super.key});

  @override
  State<CompetitionListScreen> createState() => _CompetitionListScreenState();
}

class _CompetitionListScreenState extends State<CompetitionListScreen> {
  final _search = TextEditingController();
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _items = [];
  String? _status;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      _items = asList(
        await AppScope.read(context).client.get(
          '/competicoes',
          query: {'busca': _search.text, 'status': _status, 'limite': 100},
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
    return Scaffold(
      appBar: AppBar(title: const Text('Competições')),
      body: _loading
          ? const LoadingState(message: 'Carregando competições...')
          : _error != null
          ? ErrorState(error: _error!, onRetry: _load)
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  SearchBarField(
                    controller: _search,
                    onSubmitted: (_) => _load(),
                    hint: 'Buscar competição',
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    initialValue: _status,
                    decoration: const InputDecoration(
                      labelText: 'Status',
                      prefixIcon: Icon(Icons.filter_alt_outlined),
                    ),
                    items: const [
                      DropdownMenuItem(
                        value: null,
                        child: Text('Todos os status'),
                      ),
                      DropdownMenuItem(
                        value: 'PLANEJADA',
                        child: Text('Planejada'),
                      ),
                      DropdownMenuItem(
                        value: 'INSCRICOES_ABERTAS',
                        child: Text('Inscrições abertas'),
                      ),
                      DropdownMenuItem(
                        value: 'EM_ANDAMENTO',
                        child: Text('Em andamento'),
                      ),
                      DropdownMenuItem(
                        value: 'ENCERRADA',
                        child: Text('Encerrada'),
                      ),
                    ],
                    onChanged: (value) {
                      setState(() => _status = value);
                      _load();
                    },
                  ),
                  const SizedBox(height: 16),
                  if (_items.isEmpty)
                    const EmptyState(
                      title: 'Nenhuma competição',
                      message:
                          'Não existem competições para os filtros selecionados.',
                      icon: Icons.emoji_events_outlined,
                    )
                  else
                    ..._items.map(
                      (item) => Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: SurfaceCard(
                          onTap: () => pushPage(
                            context,
                            CompetitionDetailScreen(
                              competitionId: textOf(item, ['id']),
                            ),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    width: 48,
                                    height: 48,
                                    decoration: BoxDecoration(
                                      color: Theme.of(
                                        context,
                                      ).colorScheme.primaryContainer,
                                      borderRadius: BorderRadius.circular(14),
                                    ),
                                    child: Icon(
                                      Icons.emoji_events_outlined,
                                      color: Theme.of(
                                        context,
                                      ).colorScheme.onPrimaryContainer,
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Text(
                                      textOf(item, ['nome']),
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w900,
                                        fontSize: 16,
                                      ),
                                    ),
                                  ),
                                  StatusBadge(item['status']),
                                ],
                              ),
                              if (textOf(item, [
                                'descricao',
                              ], fallback: '').isNotEmpty) ...[
                                const SizedBox(height: 12),
                                Text(
                                  textOf(item, ['descricao']),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    color: Theme.of(
                                      context,
                                    ).colorScheme.onSurfaceVariant,
                                  ),
                                ),
                              ],
                              const Divider(height: 24),
                              Row(
                                children: [
                                  Icon(
                                    Icons.calendar_today_outlined,
                                    size: 17,
                                    color: Theme.of(
                                      context,
                                    ).colorScheme.onSurfaceVariant,
                                  ),
                                  const SizedBox(width: 6),
                                  Expanded(
                                    child: Text(
                                      '${formatDate(item['data_inicio'])} a ${formatDate(item['data_fim'])}',
                                      style: TextStyle(
                                        color: Theme.of(
                                          context,
                                        ).colorScheme.onSurfaceVariant,
                                      ),
                                    ),
                                  ),
                                  Text(
                                    '${intOf(item, ['times_inscritos'])} times',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w700,
                                      color: Theme.of(
                                        context,
                                      ).colorScheme.primary,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
    );
  }
}

class CompetitionDetailScreen extends StatefulWidget {
  const CompetitionDetailScreen({required this.competitionId, super.key});
  final String competitionId;

  @override
  State<CompetitionDetailScreen> createState() =>
      _CompetitionDetailScreenState();
}

class _CompetitionDetailScreenState extends State<CompetitionDetailScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabs;
  bool _loading = true;
  Object? _error;
  Map<String, dynamic> _detail = {};
  List<Map<String, dynamic>> _standings = [];
  Map<String, dynamic> _table = {};

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 3, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final results = await Future.wait([
        AppScope.read(
          context,
        ).client.get('/competicoes/${widget.competitionId}'),
        AppScope.read(
          context,
        ).client.get('/competicoes/${widget.competitionId}/classificacao'),
        AppScope.read(
          context,
        ).client.get('/competicoes/${widget.competitionId}/tabela'),
      ]);
      _detail = asMap(results[0]);
      _standings = asList(results[1]);
      _table = asMap(results[2]);
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _enroll() async {
    final team = await pushPage<Map<String, dynamic>>(
      context,
      const TeamPickerScreen(),
    );
    if (team == null || !mounted) return;
    try {
      await AppScope.read(context).client.post(
        '/competicoes/${widget.competitionId}/inscricoes',
        body: {
          'timeId': textOf(team, ['id']),
        },
      );
      if (mounted) {
        showSuccess(context, '${textOf(team, ['nome'])} inscrito com sucesso.');
      }
      _load();
    } catch (error) {
      if (mounted) showFailure(context, error);
    }
  }

  @override
  Widget build(BuildContext context) {
    final canEnroll = AppScope.of(
      context,
    ).hasAnyProfile({'ESCOLA', 'ADMINISTRADOR', 'ORGANIZADOR'});
    return Scaffold(
      appBar: AppBar(
        title: const Text('Competição'),
        bottom: TabBar(
          controller: _tabs,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          tabs: const [
            Tab(text: 'Visão geral'),
            Tab(text: 'Classificação'),
            Tab(text: 'Tabela'),
          ],
        ),
      ),
      body: _loading
          ? const LoadingState()
          : _error != null
          ? ErrorState(error: _error!, onRetry: _load)
          : TabBarView(
              controller: _tabs,
              children: [
                _OverviewTab(
                  detail: _detail,
                  onEnroll: canEnroll ? _enroll : null,
                ),
                _StandingsTab(items: _standings),
                _CompetitionTableTab(
                  data: _table,
                  competitionId: widget.competitionId,
                ),
              ],
            ),
    );
  }
}

class _OverviewTab extends StatelessWidget {
  const _OverviewTab({required this.detail, this.onEnroll});
  final Map<String, dynamic> detail;
  final VoidCallback? onEnroll;

  @override
  Widget build(BuildContext context) {
    final competition = asMap(detail['competicao']);
    final modalities = asList(detail['modalidades']);
    final categories = asList(detail['categorias']);
    final phases = asList(detail['fases']);
    return PageBody(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SurfaceCard(
            color: AcePalette.of(context).navy,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        textOf(competition, ['nome']),
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          fontSize: 22,
                        ),
                      ),
                    ),
                    StatusBadge(competition['status']),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  textOf(competition, ['descricao']),
                  style: const TextStyle(color: Colors.white70),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    const Icon(
                      Icons.calendar_month,
                      size: 18,
                      color: Colors.white70,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      '${formatDate(competition['data_inicio'])} a ${formatDate(competition['data_fim'])}',
                      style: const TextStyle(color: Colors.white70),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),
          SurfaceCard(
            child: Column(
              children: [
                KeyValueRow(
                  label: 'Formato',
                  value: statusLabel(competition['formato']),
                  icon: Icons.account_tree_outlined,
                ),
                KeyValueRow(
                  label: 'Abrangência',
                  value: statusLabel(competition['abrangencia']),
                  icon: Icons.public,
                ),
                KeyValueRow(
                  label: 'Organizador',
                  value: textOf(competition, ['organizador']),
                  icon: Icons.person_outline,
                ),
                KeyValueRow(
                  label: 'Inscrições até',
                  value: formatDate(
                    competition['data_limite_inscricao'],
                    withTime: true,
                  ),
                  icon: Icons.how_to_reg_outlined,
                ),
              ],
            ),
          ),
          if (onEnroll != null) ...[
            const SizedBox(height: 14),
            FilledButton.icon(
              onPressed: onEnroll,
              icon: const Icon(Icons.app_registration),
              label: const Text('Inscrever um time'),
            ),
          ],
          const SizedBox(height: 22),
          const SectionHeader(title: 'Modalidades e categorias'),
          const SizedBox(height: 10),
          SurfaceCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    ...modalities.map(
                      (item) => Chip(
                        avatar: const Icon(Icons.sports, size: 18),
                        label: Text(textOf(item, ['nome'])),
                      ),
                    ),
                    ...categories.map(
                      (item) => Chip(
                        avatar: const Icon(Icons.category_outlined, size: 18),
                        label: Text(textOf(item, ['nome'])),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 22),
          const SectionHeader(title: 'Fases da competição'),
          const SizedBox(height: 10),
          if (phases.isEmpty)
            const EmptyState(
              title: 'Fases não configuradas',
              message:
                  'A configuração completa das fases é realizada na versão para PC.',
              icon: Icons.account_tree_outlined,
            )
          else
            ...phases.map(
              (phase) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  tileColor: Theme.of(context).colorScheme.surface,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                  leading: CircleAvatar(child: Text(textOf(phase, ['ordem']))),
                  title: Text(
                    textOf(phase, ['nome']),
                    style: const TextStyle(fontWeight: FontWeight.w700),
                  ),
                  subtitle: Text(statusLabel(phase['tipo'])),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _StandingsTab extends StatelessWidget {
  const _StandingsTab({required this.items});
  final List<Map<String, dynamic>> items;

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const EmptyState(
        title: 'Classificação indisponível',
        message:
            'A tabela será preenchida conforme as partidas forem encerradas.',
        icon: Icons.leaderboard_outlined,
      );
    }
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: items.length,
      separatorBuilder: (_, _) => const SizedBox(height: 8),
      itemBuilder: (_, index) {
        final item = items[index];
        return SurfaceCard(
          child: Row(
            children: [
              CircleAvatar(
                backgroundColor: index < 3
                    ? Theme.of(context).colorScheme.secondaryContainer
                    : Theme.of(context).colorScheme.primaryContainer,
                child: Text(
                  '${index + 1}º',
                  style: TextStyle(
                    fontWeight: FontWeight.w900,
                    color: Theme.of(context).colorScheme.primary,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      textOf(item, ['time_nome', 'time']),
                      style: const TextStyle(fontWeight: FontWeight.w800),
                    ),
                    Text(
                      '${intOf(item, ['jogos'])} J • ${intOf(item, ['vitorias'])} V • ${intOf(item, ['empates'])} E • ${intOf(item, ['derrotas'])} D',
                      style: TextStyle(
                        color: Theme.of(context).colorScheme.onSurfaceVariant,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    '${intOf(item, ['pontos'])} pts',
                    style: TextStyle(
                      fontWeight: FontWeight.w900,
                      color: Theme.of(context).colorScheme.primary,
                    ),
                  ),
                  Text(
                    'Saldo ${intOf(item, ['saldo_gols', 'saldo'])}',
                    style: TextStyle(
                      fontSize: 12,
                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}

class _CompetitionTableTab extends StatelessWidget {
  const _CompetitionTableTab({required this.data, required this.competitionId});
  final Map<String, dynamic> data;
  final String competitionId;

  @override
  Widget build(BuildContext context) {
    final matches = asList(data['partidas']);
    final phases = asList(data['fases']);
    if (matches.isEmpty && phases.isEmpty) {
      return const EmptyState(
        title: 'Tabela ainda não gerada',
        message:
            'O chaveamento e a geração automática da tabela são feitos na versão para PC.',
        icon: Icons.table_chart_outlined,
      );
    }
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        if (phases.isNotEmpty) ...[
          const SectionHeader(title: 'Fases'),
          const SizedBox(height: 10),
          ...phases.map(
            (phase) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: ListTile(
                tileColor: Theme.of(context).colorScheme.surface,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
                leading: Icon(
                  Icons.account_tree_outlined,
                  color: Theme.of(context).colorScheme.primary,
                ),
                title: Text(
                  textOf(phase, ['nome']),
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
                subtitle: Text(statusLabel(phase['tipo'])),
              ),
            ),
          ),
          const SizedBox(height: 16),
        ],
        const SectionHeader(title: 'Partidas'),
        const SizedBox(height: 10),
        ...matches.map(
          (match) => Padding(
            padding: const EdgeInsets.only(bottom: 10),
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
    );
  }
}

class TeamPickerScreen extends StatefulWidget {
  const TeamPickerScreen({super.key});

  @override
  State<TeamPickerScreen> createState() => _TeamPickerScreenState();
}

class _TeamPickerScreenState extends State<TeamPickerScreen> {
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _teams = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      _teams = asList(
        await AppScope.read(
          context,
        ).client.get('/times', query: {'limite': 100}),
      );
      _error = null;
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Selecionar time')),
      body: _loading
          ? const LoadingState()
          : _error != null
          ? ErrorState(error: _error!, onRetry: _load)
          : ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: _teams.length,
              separatorBuilder: (_, _) => const SizedBox(height: 8),
              itemBuilder: (_, index) {
                final team = _teams[index];
                return ListTile(
                  tileColor: Theme.of(context).colorScheme.surface,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                  leading: const CircleAvatar(
                    child: Icon(Icons.shield_outlined),
                  ),
                  title: Text(
                    textOf(team, ['nome']),
                    style: const TextStyle(fontWeight: FontWeight.w700),
                  ),
                  subtitle: Text(
                    '${textOf(team, ['modalidade'])} • ${textOf(team, ['categoria'])}',
                  ),
                  onTap: () => Navigator.pop(context, team),
                );
              },
            ),
    );
  }
}

class CalendarScreen extends StatefulWidget {
  const CalendarScreen({super.key});

  @override
  State<CalendarScreen> createState() => _CalendarScreenState();
}

class _CalendarScreenState extends State<CalendarScreen> {
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _items = [];
  DateTime? _from;
  DateTime? _to;

  bool get _canManage =>
      AppScope.read(context).hasAnyProfile({'ADMINISTRADOR', 'ORGANIZADOR'});

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
        await AppScope.read(context).client.get(
          '/calendario',
          query: {
            'de': _from?.toIso8601String(),
            'ate': _to?.toIso8601String(),
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

  Future<void> _pickPeriod() async {
    final range = await showDateRangePicker(
      context: context,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 730)),
      locale: const Locale('pt', 'BR'),
    );
    if (range != null) {
      setState(() {
        _from = range.start;
        _to = DateTime(
          range.end.year,
          range.end.month,
          range.end.day,
          23,
          59,
          59,
        );
      });
      _load();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Calendário de jogos'),
        actions: [
          IconButton(
            tooltip: 'Filtrar por período',
            onPressed: _pickPeriod,
            icon: const Icon(Icons.date_range),
          ),
          IconButton(
            tooltip: 'Ver locais',
            onPressed: () => pushPage(context, const LocationsScreen()),
            icon: const Icon(Icons.location_on_outlined),
          ),
        ],
      ),
      body: _loading
          ? const LoadingState()
          : _error != null
          ? ErrorState(error: _error!, onRetry: _load)
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  if (_from != null) ...[
                    SurfaceCard(
                      color: Theme.of(context).colorScheme.primaryContainer,
                      child: Row(
                        children: [
                          Icon(
                            Icons.filter_alt_outlined,
                            color: Theme.of(
                              context,
                            ).colorScheme.onPrimaryContainer,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'Período: ${formatDate(_from)} a ${formatDate(_to)}',
                            ),
                          ),
                          IconButton(
                            tooltip: 'Limpar filtro de período',
                            onPressed: () {
                              setState(() {
                                _from = null;
                                _to = null;
                              });
                              _load();
                            },
                            icon: const Icon(Icons.close),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],
                  if (_items.isEmpty)
                    const EmptyState(
                      title: 'Nenhum jogo agendado',
                      message: 'Não há partidas no período selecionado.',
                      icon: Icons.calendar_month_outlined,
                    )
                  else
                    ..._items.map(
                      (match) => Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: Stack(
                          children: [
                            MatchCard(
                              data: match,
                              onTap: () => pushPage(
                                context,
                                LiveMatchScreen(matchId: textOf(match, ['id'])),
                              ),
                            ),
                            if (_canManage)
                              Positioned(
                                right: 8,
                                bottom: 8,
                                child: IconButton.filledTonal(
                                  tooltip: 'Editar partida agendada',
                                  onPressed: () async {
                                    final changed = await pushPage<bool>(
                                      context,
                                      ScheduleMatchScreen(match: match),
                                    );
                                    if (changed == true) _load();
                                  },
                                  icon: const Icon(
                                    Icons.edit_calendar_outlined,
                                  ),
                                ),
                              ),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
            ),
    );
  }
}

class ScheduleMatchScreen extends StatefulWidget {
  const ScheduleMatchScreen({required this.match, super.key});
  final Map<String, dynamic> match;

  @override
  State<ScheduleMatchScreen> createState() => _ScheduleMatchScreenState();
}

class _ScheduleMatchScreenState extends State<ScheduleMatchScreen> {
  late DateTime _dateTime;
  final _reason = TextEditingController();
  List<Map<String, dynamic>> _locations = [];
  String? _locationId;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _dateTime =
        DateTime.tryParse(
          widget.match['data_hora']?.toString() ?? '',
        )?.toLocal() ??
        DateTime.now().add(const Duration(days: 1));
    _locationId = widget.match['local_id']?.toString();
    _loadLocations();
  }

  @override
  void dispose() {
    _reason.dispose();
    super.dispose();
  }

  Future<void> _loadLocations() async {
    try {
      final result = asList(await AppScope.read(context).client.get('/locais'));
      if (mounted) setState(() => _locations = result);
    } catch (error) {
      if (mounted) showFailure(context, error);
    }
  }

  Future<void> _pickDateTime() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _dateTime,
      firstDate: DateTime.now().subtract(const Duration(days: 30)),
      lastDate: DateTime.now().add(const Duration(days: 730)),
      locale: const Locale('pt', 'BR'),
    );
    if (date == null || !mounted) return;
    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_dateTime),
    );
    if (time != null) {
      setState(
        () => _dateTime = DateTime(
          date.year,
          date.month,
          date.day,
          time.hour,
          time.minute,
        ),
      );
    }
  }

  Future<void> _save() async {
    setState(() => _busy = true);
    try {
      await AppScope.read(context).client.put(
        '/partidas/${textOf(widget.match, ['id'])}/calendario',
        body: {
          'dataHora': _dateTime.toIso8601String(),
          'localId': _locationId,
          'motivo': _reason.text.trim(),
        },
      );
      if (!mounted) return;
      showSuccess(context, 'Calendário atualizado.');
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
      appBar: AppBar(title: const Text('Agendar partida')),
      body: PageBody(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            MatchCard(data: widget.match),
            const SizedBox(height: 18),
            ListTile(
              tileColor: Theme.of(context).colorScheme.surface,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
              leading: Icon(
                Icons.calendar_month,
                color: Theme.of(context).colorScheme.primary,
              ),
              title: const Text('Data e horário'),
              subtitle: Text(
                formatDate(_dateTime.toIso8601String(), withTime: true),
              ),
              trailing: const Icon(Icons.edit_outlined),
              onTap: _pickDateTime,
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: _locationId,
              decoration: const InputDecoration(
                labelText: 'Local',
                prefixIcon: Icon(Icons.location_on_outlined),
              ),
              items: _locations
                  .map(
                    (item) => DropdownMenuItem(
                      value: textOf(item, ['id']),
                      child: Text(
                        '${textOf(item, ['nome'])} • ${textOf(item, ['cidade'])}',
                      ),
                    ),
                  )
                  .toList(),
              onChanged: (value) => setState(() => _locationId = value),
            ),
            const SizedBox(height: 12),
            AppTextFormField(
              controller: _reason,
              label: 'Motivo / observação',
              prefixIcon: Icons.notes_outlined,
              maxLines: 3,
            ),
            const SizedBox(height: 18),
            FilledButton.icon(
              onPressed: _busy ? null : _save,
              icon: const Icon(Icons.save_outlined),
              label: Text(_busy ? 'Salvando...' : 'Salvar agendamento'),
            ),
          ],
        ),
      ),
    );
  }
}

class LocationsScreen extends StatefulWidget {
  const LocationsScreen({super.key});

  @override
  State<LocationsScreen> createState() => _LocationsScreenState();
}

class _LocationsScreenState extends State<LocationsScreen> {
  final _search = TextEditingController();
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _items = [];

  bool get _canCreate =>
      AppScope.read(context).hasAnyProfile({'ADMINISTRADOR', 'ORGANIZADOR'});

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      _items = asList(
        await AppScope.read(
          context,
        ).client.get('/locais', query: {'busca': _search.text}),
      );
      _error = null;
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Locais de partida')),
      body: _loading
          ? const LoadingState()
          : _error != null
          ? ErrorState(error: _error!, onRetry: _load)
          : ListView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
              children: [
                SearchBarField(
                  controller: _search,
                  onSubmitted: (_) => _load(),
                  hint: 'Buscar local ou cidade',
                ),
                const SizedBox(height: 14),
                if (_items.isEmpty)
                  const EmptyState(
                    title: 'Nenhum local cadastrado',
                    icon: Icons.location_off_outlined,
                  )
                else
                  ..._items.map(
                    (item) => Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: SurfaceCard(
                        child: Row(
                          children: [
                            Container(
                              width: 50,
                              height: 50,
                              decoration: BoxDecoration(
                                color: Theme.of(
                                  context,
                                ).colorScheme.primaryContainer,
                                borderRadius: BorderRadius.circular(14),
                              ),
                              child: Icon(
                                Icons.stadium_outlined,
                                color: Theme.of(
                                  context,
                                ).colorScheme.onPrimaryContainer,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    textOf(item, ['nome']),
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                                  Text(
                                    '${statusLabel(item['tipo'])} • ${textOf(item, ['cidade'])}/${textOf(item, ['estado'])}',
                                    style: TextStyle(
                                      color: Theme.of(
                                        context,
                                      ).colorScheme.onSurfaceVariant,
                                    ),
                                  ),
                                  if (item['capacidade'] != null)
                                    Text(
                                      'Capacidade: ${item['capacidade']}',
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: Theme.of(
                                          context,
                                        ).colorScheme.primary,
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
              ],
            ),
      floatingActionButton: _canCreate
          ? FloatingActionButton.extended(
              onPressed: () async {
                final changed = await pushPage<bool>(
                  context,
                  const LocationFormScreen(),
                );
                if (changed == true) _load();
              },
              icon: const Icon(Icons.add_location_alt_outlined),
              label: const Text('Novo local'),
            )
          : null,
    );
  }
}

class LocationFormScreen extends StatefulWidget {
  const LocationFormScreen({super.key});

  @override
  State<LocationFormScreen> createState() => _LocationFormScreenState();
}

class _LocationFormScreenState extends State<LocationFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _capacity = TextEditingController();
  final _street = TextEditingController();
  final _number = TextEditingController();
  final _neighborhood = TextEditingController();
  final _city = TextEditingController(text: 'Itanhaém');
  final _state = TextEditingController(text: 'SP');
  final _cep = TextEditingController();
  String _type = 'GINASIO';
  bool _busy = false;

  @override
  void dispose() {
    for (final controller in [
      _name,
      _capacity,
      _street,
      _number,
      _neighborhood,
      _city,
      _state,
      _cep,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _busy = true);
    try {
      await AppScope.read(context).client.post(
        '/locais',
        body: {
          'nome': _name.text.trim(),
          'tipo': _type,
          'capacidade': int.tryParse(_capacity.text),
          'logradouro': _street.text.trim(),
          'numero': _number.text.trim(),
          'bairro': _neighborhood.text.trim(),
          'cidade': _city.text.trim(),
          'estado': _state.text.trim().toUpperCase(),
          'cep': digitsOnly(_cep.text),
        },
      );
      if (!mounted) return;
      showSuccess(context, 'Local cadastrado.');
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
      appBar: AppBar(title: const Text('Cadastrar local')),
      body: Form(
        key: _formKey,
        child: PageBody(
          child: Column(
            children: [
              AppTextFormField(
                controller: _name,
                label: 'Nome do local',
                prefixIcon: Icons.stadium_outlined,
                required: true,
                validator: requiredField,
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _type,
                decoration: const InputDecoration(
                  labelText: 'Tipo',
                  prefixIcon: Icon(Icons.category_outlined),
                ),
                items: const [
                  DropdownMenuItem(value: 'GINASIO', child: Text('Ginásio')),
                  DropdownMenuItem(value: 'QUADRA', child: Text('Quadra')),
                  DropdownMenuItem(value: 'CAMPO', child: Text('Campo')),
                  DropdownMenuItem(value: 'PISTA', child: Text('Pista')),
                  DropdownMenuItem(value: 'OUTRO', child: Text('Outro')),
                ],
                onChanged: (value) =>
                    setState(() => _type = value ?? 'GINASIO'),
              ),
              const SizedBox(height: 12),
              AppTextFormField(
                controller: _capacity,
                label: 'Capacidade',
                keyboardType: TextInputType.number,
                prefixIcon: Icons.groups_outlined,
              ),
              const SizedBox(height: 12),
              AppTextFormField(
                controller: _street,
                label: 'Logradouro',
                prefixIcon: Icons.route_outlined,
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    flex: 2,
                    child: AppTextFormField(
                      controller: _number,
                      label: 'Número',
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    flex: 3,
                    child: AppTextFormField(
                      controller: _neighborhood,
                      label: 'Bairro',
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    flex: 4,
                    child: AppTextFormField(
                      controller: _city,
                      label: 'Cidade',
                      required: true,
                      validator: requiredField,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    flex: 2,
                    child: AppTextFormField(
                      controller: _state,
                      label: 'UF',
                      required: true,
                      validator: (value) =>
                          value?.trim().length == 2 ? null : 'UF inválida',
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              AppTextFormField(
                controller: _cep,
                label: 'CEP',
                keyboardType: TextInputType.number,
                prefixIcon: Icons.location_searching,
              ),
              const SizedBox(height: 18),
              FilledButton.icon(
                onPressed: _busy ? null : _save,
                icon: const Icon(Icons.save_outlined),
                label: Text(_busy ? 'Salvando...' : 'Cadastrar local'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
