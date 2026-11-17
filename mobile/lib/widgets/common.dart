import 'package:flutter/material.dart';

import '../core/accessibility/accessibility_utils.dart';
import '../core/data_helpers.dart';
import '../core/theme.dart';

void showSuccess(BuildContext context, String message) {
  final palette = AcePalette.of(context);
  final foreground = Theme.of(context).brightness == Brightness.dark
      ? Colors.black
      : Colors.white;
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(
      SnackBar(
        backgroundColor: palette.success,
        content: Semantics(
          liveRegion: true,
          child: Text(message, style: TextStyle(color: foreground)),
        ),
      ),
    );
  AccessibilityUtils.announce(context, message);
}

void showFailure(BuildContext context, Object error) {
  final message = AccessibilityUtils.errorMessage(error);
  final scheme = Theme.of(context).colorScheme;
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(
      SnackBar(
        backgroundColor: scheme.error,
        content: Semantics(
          liveRegion: true,
          child: Text(message, style: TextStyle(color: scheme.onError)),
        ),
      ),
    );
  AccessibilityUtils.announce(context, message);
}

Future<T?> pushPage<T>(BuildContext context, Widget page) {
  return Navigator.of(context).push<T>(MaterialPageRoute(builder: (_) => page));
}

class PageBody extends StatelessWidget {
  const PageBody({
    required this.child,
    this.padding = const EdgeInsets.fromLTRB(16, 16, 16, 28),
    this.scrollable = true,
    super.key,
  });

  final Widget child;
  final EdgeInsets padding;
  final bool scrollable;

  @override
  Widget build(BuildContext context) {
    final content = SafeArea(
      top: false,
      child: Padding(padding: padding, child: child),
    );
    if (!scrollable) return content;
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: content,
    );
  }
}

class AceLogo extends StatelessWidget {
  const AceLogo({this.height = 58, this.wordmark = false, super.key});

  final double height;
  final bool wordmark;

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      'assets/images/ace_logo.png',
      height: height,
      fit: BoxFit.contain,
      errorBuilder: (_, _, _) => Icon(
        Icons.sports_soccer,
        size: height,
        color: Theme.of(context).colorScheme.primary,
      ),
    );
  }
}

class SectionHeader extends StatelessWidget {
  const SectionHeader({
    required this.title,
    this.subtitle,
    this.action,
    super.key,
  });

  final String title;
  final String? subtitle;
  final Widget? action;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Semantics(
                header: true,
                child: Text(
                  title,
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.w800,
                    color: Theme.of(context).colorScheme.onSurface,
                  ),
                ),
              ),
              if (subtitle != null) ...[
                const SizedBox(height: 3),
                Text(
                  subtitle!,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                  ),
                ),
              ],
            ],
          ),
        ),
        if (action != null) action!,
      ],
    );
  }
}

class SurfaceCard extends StatelessWidget {
  const SurfaceCard({
    required this.child,
    this.padding = const EdgeInsets.all(16),
    this.onTap,
    this.color,
    this.semanticLabel,
    super.key,
  });

  final Widget child;
  final EdgeInsets padding;
  final VoidCallback? onTap;
  final Color? color;
  final String? semanticLabel;

  @override
  Widget build(BuildContext context) {
    final card = Card(
      color: color,
      child: Padding(padding: padding, child: child),
    );
    if (onTap == null) {
      return semanticLabel == null
          ? card
          : Semantics(label: semanticLabel, child: card);
    }
    return Semantics(
      button: true,
      label: semanticLabel,
      hint: 'Toque duas vezes para visualizar',
      excludeSemantics: semanticLabel != null,
      child: InkWell(
        borderRadius: BorderRadius.circular(20),
        onTap: onTap,
        child: card,
      ),
    );
  }
}

class MetricCard extends StatelessWidget {
  const MetricCard({
    required this.label,
    required this.value,
    required this.icon,
    this.onTap,
    super.key,
  });

  final String label;
  final Object value;
  final IconData icon;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return SurfaceCard(
      onTap: onTap,
      semanticLabel: '$label. $value cadastrado(s).',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.primaryContainer,
              borderRadius: BorderRadius.circular(13),
            ),
            child: Icon(icon, color: Theme.of(context).colorScheme.secondary),
          ),
          const Spacer(),
          Text(
            value.toString(),
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(
              color: Theme.of(context).colorScheme.primary,
              fontWeight: FontWeight.w900,
            ),
          ),
          Text(
            label,
            style: TextStyle(
              color: Theme.of(context).colorScheme.onSurfaceVariant,
            ),
          ),
        ],
      ),
    );
  }
}

class StatusBadge extends StatelessWidget {
  const StatusBadge(this.status, {super.key});

