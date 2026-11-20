import 'package:flutter/material.dart';

class AccessibleAction extends StatelessWidget {
  const AccessibleAction({
    required this.label,
    required this.child,
    this.hint,
    this.enabled = true,
    this.selected,
    this.excludeChildSemantics = false,
    super.key,
  });

  final String label;
  final String? hint;
  final bool enabled;
  final bool? selected;
  final bool excludeChildSemantics;
  final Widget child;

  @override
  Widget build(BuildContext context) => Semantics(
    button: true,
    label: label,
    hint: hint,
    enabled: enabled,
    selected: selected,
    excludeSemantics: excludeChildSemantics,
    child: child,
  );
}

class AccessibleHeader extends StatelessWidget {
  const AccessibleHeader({required this.child, super.key});
  final Widget child;

  @override
  Widget build(BuildContext context) => Semantics(header: true, child: child);
}

class AccessibleLiveRegion extends StatelessWidget {
  const AccessibleLiveRegion({
    required this.label,
    required this.child,
    super.key,
  });
  final String label;
  final Widget child;

  @override
  Widget build(BuildContext context) => Semantics(
    liveRegion: true,
    label: label,
    excludeSemantics: true,
    child: child,
  );
}
