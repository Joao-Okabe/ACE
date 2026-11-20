import 'package:flutter/material.dart';

import '../core/accessibility/accessibility_config.dart';
import '../core/accessibility/accessibility_scope.dart';
import '../core/accessibility/accessibility_utils.dart';

Future<void> showAccessibilityPanel(BuildContext context) {
  return showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    useSafeArea: true,
    builder: (context) => const AccessibilityPanel(),
  );
}

class AccessibilityButton extends StatelessWidget {
  const AccessibilityButton({this.onSurface = false, super.key});

  final bool onSurface;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    final button = IconButton(
      tooltip: 'Opções de acessibilidade',
      onPressed: () => showAccessibilityPanel(context),
      icon: const Icon(Icons.accessibility_new),
      style: onSurface
          ? IconButton.styleFrom(
              foregroundColor: colors.secondary,
              minimumSize: const Size(52, 52),
              shape: const CircleBorder(),
            )
          : null,
    );
    if (!onSurface) return button;
    return Material(
      color: colors.primary,
      elevation: 6,
      shadowColor: Colors.black.withValues(alpha: .3),
      shape: const CircleBorder(),
      clipBehavior: Clip.antiAlias,
      child: button,
    );
  }
}

class AccessibilityPanel extends StatelessWidget {
  const AccessibilityPanel({super.key});

  @override
  Widget build(BuildContext context) {
    final controller = AccessibilityScope.of(context);
    return Semantics(
      namesRoute: true,
      label: 'Opções de acessibilidade',
      child: SingleChildScrollView(
        padding: EdgeInsets.fromLTRB(
          20,
          4,
          20,
          24 + MediaQuery.viewInsetsOf(context).bottom,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              children: [
                Expanded(
                  child: Semantics(
                    header: true,
                    child: Text(
                      'Acessibilidade',
                      style: Theme.of(context).textTheme.headlineSmall
                          ?.copyWith(fontWeight: FontWeight.w900),
                    ),
                  ),
                ),
                IconButton(
                  tooltip: 'Fechar opções de acessibilidade',
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.close),
                ),
              ],
            ),
            Text(
              'Personalize a leitura e as cores. As escolhas ficam salvas neste dispositivo.',
              style: TextStyle(
                color: Theme.of(context).colorScheme.onSurfaceVariant,
              ),
            ),
            const SizedBox(height: 24),
            _PreferenceSection(
              title: 'Tamanho da fonte',
              description: 'Ajusta os textos sem ignorar a escala do sistema.',
              children: [
                for (final value in AccessibilityFontScale.values)
                  _PreferenceChip(
                    label: value.label,
                    selected: controller.fontScale == value,
                    onSelected: () async {
                      await controller.setFontScale(value);
                      if (context.mounted) {
                        AccessibilityUtils.announce(
                          context,
                          'Tamanho da fonte: ${value.label}',
                        );
                      }
                    },
                  ),
              ],
            ),
            const SizedBox(height: 22),
            _PreferenceSection(
              title: 'Tema',
              children: [
                for (final value in AccessibilityThemeMode.values)
                  _PreferenceChip(
                    label: value.label,
                    icon: value.icon,
                    selected: controller.themeMode == value,
                    onSelected: () async {
                      await controller.setThemeMode(value);
                      if (context.mounted) {
                        AccessibilityUtils.announce(
                          context,
                          'Tema ${value.label.toLowerCase()} ativado',
                        );
                      }
                    },
                  ),
              ],
            ),
            const SizedBox(height: 22),
            _PreferenceSection(
              title: 'Filtros para daltonismo',
              description:
                  'Aplica uma paleta alternativa; ícones e textos continuam identificando os estados.',
              children: [
                for (final value in ColorVisionMode.values)
                  _PreferenceChip(
                    label: value.label,
                    selected: controller.colorVisionMode == value,
                    onSelected: () async {
                      await controller.setColorVisionMode(value);
                      if (context.mounted) {
                        AccessibilityUtils.announce(
                          context,
                          'Paleta ${value.label} ativada',
                        );
                      }
                    },
                  ),
              ],
            ),
            const SizedBox(height: 26),
            OutlinedButton.icon(
              onPressed: () async {
                await controller.reset();
                if (context.mounted) {
                  AccessibilityUtils.announce(
                    context,
                    'Configurações de acessibilidade restauradas',
                  );
                }
              },
              icon: const Icon(Icons.restart_alt),
              label: const Text('Restaurar configurações padrão'),
            ),
          ],
        ),
      ),
    );
  }
}

class _PreferenceSection extends StatelessWidget {
  const _PreferenceSection({
    required this.title,
    required this.children,
    this.description,
  });

  final String title;
  final String? description;
  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Semantics(
          header: true,
          child: Text(
            title,
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w800),
          ),
        ),
        if (description != null) ...[
          const SizedBox(height: 4),
          Text(
            description!,
            style: TextStyle(
              color: Theme.of(context).colorScheme.onSurfaceVariant,
            ),
          ),
        ],
        const SizedBox(height: 10),
        Wrap(spacing: 8, runSpacing: 8, children: children),
      ],
    );
  }
}

class _PreferenceChip extends StatelessWidget {
  const _PreferenceChip({
    required this.label,
    required this.selected,
    required this.onSelected,
    this.icon,
  });

  final String label;
  final bool selected;
  final VoidCallback onSelected;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      selected: selected,
      label: label,
      onTap: onSelected,
      child: ExcludeSemantics(
        child: ConstrainedBox(
          constraints: const BoxConstraints(minHeight: 48),
          child: ChoiceChip(
            selected: selected,
            showCheckmark: true,
            avatar: icon == null ? null : Icon(icon, size: 19),
            label: Text(label),
            onSelected: (_) => onSelected(),
          ),
        ),
      ),
    );
  }
}