  final Object? status;

  @override
  Widget build(BuildContext context) {
    final palette = AcePalette.of(context);
    final normalized = status?.toString().toUpperCase() ?? '';
    final color = switch (normalized) {
      'EM_ANDAMENTO' || 'ATIVA' || 'APROVADA' => palette.success,
      'AGENDADA' || 'INSCRICOES_ABERTAS' || 'PENDENTE' => palette.warning,
      'ENCERRADA' || 'FINALIZADA' => Theme.of(context).colorScheme.primary,
      'CANCELADA' || 'REJEITADA' => palette.danger,
      _ => Colors.blueGrey,
    };
    final icon = switch (normalized) {
      'EM_ANDAMENTO' || 'ATIVA' || 'APROVADA' => Icons.check_circle_outline,
      'AGENDADA' || 'INSCRICOES_ABERTAS' || 'PENDENTE' => Icons.schedule,
      'ENCERRADA' || 'FINALIZADA' => Icons.stop_circle_outlined,
      'CANCELADA' || 'REJEITADA' => Icons.cancel_outlined,
      _ => Icons.info_outline,
    };
    final label = statusLabel(status);
    return Semantics(
      label: 'Status: $label',
      excludeSemantics: true,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: color.withValues(alpha: .12),
          borderRadius: BorderRadius.circular(999),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 16),
            const SizedBox(width: 5),
            Text(
              label,
              style: TextStyle(
                color: color,
                fontSize: 12,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class EmptyState extends StatelessWidget {
  const EmptyState({
    required this.title,
    this.message = 'Nenhum registro encontrado.',
    this.icon = Icons.inbox_outlined,
    this.action,
    super.key,
  });

  final String title;
  final String message;
  final IconData icon;
  final Widget? action;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 52, horizontal: 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 62,
              color: Theme.of(
                context,
              ).colorScheme.primary.withValues(alpha: .6),
            ),
            const SizedBox(height: 16),
            Text(
              title,
              style: Theme.of(
                context,
              ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 6),
            Text(
              message,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
            ),
            if (action != null) ...[const SizedBox(height: 18), action!],
          ],
        ),
      ),
    );
  }
}

class ErrorState extends StatelessWidget {
  const ErrorState({required this.error, required this.onRetry, super.key});

  final Object error;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    final message = AccessibilityUtils.errorMessage(error);
    return Semantics(
      liveRegion: true,
      label: 'Erro. $message',
      child: EmptyState(
        icon: Icons.cloud_off_outlined,
        title: 'Não foi possível carregar',
        message: message,
        action: FilledButton.icon(
          onPressed: onRetry,
          icon: const Icon(Icons.refresh),
          label: const Text('Tentar novamente'),
        ),
      ),
    );
  }
}

class LoadingState extends StatelessWidget {
  const LoadingState({this.message = 'Carregando...', super.key});
  final String message;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      liveRegion: true,
      label: message,
      excludeSemantics: true,
      child: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const CircularProgressIndicator(),
              const SizedBox(height: 14),
              Text(
                message,
                style: TextStyle(
                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class SearchBarField extends StatelessWidget {
  const SearchBarField({
    required this.controller,
    required this.onSubmitted,
    this.hint = 'Buscar',
    this.onChanged,
    super.key,
  });

  final TextEditingController controller;
  final ValueChanged<String> onSubmitted;
  final ValueChanged<String>? onChanged;
  final String hint;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      textField: true,
      label: hint,
      child: TextField(
        controller: controller,
        onSubmitted: onSubmitted,
        onChanged: onChanged,
        textInputAction: TextInputAction.search,
        decoration: InputDecoration(
          labelText: hint,
          hintText: hint,
          prefixIcon: const Icon(Icons.search),
          suffixIcon: controller.text.isEmpty
              ? null
              : IconButton(
                  tooltip: 'Limpar pesquisa',
                  onPressed: () {
                    controller.clear();
                    onSubmitted('');
                  },
                  icon: const Icon(Icons.close),
                ),
        ),
      ),
    );
  }
}

class KeyValueRow extends StatelessWidget {
  const KeyValueRow({
    required this.label,
    required this.value,
    this.icon,
    super.key,
  });

