import 'package:flutter/material.dart';

import '../core/app_scope.dart';
import '../core/data_helpers.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

class TeamListScreen extends StatefulWidget {
  const TeamListScreen({this.embedded = false, super.key});
  final bool embedded;

  @override
  State<TeamListScreen> createState() => _TeamListScreenState();
}

class _TeamListScreenState extends State<TeamListScreen> {
  final _search = TextEditingController();
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _items = [];
  List<Map<String, dynamic>> _modalities = [];
  List<Map<String, dynamic>> _categories = [];
  String? _modalityId;
  String? _categoryId;

  @override
  void initState() {
    super.initState();
    _loadFilters();
    _load();
  }

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  Future<void> _loadFilters() async {
    try {
      final results = await Future.wait([
        AppScope.read(context).client.get('/modalidades'),
        AppScope.read(context).client.get('/categorias'),
      ]);
      if (mounted) {
        setState(() {
          _modalities = asList(results[0]);
          _categories = asList(results[1]);
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
          '/times',
          query: {
            'busca': _search.text,
            'modalidadeId': _modalityId,
            'categoriaId': _categoryId,
            'pagina': 1,
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

  Future<void> _openForm([Map<String, dynamic>? team]) async {
    final changed = await pushPage<bool>(
      context,
      TeamFormScreen(teamId: team?['id']?.toString(), initialData: team),
    );
    if (changed == true) _load();
  }

  Widget _body() {
    if (_loading) return const LoadingState(message: 'Carregando times...');
    if (_error != null) return ErrorState(error: _error!, onRetry: _load);
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
        children: [
          SearchBarField(
            controller: _search,
            onSubmitted: (_) => _load(),
            hint: 'Buscar time ou escola',
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: DropdownButtonFormField<String>(
                  initialValue: _modalityId,
                  decoration: const InputDecoration(labelText: 'Modalidade'),
                  items: [
                    const DropdownMenuItem(value: null, child: Text('Todas')),
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
              ),
              const SizedBox(width: 10),
              Expanded(
                child: DropdownButtonFormField<String>(
                  initialValue: _categoryId,
                  decoration: const InputDecoration(labelText: 'Categoria'),
                  items: [
                    const DropdownMenuItem(value: null, child: Text('Todas')),
                    ..._categories.map(
                      (item) => DropdownMenuItem(
                        value: textOf(item, ['id']),
                        child: Text(textOf(item, ['nome'])),
                      ),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() => _categoryId = value);
                    _load();
                  },
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          if (_items.isEmpty)
            EmptyState(
              title: 'Nenhum time encontrado',
              message: 'Crie um time e vincule os atletas da escola.',
              icon: Icons.groups_2_outlined,
              action:
                  AppScope.of(
                    context,
                  ).hasAnyProfile({'ESCOLA', 'ADMINISTRADOR'})
                  ? FilledButton.icon(
                      onPressed: _openForm,
                      icon: const Icon(Icons.add),
                      label: const Text('Criar time'),
                    )
                  : null,
            )
          else
            ..._items.map(
              (team) => Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: SurfaceCard(
                  onTap: () => pushPage(
                    context,
                    TeamDetailScreen(teamId: textOf(team, ['id'])),
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 54,
                        height: 54,
                        decoration: BoxDecoration(
                          color: Theme.of(context).colorScheme.primaryContainer,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Icon(
                          Icons.shield_outlined,
                          color: Theme.of(
                            context,
                          ).colorScheme.onPrimaryContainer,
                          size: 30,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              textOf(team, ['nome']),
                              style: const TextStyle(
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Text(
                              '${textOf(team, ['modalidade'])} • ${textOf(team, ['categoria'])}',
                              style: TextStyle(
                                color: Theme.of(
                                  context,
                                ).colorScheme.onSurfaceVariant,
                              ),
                            ),
                            const SizedBox(height: 7),
                            Text(
                              '${intOf(team, ['atletas'])} atletas • ${intOf(team, ['titulares'])} titulares',
                              style: TextStyle(
                                fontSize: 12,
                                color: Theme.of(context).colorScheme.primary,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.chevron_right),
                    ],
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
      appBar: AppBar(title: const Text('Times')),
      body: body,
      floatingActionButton:
          AppScope.of(context).hasAnyProfile({'ESCOLA', 'ADMINISTRADOR'})
          ? FloatingActionButton.extended(
              onPressed: _openForm,
              icon: const Icon(Icons.group_add_outlined),
              label: const Text('Novo time'),
            )
          : null,
    );
  }
}

class TeamFormScreen extends StatefulWidget {
  const TeamFormScreen({this.teamId, this.initialData, super.key});
  final String? teamId;
  final Map<String, dynamic>? initialData;
  bool get editing => teamId != null;

  @override
  State<TeamFormScreen> createState() => _TeamFormScreenState();
}

class _TeamFormScreenState extends State<TeamFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _coach = TextEditingController();
  final _schoolId = TextEditingController();
  List<Map<String, dynamic>> _modalities = [];
  List<Map<String, dynamic>> _categories = [];
  String? _modalityId;
  String? _categoryId;
  bool _active = true;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    final data = widget.initialData ?? {};
    _name.text = textOf(data, ['nome'], fallback: '');
    _coach.text = textOf(data, ['treinador', 'tecnico'], fallback: '');
    _schoolId.text = textOf(data, ['escola_id'], fallback: '');
    _modalityId = data['modalidade_id']?.toString();
    _categoryId = data['categoria_id']?.toString();
    _active = data['ativo'] != false;
    _loadOptions();
  }

  @override
  void dispose() {
    _name.dispose();
    _coach.dispose();
    _schoolId.dispose();
    super.dispose();
  }

  Future<void> _loadOptions() async {
    try {
      final results = await Future.wait([
        AppScope.read(context).client.get('/modalidades'),
        AppScope.read(context).client.get('/categorias'),
      ]);
      if (mounted) {
        setState(() {
          _modalities = asList(results[0]);
          _categories = asList(results[1]);
        });
      }
    } catch (error) {
      if (mounted) showFailure(context, error);
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    final session = AppScope.read(context);
    final body = <String, Object?>{
      'nome': _name.text.trim(),
      'tecnico': _coach.text.trim(),
      'modalidadeId': _modalityId,
      'categoriaId': _categoryId,
      'ativo': _active,
      if (session.profile != 'ESCOLA') 'escolaId': _schoolId.text.trim(),
    };
    setState(() => _busy = true);
    try {
      if (widget.editing) {
        await session.client.put('/times/${widget.teamId}', body: body);
      } else {
        await session.client.post('/times', body: body);
      }
      if (!mounted) return;
      showSuccess(
        context,
        widget.editing ? 'Time atualizado.' : 'Time criado.',
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
    final session = AppScope.of(context);
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.editing ? 'Editar time' : 'Criar time'),
      ),
      body: Form(
        key: _formKey,
        child: PageBody(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SectionHeader(
                title: 'Identificação do time',
                subtitle: 'Informe os dados principais da equipe.',
              ),
              const SizedBox(height: 18),
              AppTextFormField(
                controller: _name,
                label: 'Nome do time',
                prefixIcon: Icons.shield_outlined,
                required: true,
                validator: requiredField,
              ),
              const SizedBox(height: 12),
              AppTextFormField(
                controller: _coach,
                label: 'Técnico / responsável',
                prefixIcon: Icons.person_outline,
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _modalityId,
                decoration: const InputDecoration(
                  labelText: 'Modalidade *',
                  helperText: 'Campo obrigatório',
                  prefixIcon: Icon(Icons.sports),
                ),
                items: _modalities
                    .map(
                      (item) => DropdownMenuItem(
                        value: textOf(item, ['id']),
                        child: Text(textOf(item, ['nome'])),
                      ),
                    )
                    .toList(),
                validator: (value) =>
                    value == null ? 'Selecione a modalidade' : null,
                onChanged: (value) => setState(() => _modalityId = value),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _categoryId,
                decoration: const InputDecoration(
                  labelText: 'Categoria *',
                  helperText: 'Campo obrigatório',
                  prefixIcon: Icon(Icons.category_outlined),
                ),
                items: _categories
                    .map(
                      (item) => DropdownMenuItem(
                        value: textOf(item, ['id']),
                        child: Text(textOf(item, ['nome'])),
                      ),
                    )
                    .toList(),
                validator: (value) =>
                    value == null ? 'Selecione a categoria' : null,
                onChanged: (value) => setState(() => _categoryId = value),
              ),
              if (session.profile != 'ESCOLA') ...[
                const SizedBox(height: 12),
                AppTextFormField(
                  controller: _schoolId,
                  label: 'ID da escola',
                  prefixIcon: Icons.school_outlined,
                  required: true,
                  validator: requiredField,
                ),
              ],
              const SizedBox(height: 12),
              SwitchListTile.adaptive(
                value: _active,
                onChanged: (value) => setState(() => _active = value),
                contentPadding: EdgeInsets.zero,
                title: const Text('Time ativo'),
                subtitle: const Text('Times inativos não aparecem nas listas.'),
              ),
              const SizedBox(height: 18),
              FilledButton.icon(
                onPressed: _busy ? null : _save,
                icon: const Icon(Icons.save_outlined),
                label: Text(_busy ? 'Salvando...' : 'Salvar time'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class TeamDetailScreen extends StatefulWidget {
  const TeamDetailScreen({required this.teamId, super.key});
  final String teamId;

  @override
  State<TeamDetailScreen> createState() => _TeamDetailScreenState();
}

class _TeamDetailScreenState extends State<TeamDetailScreen> {
  bool _loading = true;
  Object? _error;
  Map<String, dynamic> _team = {};
  Map<String, dynamic> _performance = {};

  bool get _canEdit =>
      AppScope.read(context).hasAnyProfile({'ESCOLA', 'ADMINISTRADOR'});

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
      final results = await Future.wait([
        AppScope.read(context).client.get('/times/${widget.teamId}'),
        AppScope.read(context).client.get('/times/${widget.teamId}/ranking'),
      ]);
      _team = asMap(results[0]);
      _performance = asMap(asMap(results[1])['desempenho']);
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _editTeam() async {
    final changed = await pushPage<bool>(
      context,
      TeamFormScreen(teamId: widget.teamId, initialData: _team),
    );
    if (changed == true) _load();
  }

  Future<void> _addAthlete() async {
    final selected = await pushPage<Map<String, dynamic>>(
      context,
      const AthletePickerScreen(),
    );
    if (selected == null || !mounted) return;
    await pushPage(
      context,
      TeamAthleteFormScreen(teamId: widget.teamId, athlete: selected),
    );
    _load();
  }

  Future<void> _editAthlete(Map<String, dynamic> athlete) async {
    await pushPage(
      context,
      TeamAthleteFormScreen(
        teamId: widget.teamId,
        athlete: athlete,
        editing: true,
      ),
    );
    _load();
  }

  Future<void> _removeAthlete(Map<String, dynamic> athlete) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Remover atleta'),
        content: Text('Remover ${textOf(athlete, ['nome'])} deste time?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Remover'),
          ),
        ],
      ),
    );
    if (confirmed != true || !mounted) return;
    try {
      await AppScope.read(context).client.delete(
        '/times/${widget.teamId}/atletas/${textOf(athlete, ['aluno_id'])}',
      );
      if (mounted) showSuccess(context, 'Atleta removido do time.');
      _load();
    } catch (error) {
      if (mounted) showFailure(context, error);
    }
  }

  Future<void> _defineCaptain(Map<String, dynamic> athlete) async {
    try {
      await AppScope.read(context).client.put(
        '/times/${widget.teamId}/capitao',
        body: {
          'alunoId': textOf(athlete, ['aluno_id']),
        },
      );
      if (mounted) {
        showSuccess(context, '${textOf(athlete, ['nome'])} é o novo capitão.');
      }
      _load();
    } catch (error) {
      if (mounted) showFailure(context, error);
    }
  }

  Future<void> _saveLineup() async {
    final athletes = asList(_team['atletas']);
    final selected = <String>{
      for (final item in athletes)
        if (boolOf(item, ['titular'])) textOf(item, ['aluno_id']),
    };
    final result = await showModalBottomSheet<Set<String>>(
      context: context,
      isScrollControlled: true,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 28),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SectionHeader(
                  title: 'Escalação atual',
                  subtitle: 'Marque os atletas titulares.',
                ),
                const SizedBox(height: 12),
                Flexible(
                  child: ListView(
                    shrinkWrap: true,
                    children: athletes.map((athlete) {
                      final id = textOf(athlete, ['aluno_id']);
                      return CheckboxListTile(
                        value: selected.contains(id),
                        onChanged: (checked) => setModalState(
                          () => checked == true
                              ? selected.add(id)
                              : selected.remove(id),
                        ),
                        title: Text(textOf(athlete, ['nome'])),
                        subtitle: Text(
                          '${textOf(athlete, ['posicao'])} • Camisa ${textOf(athlete, ['numero_camisa'])}',
                        ),
                      );
                    }).toList(),
                  ),
                ),
                const SizedBox(height: 12),
                FilledButton(
                  onPressed: () => Navigator.pop(context, selected),
                  child: const Text('Salvar escalação'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
    if (result == null || !mounted) return;
    try {
      await AppScope.read(context).client.put(
        '/times/${widget.teamId}/escalacao',
        body: {
          'atletas': athletes
              .where((item) => result.contains(textOf(item, ['aluno_id'])))
              .map(
                (item) => {
                  'alunoId': textOf(item, ['aluno_id']),
                  'numeroCamisa': intOf(item, ['numero_camisa']),
                  'posicao': textOf(item, ['posicao'], fallback: ''),
                  'funcao': textOf(item, ['funcao'], fallback: 'JOGADOR'),
                },
              )
              .toList(),
        },
      );
      if (mounted) showSuccess(context, 'Escalação atualizada.');
      _load();
    } catch (error) {
      if (mounted) showFailure(context, error);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Detalhes do time'),
        actions: [
          if (_canEdit && _team.isNotEmpty)
            IconButton(
              tooltip: 'Editar time',
              onPressed: _editTeam,
              icon: const Icon(Icons.edit_outlined),
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
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                children: [
                  SurfaceCard(
                    color: AcePalette.of(context).navy,
                    child: Row(
                      children: [
                        Container(
                          width: 70,
                          height: 70,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Icon(
                            Icons.shield_outlined,
                            size: 42,
                            color: AcePalette.of(context).navy,
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                textOf(_team, ['nome']),
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 22,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${textOf(_team, ['modalidade'])} • ${textOf(_team, ['categoria'])}',
                                style: const TextStyle(color: Colors.white70),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                textOf(_team, ['escola']),
                                style: const TextStyle(color: Colors.white70),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  GridView.count(
                    crossAxisCount: 3,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    mainAxisSpacing: 10,
                    crossAxisSpacing: 10,
                    childAspectRatio: .9,
                    children: [
                      MetricCard(
                        label: 'Jogos',
                        value: intOf(_performance, ['jogos']),
                        icon: Icons.sports_soccer,
                      ),
                      MetricCard(
                        label: 'Vitórias',
                        value: intOf(_performance, ['vitorias']),
                        icon: Icons.emoji_events_outlined,
                      ),
                      MetricCard(
                        label: 'Empates',
                        value: intOf(_performance, ['empates']),
                        icon: Icons.balance,
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  SectionHeader(
                    title: 'Elenco',
                    subtitle:
                        '${asList(_team['atletas']).length} atleta(s) vinculados',
                    action: _canEdit
                        ? IconButton(
                            tooltip: 'Adicionar atleta ao time',
                            onPressed: _addAthlete,
                            icon: const Icon(Icons.person_add_alt_1),
                          )
                        : null,
                  ),
                  const SizedBox(height: 12),
                  if (asList(_team['atletas']).isEmpty)
                    const EmptyState(
                      title: 'Time sem atletas',
                      message: 'Adicione atletas da mesma escola ao elenco.',
                      icon: Icons.group_off_outlined,
                    )
                  else
                    ...asList(_team['atletas']).map(
                      (athlete) => Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: SurfaceCard(
                          child: Row(
                            children: [
                              CircleAvatar(
                                backgroundColor: Theme.of(
                                  context,
                                ).colorScheme.primaryContainer,
                                child: Text(
                                  textOf(athlete, [
                                    'numero_camisa',
                                  ], fallback: '?'),
                                  style: TextStyle(
                                    fontWeight: FontWeight.w900,
                                    color: Theme.of(
                                      context,
                                    ).colorScheme.onPrimaryContainer,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Flexible(
                                          child: Text(
                                            textOf(athlete, ['nome']),
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w800,
                                            ),
                                          ),
                                        ),
                                        if (boolOf(athlete, ['capitao'])) ...[
                                          const SizedBox(width: 6),
                                          Icon(
                                            Icons.star,
                                            size: 18,
                                            color: Theme.of(
                                              context,
                                            ).colorScheme.secondary,
                                          ),
                                        ],
                                      ],
                                    ),
                                    Text(
                                      '${textOf(athlete, ['posicao'])} • ${textOf(athlete, ['funcao'])}${boolOf(athlete, ['titular']) ? ' • Titular' : ''}',
                                      style: TextStyle(
                                        color: Theme.of(
                                          context,
                                        ).colorScheme.onSurfaceVariant,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              if (_canEdit)
                                PopupMenuButton<String>(
                                  onSelected: (value) {
                                    if (value == 'edit') _editAthlete(athlete);
                                    if (value == 'captain') {
                                      _defineCaptain(athlete);
                                    }
                                    if (value == 'remove') {
                                      _removeAthlete(athlete);
                                    }
                                  },
                                  itemBuilder: (_) => const [
                                    PopupMenuItem(
                                      value: 'edit',
                                      child: Text('Editar função'),
                                    ),
                                    PopupMenuItem(
                                      value: 'captain',
                                      child: Text('Definir capitão'),
                                    ),
                                    PopupMenuItem(
                                      value: 'remove',
                                      child: Text('Remover do time'),
                                    ),
                                  ],
                                ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  if (_canEdit && asList(_team['atletas']).isNotEmpty) ...[
                    const SizedBox(height: 8),
                    FilledButton.icon(
                      onPressed: _saveLineup,
                      icon: const Icon(Icons.format_list_numbered),
                      label: const Text('Definir escalação atual'),
                    ),
                  ],
                ],
              ),
            ),
    );
  }
}

class AthletePickerScreen extends StatefulWidget {
  const AthletePickerScreen({super.key});

  @override
  State<AthletePickerScreen> createState() => _AthletePickerScreenState();
}

class _AthletePickerScreenState extends State<AthletePickerScreen> {
  final _search = TextEditingController();
  bool _loading = true;
  List<Map<String, dynamic>> _items = [];
  Object? _error;

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
        ).client.get('/alunos', query: {'busca': _search.text, 'limite': 100}),
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
      appBar: AppBar(title: const Text('Selecionar atleta')),
      body: _loading
          ? const LoadingState()
          : _error != null
          ? ErrorState(error: _error!, onRetry: _load)
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                SearchBarField(
                  controller: _search,
                  onSubmitted: (_) => _load(),
                ),
                const SizedBox(height: 14),
                ..._items.map(
                  (item) => ListTile(
                    tileColor: Theme.of(context).colorScheme.surface,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    leading: const CircleAvatar(
                      child: Icon(Icons.person_outline),
                    ),
                    title: Text(textOf(item, ['nome'])),
                    subtitle: Text(
                      textOf(item, [
                        'matricula',
                      ], fallback: textOf(item, ['email'])),
                    ),
                    trailing: const Icon(Icons.add_circle_outline),
                    onTap: () => Navigator.pop(context, item),
                  ),
                ),
              ],
            ),
    );
  }
}

class TeamAthleteFormScreen extends StatefulWidget {
  const TeamAthleteFormScreen({
    required this.teamId,
    required this.athlete,
    this.editing = false,
    super.key,
  });
  final String teamId;
  final Map<String, dynamic> athlete;
  final bool editing;

  @override
  State<TeamAthleteFormScreen> createState() => _TeamAthleteFormScreenState();
}

class _TeamAthleteFormScreenState extends State<TeamAthleteFormScreen> {
  late final TextEditingController _number;
  late final TextEditingController _position;
  String _role = 'JOGADOR';
  bool _starter = false;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _number = TextEditingController(
      text: textOf(widget.athlete, ['numero_camisa'], fallback: ''),
    );
    _position = TextEditingController(
      text: textOf(widget.athlete, ['posicao'], fallback: ''),
    );
    _role = textOf(widget.athlete, ['funcao'], fallback: 'JOGADOR');
    _starter = boolOf(widget.athlete, ['titular']);
  }

  @override
  void dispose() {
    _number.dispose();
    _position.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    setState(() => _busy = true);
    final athleteId = textOf(widget.athlete, ['aluno_id', 'id']);
    final body = <String, Object?>{
      'numeroCamisa': int.tryParse(_number.text),
      'posicao': _position.text.trim(),
      'funcao': _role,
      'titular': _starter,
    };
    try {
      if (widget.editing) {
        await AppScope.read(context).client.patch(
          '/times/${widget.teamId}/atletas/$athleteId',
          body: body,
        );
      } else {
        await AppScope.read(
          context,
        ).client.post('/times/${widget.teamId}/atletas/$athleteId', body: body);
      }
      if (!mounted) return;
      showSuccess(
        context,
        widget.editing
            ? 'Dados do atleta atualizados.'
            : 'Atleta vinculado ao time.',
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
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.editing ? 'Editar função' : 'Adicionar ao time'),
      ),
      body: PageBody(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SurfaceCard(
              child: Row(
                children: [
                  const CircleAvatar(child: Icon(Icons.person_outline)),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      textOf(widget.athlete, ['nome']),
                      style: const TextStyle(fontWeight: FontWeight.w800),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),
            AppTextFormField(
              controller: _number,
              label: 'Número da camisa',
              keyboardType: TextInputType.number,
              prefixIcon: Icons.numbers,
            ),
            const SizedBox(height: 12),
            AppTextFormField(
              controller: _position,
              label: 'Posição',
              prefixIcon: Icons.place_outlined,
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: _role,
              decoration: const InputDecoration(
                labelText: 'Função',
                prefixIcon: Icon(Icons.assignment_ind_outlined),
              ),
              items: const [
                DropdownMenuItem(value: 'JOGADOR', child: Text('Jogador')),
                DropdownMenuItem(value: 'GOLEIRO', child: Text('Goleiro')),
                DropdownMenuItem(value: 'RESERVA', child: Text('Reserva')),
              ],
              onChanged: (value) => setState(() => _role = value ?? 'JOGADOR'),
            ),
            SwitchListTile.adaptive(
              value: _starter,
              onChanged: (value) => setState(() => _starter = value),
              title: const Text('Titular'),
              contentPadding: EdgeInsets.zero,
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: _busy ? null : _save,
              icon: const Icon(Icons.save_outlined),
              label: Text(_busy ? 'Salvando...' : 'Salvar'),
            ),
          ],
        ),
      ),
    );
  }
}
