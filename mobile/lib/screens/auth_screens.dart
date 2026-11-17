import 'package:flutter/material.dart';

import '../core/api_client.dart';
import '../core/app_scope.dart';
import '../core/data_helpers.dart';
import '../core/theme.dart';
import '../widgets/accessibility_panel.dart';
import '../widgets/common.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController(text: 'escola@ace.local');
  final _password = TextEditingController(text: 'Admin@123');
  final _emailFocus = FocusNode();
  final _passwordFocus = FocusNode();
  bool _obscure = true;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    _emailFocus.dispose();
    _passwordFocus.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    try {
      await AppScope.read(context).login(_email.text, _password.text);
    } catch (error) {
      if (mounted) showFailure(context, error);
    }
  }

  void _demo(String email) {
    setState(() {
      _email.text = email;
      _password.text = 'Admin@123';
    });
  }

  @override
  Widget build(BuildContext context) {
    final session = AppScope.of(context);
    return Scaffold(
      floatingActionButton: const AccessibilityButton(onSurface: true),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 460),
              child: FocusTraversalGroup(
                policy: OrderedTraversalPolicy(),
                child: Form(
                  key: _formKey,
                  child: Column(
                    children: [
                      Container(
                        width: 118,
                        height: 118,
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          color: Theme.of(context).colorScheme.surface,
                          borderRadius: BorderRadius.circular(32),
                          boxShadow: [
                            BoxShadow(
                              color: Theme.of(
                                context,
                              ).colorScheme.primary.withValues(alpha: .16),
                              blurRadius: 28,
                              offset: const Offset(0, 12),
                            ),
                          ],
                        ),
                        child: const AceLogo(),
                      ),
                      const SizedBox(height: 24),
                      Text(
                        'Bem-vindo ao ACE',
                        style: Theme.of(context).textTheme.headlineSmall
                            ?.copyWith(
                              fontWeight: FontWeight.w900,
                              color: Theme.of(context).colorScheme.primary,
                            ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Gestão esportiva escolar em um só lugar',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Theme.of(context).colorScheme.onSurfaceVariant,
                        ),
                      ),
                      const SizedBox(height: 28),
                      AppTextFormField(
                        controller: _email,
                        label: 'E-mail',
                        hint: 'Digite seu e-mail cadastrado',
                        required: true,
                        focusNode: _emailFocus,
                        textInputAction: TextInputAction.next,
                        onFieldSubmitted: (_) => _passwordFocus.requestFocus(),
                        keyboardType: TextInputType.emailAddress,
                        prefixIcon: Icons.alternate_email,
                        validator: (value) {
                          if (requiredField(value) != null) {
                            return 'Informe o e-mail';
                          }
                          if (!value!.contains('@')) return 'E-mail inválido';
                          return null;
                        },
                      ),
                      const SizedBox(height: 14),
                      AppTextFormField(
                        controller: _password,
                        label: 'Senha',
                        hint: 'Digite sua senha',
                        required: true,
                        focusNode: _passwordFocus,
                        obscureText: _obscure,
                        prefixIcon: Icons.lock_outline,
                        textInputAction: TextInputAction.done,
                        validator: requiredField,
                        suffixIcon: IconButton(
                          tooltip: _obscure ? 'Mostrar senha' : 'Ocultar senha',
                          onPressed: () => setState(() => _obscure = !_obscure),
                          icon: Icon(
                            _obscure
                                ? Icons.visibility_outlined
                                : Icons.visibility_off_outlined,
                          ),
                        ),
                      ),
                      Align(
                        alignment: Alignment.centerRight,
                        child: TextButton(
                          onPressed: () =>
                              pushPage(context, const RecoveryRequestScreen()),
                          child: const Text('Esqueceu a senha?'),
                        ),
                      ),
                      FilledButton.icon(
                        onPressed: session.busy ? null : _submit,
                        icon: session.busy
                            ? SizedBox.square(
                                dimension: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: AcePalette.of(context).onOrange,
                                ),
                              )
                            : const Icon(Icons.login),
                        label: Text(session.busy ? 'Entrando...' : 'Entrar'),
                      ),
                      const SizedBox(height: 24),
                      const Text(
                        'Acessos de demonstração',
                        style: TextStyle(fontWeight: FontWeight.w700),
                      ),
                      const SizedBox(height: 10),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        alignment: WrapAlignment.center,
                        children: [
                          ActionChip(
                            label: const Text('Escola'),
                            avatar: const Icon(Icons.school_outlined, size: 18),
                            onPressed: () => _demo('escola@ace.local'),
                          ),
                          ActionChip(
                            label: const Text('Atleta'),
                            avatar: const Icon(Icons.sports_outlined, size: 18),
                            onPressed: () => _demo('atleta@ace.local'),
                          ),
                          ActionChip(
                            label: const Text('Árbitro'),
                            avatar: const Icon(
                              Icons.sports_score_outlined,
                              size: 18,
                            ),
                            onPressed: () => _demo('arbitro@ace.local'),
                          ),
                          ActionChip(
                            label: const Text('Admin'),
                            avatar: const Icon(
                              Icons.admin_panel_settings_outlined,
                              size: 18,
                            ),
                            onPressed: () => _demo('admin@ace.local'),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class RecoveryRequestScreen extends StatefulWidget {
  const RecoveryRequestScreen({super.key});

  @override
  State<RecoveryRequestScreen> createState() => _RecoveryRequestScreenState();
}

class _RecoveryRequestScreenState extends State<RecoveryRequestScreen> {
  final _email = TextEditingController();
  bool _busy = false;

  @override
  void dispose() {
    _email.dispose();
    super.dispose();
  }

  Future<void> _send() async {
    if (!_email.text.contains('@')) {
      showFailure(context, const ApiFailure('Informe um e-mail válido.'));
      return;
    }
    setState(() => _busy = true);
    try {
      final response = asMap(
        await AppScope.read(context).client.post(
          '/auth/recuperacao/solicitar',
          body: {'email': _email.text.trim()},
        ),
      );
      if (!mounted) return;
      final code = response['codigoDesenvolvimento']?.toString();
      await pushPage(
        context,
        RecoveryConfirmScreen(email: _email.text.trim(), developmentCode: code),
      );
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
        title: const Text('Recuperar acesso'),
        actions: const [AccessibilityButton()],
      ),
      body: PageBody(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 18),
            const AceLogo(height: 74),
            const SizedBox(height: 24),
            const SectionHeader(
              title: 'Redefina sua senha',
              subtitle:
                  'Informe o e-mail cadastrado para receber um código de verificação.',
            ),
            const SizedBox(height: 24),
            AppTextFormField(
              controller: _email,
              label: 'E-mail',
              hint: 'Digite seu e-mail cadastrado',
              required: true,
              textInputAction: TextInputAction.done,
              keyboardType: TextInputType.emailAddress,
              prefixIcon: Icons.alternate_email,
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: _busy ? null : _send,
              icon: const Icon(Icons.mark_email_read_outlined),
              label: Text(_busy ? 'Enviando...' : 'Enviar código'),
            ),
          ],
        ),
      ),
    );
  }
}

class RecoveryConfirmScreen extends StatefulWidget {
  const RecoveryConfirmScreen({
    required this.email,
    this.developmentCode,
    super.key,
  });

  final String email;
  final String? developmentCode;

  @override
  State<RecoveryConfirmScreen> createState() => _RecoveryConfirmScreenState();
}

class _RecoveryConfirmScreenState extends State<RecoveryConfirmScreen> {
  late final TextEditingController _code;
  final _password = TextEditingController();
  final _confirm = TextEditingController();
  bool _busy = false;
  bool _obscure = true;

  @override
  void initState() {
    super.initState();
    _code = TextEditingController(text: widget.developmentCode ?? '');
  }

  @override
  void dispose() {
    _code.dispose();
    _password.dispose();
    _confirm.dispose();
    super.dispose();
  }

  Future<void> _confirmRecovery() async {
    if (_code.text.trim().length != 6) {
      showFailure(context, const ApiFailure('O código deve ter seis dígitos.'));
      return;
    }
    if (_password.text.length < 8) {
      showFailure(
        context,
        const ApiFailure('A nova senha deve ter ao menos 8 caracteres.'),
      );
      return;
    }
    if (_password.text != _confirm.text) {
      showFailure(context, const ApiFailure('As senhas não coincidem.'));
      return;
    }
    setState(() => _busy = true);
    try {
      await AppScope.read(context).client.post(
        '/auth/recuperacao/confirmar',
        body: {
          'email': widget.email,
          'codigo': _code.text.trim(),
          'novaSenha': _password.text,
        },
      );
      if (!mounted) return;
      showSuccess(context, 'Senha alterada com sucesso.');
      Navigator.of(context).popUntil((route) => route.isFirst);
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
        title: const Text('Confirmar recuperação'),
        actions: const [AccessibilityButton()],
      ),
      body: PageBody(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SectionHeader(title: 'Código e nova senha'),
            const SizedBox(height: 8),
            Text(
              'Código enviado para ${widget.email}',
              style: TextStyle(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
            ),
            if (widget.developmentCode != null) ...[
              const SizedBox(height: 12),
              SurfaceCard(
                color: Theme.of(context).colorScheme.primaryContainer,
                child: Text(
                  'Ambiente de desenvolvimento: código ${widget.developmentCode}',
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
              ),
            ],
            const SizedBox(height: 20),
            AppTextFormField(
              controller: _code,
              label: 'Código de 6 dígitos',
              keyboardType: TextInputType.number,
              prefixIcon: Icons.pin_outlined,
            ),
            const SizedBox(height: 14),
            AppTextFormField(
              controller: _password,
              label: 'Nova senha',
              obscureText: _obscure,
              prefixIcon: Icons.lock_reset,
              suffixIcon: IconButton(
                tooltip: _obscure ? 'Mostrar senha' : 'Ocultar senha',
                onPressed: () => setState(() => _obscure = !_obscure),
                icon: Icon(_obscure ? Icons.visibility : Icons.visibility_off),
              ),
            ),
            const SizedBox(height: 14),
            AppTextFormField(
              controller: _confirm,
              label: 'Confirme a senha',
              obscureText: _obscure,
              prefixIcon: Icons.lock_outline,
            ),
            const SizedBox(height: 20),
            FilledButton.icon(
              onPressed: _busy ? null : _confirmRecovery,
              icon: const Icon(Icons.check_circle_outline),
              label: Text(_busy ? 'Alterando...' : 'Alterar senha'),
            ),
          ],
        ),
      ),
    );
  }
}

