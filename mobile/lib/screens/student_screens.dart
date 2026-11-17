import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';

import '../core/app_scope.dart';
import '../core/data_helpers.dart';
import '../core/theme.dart';
import '../widgets/common.dart';

MemoryImage? _athletePhoto(Map<String, dynamic> athlete) {
  final encoded = athlete['foto_base64']?.toString();
  if (encoded == null || encoded.isEmpty) return null;
  try {
    return MemoryImage(base64Decode(encoded));
  } on FormatException {
    return null;
  }
}

class StudentListScreen extends StatefulWidget {
  const StudentListScreen({this.embedded = false, super.key});

  final bool embedded;

  @override
  State<StudentListScreen> createState() => _StudentListScreenState();
}

class _StudentListScreenState extends State<StudentListScreen> {
  final _search = TextEditingController();
  bool _loading = true;
  Object? _error;
  List<Map<String, dynamic>> _items = [];

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
      final response = await AppScope.read(context).client.get(
        '/alunos',
        query: {'busca': _search.text, 'pagina': 1, 'limite': 100},
      );
      _items = asList(response);
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _openForm([Map<String, dynamic>? student]) async {
    final changed = await pushPage<bool>(
      context,
      StudentFormScreen(
        studentId: student?['id']?.toString(),
        initialData: student,
      ),
    );
    if (changed == true) _load();
  }

