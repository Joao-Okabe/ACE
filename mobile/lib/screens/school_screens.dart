import 'package:flutter/material.dart';

import '../core/app_scope.dart';
import '../core/data_helpers.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

class SchoolProfileScreen extends StatefulWidget {
  const SchoolProfileScreen({super.key});

  @override
  State<SchoolProfileScreen> createState() => _SchoolProfileScreenState();
}

class _SchoolProfileScreenState extends State<SchoolProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _street = TextEditingController();
  final _number = TextEditingController();
  final _complement = TextEditingController();
  final _district = TextEditingController();
  final _city = TextEditingController();
  final _state = TextEditingController();
  final _zip = TextEditingController();

  bool _loading = true;
  bool _saving = false;
  Object? _error;
  Map<String, dynamic> _school = {};

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    for (final controller in [
      _name,
      _email,
      _phone,
      _street,
      _number,
      _complement,
      _district,
      _city,
      _state,
      _zip,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      _school = asMap(
        await AppScope.read(context).client.get('/perfil/escola'),
      );
      _fill(_school);
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _fill(Map<String, dynamic> school) {
    _name.text = textOf(school, ['nome'], fallback: '');
    _email.text = textOf(school, ['email'], fallback: '');
    _phone.text = textOf(school, ['telefone'], fallback: '');
    _street.text = textOf(school, ['logradouro'], fallback: '');
    _number.text = textOf(school, ['numero'], fallback: '');
    _complement.text = textOf(school, ['complemento'], fallback: '');
    _district.text = textOf(school, ['bairro'], fallback: '');
    _city.text = textOf(school, ['cidade'], fallback: '');
    _state.text = textOf(school, ['estado'], fallback: '');
    _zip.text = textOf(school, ['cep'], fallback: '');
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);
    try {
      _school = asMap(
        await AppScope.read(context).client.put(
          '/perfil/escola',
          body: {
            'nome': _name.text.trim(),
            'email': _email.text.trim(),
            'telefone': _phone.text.trim(),
            'logradouro': _street.text.trim(),
            'numero': _number.text.trim(),
            'complemento': _complement.text.trim(),
            'bairro': _district.text.trim(),
            'cidade': _city.text.trim(),
            'estado': _state.text.trim().toUpperCase(),
            'cep': _zip.text.trim(),
          },
        ),
      );
      _fill(_school);
      if (mounted) showSuccess(context, 'Perfil da escola atualizado.');
    } catch (error) {
      if (mounted) showFailure(context, error);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Perfil da escola')),
      body: _loading
          ? const LoadingState(message: 'Carregando a escola...')
          : _error != null
          ? ErrorState(error: _error!, onRetry: _load)
          : RefreshIndicator(
              onRefresh: _load,
              child: PageBody(
                scrollable: true,
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _SchoolHeader(data: _school),
                      const SizedBox(height: 22),
                      const SectionHeader(
                        title: 'Dados institucionais',
                        subtitle:
                            'Informações exibidas no aplicativo e nas competições.',
                      ),
                      const SizedBox(height: 16),
                      AppTextFormField(
                        controller: _name,
                        label: 'Nome da escola',
                        prefixIcon: Icons.school_outlined,
                        required: true,
                        validator: requiredField,
                      ),
                      const SizedBox(height: 12),
                      AppTextFormField(
                        controller: _email,
                        label: 'E-mail',
                        prefixIcon: Icons.mail_outline,
                        keyboardType: TextInputType.emailAddress,
                        required: true,
                        validator: emailField,
                      ),
                      const SizedBox(height: 12),
                      AppTextFormField(
                        controller: _phone,
                        label: 'Telefone',
                        prefixIcon: Icons.phone_outlined,
                        keyboardType: TextInputType.phone,
                      ),
                      const SizedBox(height: 22),
                      const SectionHeader(title: 'Endereço'),
                      const SizedBox(height: 16),
                      AppTextFormField(
                        controller: _street,
                        label: 'Logradouro',
                        prefixIcon: Icons.location_on_outlined,
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(
                            child: AppTextFormField(
                              controller: _number,
                              label: 'Número',
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: AppTextFormField(
                              controller: _complement,
                              label: 'Complemento',
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      AppTextFormField(controller: _district, label: 'Bairro'),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(
                            flex: 3,
                            child: AppTextFormField(
                              controller: _city,
                              label: 'Cidade',
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: AppTextFormField(
                              controller: _state,
                              label: 'UF',
                              maxLength: 2,
                              textCapitalization: TextCapitalization.characters,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      AppTextFormField(
                        controller: _zip,
                        label: 'CEP',
                        keyboardType: TextInputType.number,
                        prefixIcon: Icons.markunread_mailbox_outlined,
                      ),
                      const SizedBox(height: 22),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton.icon(
                          onPressed: _saving ? null : _save,
                          icon: const Icon(Icons.save_outlined),
                          label: Text(
                            _saving ? 'Salvando...' : 'Salvar alterações',
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
    );
  }
}

class SchoolListScreen extends StatefulWidget {
  const SchoolListScreen({super.key});

  @override
  State<SchoolListScreen> createState() => _SchoolListScreenState();
}

class _SchoolListScreenState extends State<SchoolListScreen> {
  final _search = TextEditingController();
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _schools = [];

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

  Future<void> _load([String? search]) async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final query = Uri(
        queryParameters: {
          if ((search ?? _search.text).trim().isNotEmpty)
            'busca': (search ?? _search.text).trim(),
        },
      ).query;
      final raw = await AppScope.read(
        context,
      ).client.get('/escolas${query.isEmpty ? '' : '?$query'}');
      _schools = asList(raw, key: 'itens');
      if (_schools.isEmpty) _schools = asList(raw, key: 'dados');
      if (_schools.isEmpty && raw is List) _schools = raw.map(asMap).toList();
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Escolas')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: SearchBarField(
              controller: _search,
              hint: 'Buscar por nome, cidade ou INEP',
              onSubmitted: _load,
            ),
          ),
          Expanded(
            child: _loading
                ? const LoadingState(message: 'Carregando escolas...')
                : _error != null
                ? ErrorState(error: _error!, onRetry: _load)
                : RefreshIndicator(
                    onRefresh: _load,
                    child: _schools.isEmpty
                        ? ListView(
                            children: const [
                              EmptyState(
                                title: 'Nenhuma escola encontrada',
                                icon: Icons.school_outlined,
                              ),
                            ],
                          )
                        : ListView.separated(
                            padding: const EdgeInsets.fromLTRB(16, 8, 16, 28),
                            itemCount: _schools.length,
                            separatorBuilder: (_, _) =>
                                const SizedBox(height: 10),
                            itemBuilder: (context, index) {
                              final school = _schools[index];
                              return SurfaceCard(
                                onTap: () => pushPage(
                                  context,
                                  SchoolDetailScreen(
                                    schoolId: textOf(school, ['id']),
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    CircleAvatar(
                                      radius: 26,
                                      backgroundColor: Theme.of(
                                        context,
                                      ).colorScheme.primaryContainer,
                                      child: Icon(
                                        Icons.school_outlined,
                                        color: Theme.of(
                                          context,
                                        ).colorScheme.onPrimaryContainer,
                                      ),
                                    ),
                                    const SizedBox(width: 14),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            textOf(school, ['nome']),
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w800,
                                            ),
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            '${textOf(school, ['cidade'])} • ${textOf(school, ['estado'])}',
                                            style: TextStyle(
                                              color: Theme.of(
                                                context,
                                              ).colorScheme.onSurfaceVariant,
                                            ),
                                          ),
                                          const SizedBox(height: 7),
                                          Wrap(
                                            spacing: 8,
                                            runSpacing: 5,
                                            children: [
                                              _MiniCount(
                                                icon: Icons.directions_run,
                                                label:
                                                    '${intOf(school, ['atletas'])} atletas',
                                              ),
                                              _MiniCount(
                                                icon: Icons.groups_2_outlined,
                                                label:
                                                    '${intOf(school, ['times'])} times',
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),
                                    const Icon(Icons.chevron_right),
                                  ],
                                ),
                              );
                            },
                          ),
                  ),
          ),
        ],
      ),
    );
  }
}

class SchoolDetailScreen extends StatefulWidget {
  const SchoolDetailScreen({required this.schoolId, super.key});

  final String schoolId;

  @override
  State<SchoolDetailScreen> createState() => _SchoolDetailScreenState();
}

class _SchoolDetailScreenState extends State<SchoolDetailScreen> {
  bool _loading = true;
  Object? _error;
  Map<String, dynamic> _school = {};

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
      _school = asMap(
        await AppScope.read(context).client.get('/escolas/${widget.schoolId}'),
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
      appBar: AppBar(title: const Text('Detalhes da escola')),
      body: _loading
          ? const LoadingState(message: 'Carregando escola...')
          : _error != null
          ? ErrorState(error: _error!, onRetry: _load)
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
                children: [
                  _SchoolHeader(data: _school),
                  const SizedBox(height: 18),
                  SurfaceCard(
                    child: Column(
                      children: [
                        KeyValueRow(
                          label: 'CNPJ',
                          value: textOf(_school, ['cnpj']),
                        ),
                        KeyValueRow(
                          label: 'Código INEP',
                          value: textOf(_school, ['codigo_inep', 'codigoInep']),
                        ),
                        KeyValueRow(
                          label: 'E-mail',
                          value: textOf(_school, ['email']),
                          icon: Icons.mail_outline,
                        ),
                        KeyValueRow(
                          label: 'Telefone',
                          value: textOf(_school, ['telefone']),
                          icon: Icons.phone_outlined,
                        ),
                        KeyValueRow(
                          label: 'Endereço',
                          value: _address(_school),
                          icon: Icons.location_on_outlined,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                  Row(
                    children: [
                      Expanded(
                        child: MetricCard(
                          label: 'Atletas',
                          value: intOf(_school, ['atletas']),
                          icon: Icons.directions_run,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: MetricCard(
                          label: 'Times',
                          value: intOf(_school, ['times']),
                          icon: Icons.groups_2_outlined,
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

class _SchoolHeader extends StatelessWidget {
  const _SchoolHeader({required this.data});

  final Map<String, dynamic> data;

  @override
  Widget build(BuildContext context) {
    final palette = AcePalette.of(context);
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(colors: [palette.navy, palette.blue]),
        borderRadius: BorderRadius.circular(24),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 34,
            backgroundColor: Colors.white,
            child: Icon(Icons.school, size: 36, color: palette.navy),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  textOf(data, ['nome']),
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 5),
                Text(
                  '${textOf(data, ['cidade'])} • ${textOf(data, ['estado'])}',
                  style: const TextStyle(color: Colors.white70),
                ),
              ],
            ),
          ),
          StatusBadge(
            boolOf(data, ['ativa'], fallback: true) ? 'ATIVA' : 'INATIVA',
          ),
        ],
      ),
    );
  }
}

class _MiniCount extends StatelessWidget {
  const _MiniCount({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 15, color: Theme.of(context).colorScheme.primary),
        const SizedBox(width: 4),
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            color: Theme.of(context).colorScheme.onSurfaceVariant,
          ),
        ),
      ],
    );
  }
}

String _address(Map<String, dynamic> school) {
  final parts = <String>[
    textOf(school, ['logradouro'], fallback: ''),
    textOf(school, ['numero'], fallback: ''),
    textOf(school, ['bairro'], fallback: ''),
    textOf(school, ['cidade'], fallback: ''),
    textOf(school, ['estado'], fallback: ''),
    textOf(school, ['cep'], fallback: ''),
  ].where((part) => part.trim().isNotEmpty).toList();
  return parts.isEmpty ? 'Não informado' : parts.join(', ');
}