  final String label;
  final String value;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 7),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (icon != null) ...[
            Icon(
              icon,
              size: 19,
              color: Theme.of(context).colorScheme.secondary,
            ),
            const SizedBox(width: 10),
          ],
          SizedBox(
            width: 110,
            child: Text(
              label,
              style: TextStyle(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}

class MatchCard extends StatelessWidget {
  const MatchCard({required this.data, this.onTap, super.key});

  final Map<String, dynamic> data;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final home = textOf(data, [
      'time_casa',
      'timeCasa',
      'mandante',
    ], fallback: 'Time A');
    final away = textOf(data, [
      'time_visitante',
      'timeVisitante',
      'visitante',
    ], fallback: 'Time B');
    final homeScore = intOf(data, ['placar_casa', 'placarCasa']);
    final awayScore = intOf(data, ['placar_visitante', 'placarVisitante']);
    return SurfaceCard(
      onTap: onTap,
      semanticLabel:
          '$home, $homeScore pontos. $away, $awayScore pontos. '
          'Status: ${statusLabel(data['status'])}. '
          '${formatDate(data['data_hora'] ?? data['dataHora'], withTime: true)}. '
          '${textOf(data, ['local'], fallback: 'Local a definir')}.',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  textOf(data, ['competicao'], fallback: 'Competição'),
                  style: TextStyle(
                    fontWeight: FontWeight.w800,
                    color: Theme.of(context).colorScheme.primary,
                  ),
                ),
              ),
              StatusBadge(data['status']),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: Text(
                  home,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 18,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.primaryContainer,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Text(
                  '$homeScore × $awayScore',
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    color: Theme.of(context).colorScheme.onPrimaryContainer,
                  ),
                ),
              ),
              Expanded(
                child: Text(
                  away,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Icon(
                Icons.schedule,
                size: 17,
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  formatDate(
                    data['data_hora'] ?? data['dataHora'],
                    withTime: true,
                  ),
                  style: TextStyle(
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                  ),
                ),
              ),
              Icon(
                Icons.location_on_outlined,
                size: 17,
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
              const SizedBox(width: 4),
              Flexible(
                child: Text(
                  textOf(data, ['local'], fallback: 'Local a definir'),
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class AppTextFormField extends StatelessWidget {
  const AppTextFormField({
    required this.controller,
    required this.label,
    this.hint,
    this.keyboardType,
    this.obscureText = false,
    this.validator,
    this.maxLines = 1,
    this.readOnly = false,
    this.onTap,
    this.prefixIcon,
    this.suffixIcon,
    this.textInputAction,
    this.maxLength,
    this.textCapitalization = TextCapitalization.none,
    this.required = false,
    this.focusNode,
    this.onFieldSubmitted,
    super.key,
  });

  final TextEditingController controller;
  final String label;
  final String? hint;
  final TextInputType? keyboardType;
  final bool obscureText;
  final String? Function(String?)? validator;
  final int maxLines;
  final bool readOnly;
  final VoidCallback? onTap;
  final IconData? prefixIcon;
  final Widget? suffixIcon;
  final TextInputAction? textInputAction;
  final int? maxLength;
  final TextCapitalization textCapitalization;
  final bool required;
  final FocusNode? focusNode;
  final ValueChanged<String>? onFieldSubmitted;

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      focusNode: focusNode,
      keyboardType: keyboardType,
      obscureText: obscureText,
      validator: validator,
      maxLines: maxLines,
      readOnly: readOnly,
      onTap: onTap,
      textInputAction: textInputAction,
      maxLength: maxLength,
      textCapitalization: textCapitalization,
      onFieldSubmitted: onFieldSubmitted,
      decoration: InputDecoration(
        labelText: required ? '$label *' : label,
        helperText: required ? 'Campo obrigatório' : null,
        hintText: hint,
        prefixIcon: prefixIcon == null ? null : Icon(prefixIcon),
        suffixIcon: suffixIcon,
      ),
    );
  }
}

String? requiredField(String? value) {
  if (value == null || value.trim().isEmpty) {
    return 'Preencha este campo obrigatório.';
  }
  return null;
}

Future<bool> showConfirmationDialog(
  BuildContext context, {
  required String title,
  required String message,
  String confirmLabel = 'Excluir',
}) async {
  final confirmed = await showDialog<bool>(
    context: context,
    builder: (dialogContext) => AlertDialog(
      title: Semantics(header: true, child: Text(title)),
      content: Text(message),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(dialogContext, false),
          child: const Text('Cancelar'),
        ),
        FilledButton(
          style: FilledButton.styleFrom(
            backgroundColor: Theme.of(dialogContext).colorScheme.error,
            foregroundColor: Theme.of(dialogContext).colorScheme.onError,
          ),
          onPressed: () => Navigator.pop(dialogContext, true),
          child: Text(confirmLabel),
        ),
      ],
    ),
  );
  return confirmed ?? false;
}

String? emailField(String? value) {
  final required = requiredField(value);
  if (required != null) return required;
  final email = value!.trim();
  if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(email)) {
    return 'Informe um e-mail válido';
  }
  return null;
}