  Widget _content() {
    if (_loading) return const LoadingState(message: 'Carregando atletas...');
    if (_error != null) return ErrorState(error: _error!, onRetry: _load);
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
        children: [
          SearchBarField(
            controller: _search,
            onSubmitted: (_) => _load(),
            hint: 'Buscar por nome, e-mail ou CPF',
          ),
          const SizedBox(height: 16),
          if (_items.isEmpty)
            EmptyState(
              title: 'Nenhum atleta encontrado',
              message: 'Cadastre o primeiro atleta da escola.',
              icon: Icons.directions_run,
              action: FilledButton.icon(
                onPressed: _openForm,
                icon: const Icon(Icons.add),
                label: const Text('Cadastrar atleta'),
              ),
            )
          else
            ..._items.map((student) {
              final photo = _athletePhoto(student);
              return Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: SurfaceCard(
                  onTap: () => pushPage(
                    context,
                    StudentDetailScreen(studentId: textOf(student, ['id'])),
                  ),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 25,
                        backgroundColor: Theme.of(
                          context,
                        ).colorScheme.primaryContainer,
                        backgroundImage: photo,
                        child: photo == null
                            ? Text(
                                textOf(student, [
                                  'nome',
                                ], fallback: 'A').substring(0, 1).toUpperCase(),
                                style: TextStyle(
                                  fontWeight: FontWeight.w900,
                                  color: Theme.of(
                                    context,
                                  ).colorScheme.onPrimaryContainer,
                                ),
                              )
                            : null,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              textOf(student, ['nome']),
                              style: const TextStyle(
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Text(
                              textOf(student, [
                                'escola_nome',
                                'escolaNome',
                              ], fallback: textOf(student, ['email'])),
                              style: TextStyle(
                                color: Theme.of(
                                  context,
                                ).colorScheme.onSurfaceVariant,
                              ),
                            ),
                            const SizedBox(height: 7),
                            Wrap(
                              spacing: 8,
                              runSpacing: 4,
                              children: [
                                if (textOf(student, [
                                  'matricula',
                                ], fallback: '').isNotEmpty)
                                  Text(
                                    'Matrícula ${textOf(student, ['matricula'])}',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Theme.of(
                                        context,
                                      ).colorScheme.onSurfaceVariant,
                                    ),
                                  ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        tooltip: 'Editar atleta',
                        onPressed: () => _openForm(student),
                        icon: const Icon(Icons.edit_outlined),
                      ),
                    ],
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final body = _content();
    if (widget.embedded) return body;
    return Scaffold(
      appBar: AppBar(title: const Text('Alunos e atletas')),
      body: body,
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openForm,
        icon: const Icon(Icons.person_add_alt_1),
        label: const Text('Cadastrar'),
      ),
    );
  }
}

class StudentFormScreen extends StatefulWidget {
  const StudentFormScreen({this.studentId, this.initialData, super.key});

  final String? studentId;
  final Map<String, dynamic>? initialData;

  bool get editing => studentId != null;

  @override
  State<StudentFormScreen> createState() => _StudentFormScreenState();
}

class _StudentFormScreenState extends State<StudentFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _cpf = TextEditingController();
  final _birth = TextEditingController();
  final _registration = TextEditingController();
  final _password = TextEditingController();
  final _schoolId = TextEditingController();
  final _schoolCode = TextEditingController();
  final _schoolName = TextEditingController();
  bool _lookingUpSchool = false;
  String? _gender;
  final _imagePicker = ImagePicker();
  Uint8List? _photoBytes;
  String? _photoMimeType;
  bool _busy = false;
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _fill(widget.initialData ?? {});
    if (widget.editing &&
        (widget.initialData == null || widget.initialData!.length < 4)) {
      _loadDetail();
    }
  }

  void _fill(Map<String, dynamic> data) {
    _name.text = textOf(data, ['nome'], fallback: '');
    _email.text = textOf(data, ['email'], fallback: '');
    _phone.text = textOf(data, ['telefone'], fallback: '');
    _cpf.text = textOf(data, ['cpf'], fallback: '');
    _birth.text = data['data_nascimento'] == null
        ? ''
        : formatDate(data['data_nascimento']);
    _registration.text = textOf(data, ['matricula'], fallback: '');
    _schoolId.text = textOf(data, [
      'escola_id',
      'escolaId',
    ], fallback: AppScope.read(context).schoolId ?? '');
    _schoolCode.text = textOf(data, ['escola_codigo'], fallback: '');
    _schoolName.text = textOf(data, ['escola_nome'], fallback: '');
    _gender = data['genero']?.toString();
    final photo = data['foto_base64']?.toString();
    if (photo != null && photo.isNotEmpty) {
      _photoBytes = base64Decode(photo);
      _photoMimeType = data['foto_mime_type']?.toString();
    }
  }

  Future<void> _lookupSchool(String value) async {
    final code = digitsOnly(value);
    if (code != value) {
      _schoolCode.value = TextEditingValue(
        text: code,
        selection: TextSelection.collapsed(offset: code.length),
      );
    }
    setState(() {
      _schoolId.clear();
      _schoolName.clear();
      _lookingUpSchool = code.length == 3;
    });
    if (code.length != 3) return;
    try {
      final school = asMap(
        await AppScope.read(context).client.get('/escolas/codigo/$code'),
      );
      if (!mounted || _schoolCode.text != code) return;
      setState(() {
        _schoolId.text = textOf(school, ['id'], fallback: '');
        _schoolName.text = textOf(school, ['nome'], fallback: '');
      });
    } catch (_) {
      if (!mounted || _schoolCode.text != code) return;
      setState(() => _schoolName.text = 'Código não encontrado');
    } finally {
      if (mounted && _schoolCode.text == code) {
        setState(() => _lookingUpSchool = false);
      }
    }
  }

  Future<void> _pickPhoto(ImageSource source) async {
    try {
      final image = await _imagePicker.pickImage(
        source: source,
        maxWidth: 1200,
        maxHeight: 1200,
        imageQuality: 82,
        preferredCameraDevice: CameraDevice.front,
      );
      if (image == null) return;
      final bytes = await image.readAsBytes();
      if (bytes.length > 4 * 1024 * 1024) {
        if (mounted) showFailure(context, 'A foto deve ter no máximo 4 MB.');
        return;
      }
      final extension = image.name.split('.').last.toLowerCase();
      setState(() {
        _photoBytes = bytes;
        _photoMimeType = extension == 'png' ? 'image/png' : 'image/jpeg';
      });
    } catch (error) {
      if (mounted) showFailure(context, error);
    }
  }

  Future<void> _showPhotoOptions() async {
    await showModalBottomSheet<void>(
      context: context,
      showDragHandle: true,
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_library_outlined),
              title: const Text('Escolher da galeria'),
              onTap: () {
                Navigator.pop(context);
                _pickPhoto(ImageSource.gallery);
              },
            ),
            ListTile(
              leading: const Icon(Icons.photo_camera_outlined),
              title: const Text('Tirar foto agora'),
              onTap: () {
                Navigator.pop(context);
                _pickPhoto(ImageSource.camera);
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _loadDetail() async {
    setState(() => _loading = true);
    try {
      final data = asMap(
        await AppScope.read(context).client.get('/alunos/${widget.studentId}'),
      );
      if (mounted) setState(() => _fill(data));
    } catch (error) {
      if (mounted) showFailure(context, error);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  void dispose() {
    for (final controller in [
      _name,
      _email,
      _phone,
      _cpf,
      _birth,
      _registration,
      _password,
      _schoolId,
      _schoolCode,
      _schoolName,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _pickBirthDate() async {
    final initial =
        DateTime.tryParse(_birth.text.split('/').reversed.join('-')) ??
        DateTime(2009, 1, 1);
    final selected = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(1990),
      lastDate: DateTime.now(),
      locale: const Locale('pt', 'BR'),
    );
    if (selected != null) {
      setState(
        () => _birth.text =
            '${selected.day.toString().padLeft(2, '0')}/${selected.month.toString().padLeft(2, '0')}/${selected.year}',
      );
    }
  }

  String? _isoBirth() {
    final parts = _birth.text.split('/');
    if (parts.length != 3) return null;
    return '${parts[2]}-${parts[1].padLeft(2, '0')}-${parts[0].padLeft(2, '0')}';
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    final session = AppScope.read(context);
    final body = <String, Object?>{
      'nome': _name.text.trim(),
      'email': _email.text.trim(),
      'telefone': _phone.text.trim(),
      'cpf': digitsOnly(_cpf.text),
      'dataNascimento': _isoBirth(),
      'matricula': _registration.text.trim(),
      'genero': _gender,
      if (_photoBytes != null) 'fotoBase64': base64Encode(_photoBytes!),
      if (_photoBytes != null) 'fotoMimeType': _photoMimeType,
      if (session.profile != 'ESCOLA') 'escolaId': _schoolId.text.trim(),
      if (!widget.editing && _password.text.isNotEmpty) 'senha': _password.text,
    };
    setState(() => _busy = true);
    try {
      final response = widget.editing
          ? await session.client.put('/alunos/${widget.studentId}', body: body)
          : await session.client.post('/alunos', body: body);
      if (!mounted) return;
      final data = asMap(response);
      final tempPassword = data['senhaTemporaria']?.toString();
      if (tempPassword != null) {
        await showDialog<void>(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Atleta cadastrado'),
            content: SelectableText(
              'Senha temporária: $tempPassword\n\nAnote e entregue ao atleta.',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Entendi'),
              ),
            ],
          ),
        );
      } else {
        showSuccess(
          context,
          widget.editing ? 'Atleta atualizado.' : 'Atleta cadastrado.',
        );
      }
      if (mounted) Navigator.pop(context, true);
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
        title: Text(widget.editing ? 'Editar atleta' : 'Cadastrar atleta'),
      ),
      body: _loading
          ? const LoadingState()
          : Form(
              key: _formKey,
              child: PageBody(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SectionHeader(
                      title: 'Dados pessoais',
                      subtitle: 'Preencha as informações do aluno/atleta.',
                    ),
                    const SizedBox(height: 18),
                    Center(
                      child: Column(
                        children: [
                          Semantics(
                            button: true,
                            enabled: !_busy,
                            label: _photoBytes == null
                                ? 'Adicionar foto do atleta'
                                : 'Alterar foto do atleta',
                            hint: 'Escolha uma imagem ou use a câmera',
                            child: InkWell(
                              onTap: _busy ? null : _showPhotoOptions,
                              borderRadius: BorderRadius.circular(64),
                              child: CircleAvatar(
                                radius: 58,
                                backgroundColor: Theme.of(
                                  context,
                                ).colorScheme.primaryContainer,
                                backgroundImage: _photoBytes == null
                                    ? null
                                    : MemoryImage(_photoBytes!),
                                child: _photoBytes == null
                                    ? Icon(
                                        Icons.add_a_photo_outlined,
                                        size: 38,
                                        color: Theme.of(
                                          context,
                                        ).colorScheme.onPrimaryContainer,
                                      )
                                    : null,
                              ),
                            ),
                          ),
                          const SizedBox(height: 8),
                          TextButton.icon(
                            onPressed: _busy ? null : _showPhotoOptions,
                            icon: const Icon(Icons.camera_alt_outlined),
                            label: Text(
                              _photoBytes == null
                                  ? 'Adicionar foto do atleta'
                                  : 'Alterar foto',
                            ),
                          ),
                          Text(
                            'Escolha da galeria ou tire uma foto na hora',
                            style: TextStyle(
                              fontSize: 12,
                              color: Theme.of(
                                context,
                              ).colorScheme.onSurfaceVariant,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),
                    AppTextFormField(
                      controller: _name,
                      label: 'Nome completo',
                      prefixIcon: Icons.person_outline,
                      required: true,
                      validator: requiredField,
                    ),
                    const SizedBox(height: 12),
                    AppTextFormField(
                      controller: _email,
                      label: 'E-mail',
                      keyboardType: TextInputType.emailAddress,
                      prefixIcon: Icons.alternate_email,
                      required: true,
                      validator: (value) => value?.contains('@') == true
                          ? null
                          : 'E-mail inválido',
                    ),
                    const SizedBox(height: 12),
                    AppTextFormField(
                      controller: _phone,
                      label: 'Telefone',
                      keyboardType: TextInputType.phone,
                      prefixIcon: Icons.phone_outlined,
                    ),
                    const SizedBox(height: 12),
                    AppTextFormField(
                      controller: _cpf,
                      label: 'CPF',
                      keyboardType: TextInputType.number,
                      prefixIcon: Icons.badge_outlined,
                      required: true,
                      validator: (value) => digitsOnly(value ?? '').length == 11
                          ? null
                          : 'Informe 11 dígitos',
                    ),
                    const SizedBox(height: 12),
                    AppTextFormField(
                      controller: _birth,
                      label: 'Data de nascimento',
                      readOnly: true,
                      onTap: _pickBirthDate,
                      prefixIcon: Icons.cake_outlined,
                      required: true,
                      validator: requiredField,
                      suffixIcon: const Icon(Icons.calendar_month),
                    ),
                    const SizedBox(height: 12),
                    AppTextFormField(
                      controller: _registration,
                      label: 'Matrícula',
                      prefixIcon: Icons.numbers,
                    ),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      initialValue: _gender,
                      decoration: const InputDecoration(
                        labelText: 'Gênero',
                        prefixIcon: Icon(Icons.wc_outlined),
                      ),
                      items: const [
                        DropdownMenuItem(
                          value: 'MASCULINO',
                          child: Text('Masculino'),
                        ),
                        DropdownMenuItem(
                          value: 'FEMININO',
                          child: Text('Feminino'),
                        ),
                        DropdownMenuItem(
                          value: 'OUTRO',
                          child: Text('Outro / não informar'),
                        ),
                      ],
                      onChanged: (value) => setState(() => _gender = value),
                    ),
                    if (session.profile != 'ESCOLA') ...[
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _schoolCode,
                        keyboardType: TextInputType.number,
                        maxLength: 3,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                        ],
                        onChanged: _lookupSchool,
                        validator: (value) {
                          if (value == null ||
                              !RegExp(r'^\d{3}$').hasMatch(value)) {
                            return 'Informe os 3 dígitos do código da escola';
                          }
                          if (_schoolId.text.isEmpty) {
                            return 'Código de escola não encontrado';
                          }
                          return null;
                        },
                        decoration: InputDecoration(
                          labelText: 'Código da escola *',
                          helperText: 'Campo obrigatório',
                          hintText: 'Ex.: 023',
                          prefixIcon: const Icon(Icons.school_outlined),
                          suffixIcon: _lookingUpSchool
                              ? const Padding(
                                  padding: EdgeInsets.all(12),
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                  ),
                                )
                              : _schoolId.text.isNotEmpty
                              ? Icon(
                                  Icons.check_circle,
                                  color: AcePalette.of(context).success,
                                )
                              : null,
                        ),
                      ),
                      const SizedBox(height: 12),
                      AppTextFormField(
                        controller: _schoolName,
                        label: 'Nome da escola',
                        prefixIcon: Icons.apartment_outlined,
                        readOnly: true,
                      ),
                    ],
                    if (!widget.editing) ...[
                      const SizedBox(height: 24),
                      const SectionHeader(
                        title: 'Acesso ao aplicativo',
                        subtitle:
                            'A senha é opcional. Se deixada vazia, o sistema gera uma senha temporária.',
                      ),
                      const SizedBox(height: 14),
                      AppTextFormField(
                        controller: _password,
                        label: 'Senha inicial (opcional)',
                        obscureText: true,
                        prefixIcon: Icons.lock_outline,
                      ),
                    ],
                    const SizedBox(height: 24),
                    FilledButton.icon(
                      onPressed: _busy ? null : _save,
                      icon: const Icon(Icons.save_outlined),
                      label: Text(_busy ? 'Salvando...' : 'Salvar atleta'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}

class StudentDetailScreen extends StatefulWidget {
  const StudentDetailScreen({required this.studentId, super.key});
  final String studentId;

  @override
  State<StudentDetailScreen> createState() => _StudentDetailScreenState();
}

class _StudentDetailScreenState extends State<StudentDetailScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabs;
  bool _loading = true;
  Object? _error;
  Map<String, dynamic> _student = {};
  Map<String, dynamic> _stats = {};
  List<Map<String, dynamic>> _history = [];

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
        AppScope.read(context).client.get('/alunos/${widget.studentId}'),
        AppScope.read(
          context,
        ).client.get('/alunos/${widget.studentId}/estatisticas'),
        AppScope.read(
          context,
        ).client.get('/alunos/${widget.studentId}/historico'),
      ]);
      _student = asMap(results[0]);
      _stats = asMap(results[1]);
      _history = asList(results[2]);
    } catch (error) {
      _error = error;
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Perfil do atleta'),
        actions: [
          if (AppScope.of(context).hasAnyProfile({'ESCOLA', 'ADMINISTRADOR'}) &&
              _student.isNotEmpty)
            IconButton(
              tooltip: 'Editar perfil do atleta',
              onPressed: () async {
                final changed = await pushPage<bool>(
                  context,
                  StudentFormScreen(
                    studentId: widget.studentId,
                    initialData: _student,
                  ),
                );
                if (changed == true) _load();
              },
              icon: const Icon(Icons.edit_outlined),
            ),
        ],
        bottom: TabBar(
          controller: _tabs,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          tabs: const [
            Tab(text: 'Perfil'),
            Tab(text: 'Estatísticas'),
            Tab(text: 'Histórico'),
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
                _ProfileTab(student: _student),
                _StatisticsTab(stats: _stats),
                _HistoryTab(items: _history),
              ],
            ),
    );
  }
}

class _ProfileTab extends StatelessWidget {
  const _ProfileTab({required this.student});
  final Map<String, dynamic> student;

  @override
  Widget build(BuildContext context) {
    final photo = _athletePhoto(student);
    return PageBody(
      child: Column(
        children: [
          CircleAvatar(
            radius: 48,
            backgroundColor: Theme.of(context).colorScheme.primaryContainer,
            backgroundImage: photo,
            child: photo == null
                ? Text(
                    textOf(student, ['nome'], fallback: 'A').substring(0, 1),
                    style: TextStyle(
                      fontSize: 34,
                      fontWeight: FontWeight.w900,
                      color: Theme.of(context).colorScheme.onPrimaryContainer,
                    ),
                  )
                : null,
          ),
          const SizedBox(height: 14),
          Text(
            textOf(student, ['nome']),
            textAlign: TextAlign.center,
            style: Theme.of(
              context,
            ).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 6),
          Text(
            textOf(student, ['escola_nome']),
            style: TextStyle(
              color: Theme.of(context).colorScheme.onSurfaceVariant,
            ),
          ),
          const SizedBox(height: 22),
          SurfaceCard(
            child: Column(
              children: [
                KeyValueRow(
                  label: 'E-mail',
                  value: textOf(student, ['email']),
                  icon: Icons.alternate_email,
                ),
                KeyValueRow(
                  label: 'Telefone',
                  value: textOf(student, ['telefone']),
                  icon: Icons.phone_outlined,
                ),
                KeyValueRow(
                  label: 'CPF',
                  value: textOf(student, ['cpf']),
                  icon: Icons.badge_outlined,
                ),
                KeyValueRow(
                  label: 'Nascimento',
                  value: formatDate(student['data_nascimento']),
                  icon: Icons.cake_outlined,
                ),
                KeyValueRow(
                  label: 'Matrícula',
                  value: textOf(student, ['matricula']),
                  icon: Icons.numbers,
                ),
                KeyValueRow(
                  label: 'Gênero',
                  value: statusLabel(student['genero']),
                  icon: Icons.wc_outlined,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _StatisticsTab extends StatelessWidget {
  const _StatisticsTab({required this.stats});
  final Map<String, dynamic> stats;

  @override
  Widget build(BuildContext context) {
    final metrics = [
      ('Partidas', intOf(stats, ['partidas']), Icons.sports_soccer),
      ('Pontos', intOf(stats, ['pontos']), Icons.add_circle_outline),
      (
        'Assistências',
        intOf(stats, ['assistencias']),
        Icons.assistant_direction,
      ),
      ('Faltas', intOf(stats, ['faltas']), Icons.warning_amber),
      ('Amarelos', intOf(stats, ['cartoes_amarelos']), Icons.style_outlined),
      (
        'Vermelhos',
        intOf(stats, ['cartoes_vermelhos']),
        Icons.rectangle_outlined,
      ),
    ];
    return PageBody(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SectionHeader(
            title: 'Desempenho do atleta',
            subtitle: 'Dados acumulados nas partidas registradas.',
          ),
          const SizedBox(height: 16),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 1.3,
            ),
            itemCount: metrics.length,
            itemBuilder: (_, index) => MetricCard(
              label: metrics[index].$1,
              value: metrics[index].$2,
              icon: metrics[index].$3,
            ),
          ),
          const SizedBox(height: 16),
          SurfaceCard(
            color: AcePalette.of(context).navy,
            child: Row(
              children: [
                Icon(
                  Icons.star_rounded,
                  size: 48,
                  color: AcePalette.of(context).orange,
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Média de avaliação',
                        style: TextStyle(color: Colors.white70),
                      ),
                      Text(
                        doubleOf(stats, ['media_nota']).toStringAsFixed(2),
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 30,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _HistoryTab extends StatelessWidget {
  const _HistoryTab({required this.items});
  final List<Map<String, dynamic>> items;

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const EmptyState(
        title: 'Sem histórico esportivo',
        message: 'As participações aparecerão após o registro das partidas.',
        icon: Icons.history,
      );
    }
    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: items.length,
      separatorBuilder: (_, _) => const SizedBox(height: 10),
      itemBuilder: (_, index) {
        final item = items[index];
        return SurfaceCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      textOf(item, ['competicao']),
                      style: const TextStyle(fontWeight: FontWeight.w800),
                    ),
                  ),
                  StatusBadge(item['status']),
                ],
              ),
              const SizedBox(height: 10),
              Text(
                '${textOf(item, ['time_casa'])} ${intOf(item, ['placar_casa'])} × ${intOf(item, ['placar_visitante'])} ${textOf(item, ['time_visitante'])}',
                style: const TextStyle(fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 8),
              Text(
                formatDate(item['data_hora'], withTime: true),
                style: TextStyle(
                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                ),
              ),
              const Divider(height: 24),
              Wrap(
                spacing: 16,
                children: [
                  Text('${intOf(item, ['pontos_do_atleta'])} pontos'),
                  Text('${intOf(item, ['assistencias'])} assistências'),
                  Text('${intOf(item, ['faltas'])} faltas'),
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}