class ApiSettingsScreen extends StatefulWidget {
  const ApiSettingsScreen({this.fromLogin = false, super.key});

  final bool fromLogin;

  @override
  State<ApiSettingsScreen> createState() => _ApiSettingsScreenState();
}

class _ApiSettingsScreenState extends State<ApiSettingsScreen> {
  TextEditingController? _url;
  bool _testing = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _url ??= TextEditingController(text: AppScope.read(context).client.baseUrl);
  }

  @override
  void dispose() {
    _url?.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    await AppScope.read(context).updateBaseUrl(_url!.text);
    if (!mounted) return;
    showSuccess(context, 'Endereço da API salvo.');
    setState(() => _url!.text = AppScope.read(context).client.baseUrl);
  }

  Future<void> _test() async {
    setState(() => _testing = true);
    try {
      final session = AppScope.read(context);
      await session.updateBaseUrl(_url!.text);
      final response = asMap(await session.client.get('/saude'));
      if (mounted) {
        showSuccess(
          context,
          'API conectada: ${textOf(response, ['servico'], fallback: 'ACE')}',
        );
      }
    } catch (error) {
      if (mounted) showFailure(context, error);
    } finally {
      if (mounted) setState(() => _testing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Configuração da API'),
        actions: const [AccessibilityButton()],
      ),
      body: PageBody(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SectionHeader(
              title: 'Endereço do servidor',
              subtitle:
                  'Use localhost no Windows/Chrome, 10.0.2.2 no emulador Android ou o IP do computador no celular físico.',
            ),
            const SizedBox(height: 20),
            AppTextFormField(
              controller: _url!,
              label: 'URL da API',
              prefixIcon: Icons.dns_outlined,
              keyboardType: TextInputType.url,
            ),
            const SizedBox(height: 12),
            SurfaceCard(
              color: Theme.of(context).colorScheme.primaryContainer,
              child: const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Exemplos',
                    style: TextStyle(fontWeight: FontWeight.w800),
                  ),
                  SizedBox(height: 8),
                  Text('Emulador: http://10.0.2.2:8080/api/mobile'),
                  Text('Computador: http://localhost:8080/api/mobile'),
                  Text('Celular: http://192.168.x.x:8080/api/mobile'),
                ],
              ),
            ),
            const SizedBox(height: 18),
            FilledButton.icon(
              onPressed: _testing ? null : _test,
              icon: const Icon(Icons.wifi_tethering),
              label: Text(_testing ? 'Testando...' : 'Salvar e testar conexão'),
            ),
            const SizedBox(height: 10),
            OutlinedButton.icon(
              onPressed: _save,
              icon: const Icon(Icons.save_outlined),
              label: const Text('Apenas salvar'),
            ),
          ],
        ),
      ),
    );
  }
}
