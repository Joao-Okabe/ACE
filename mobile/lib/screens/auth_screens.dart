import 'package:flutter/material.dart';

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
  final _email = TextEditingController();
  final _password = TextEditingController();
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

  Future<void> _showRecoveryUnavailable() async {
    await showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Recuperação indisponível'),
        content: const Text(
          'A recuperação de senha ainda não está configurada. Entre em contato com o administrador do sistema.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Entendi'),
          ),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    try {
      await AppScope.read(context).login(_email.text, _password.text);
    } catch (error) {
      if (mounted) showFailure(context, error);
    }
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
                          onPressed: _showRecoveryUnavailable,
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
                  Text('Emulador: http://10.0.2.2:8080/api/v1'),
                  Text('Computador: http://localhost:8080/api/v1'),
                  Text('Celular: http://192.168.x.x:8080/api/v1'),
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
