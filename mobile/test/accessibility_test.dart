import 'package:aether_mobile/core/api_client.dart';
import 'package:aether_mobile/core/theme.dart';
import 'package:aether_mobile/widgets/common.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

Widget _app(Widget child) => MaterialApp(
  theme: buildAceTheme(),
  home: Scaffold(body: child),
);

void main() {
  testWidgets('campo obrigatório expõe rótulo, instrução e erro textual', (
    tester,
  ) async {
    final controller = TextEditingController();
    final key = GlobalKey<FormState>();
    addTearDown(controller.dispose);

    await tester.pumpWidget(
      _app(
        Form(
          key: key,
          child: AppTextFormField(
            controller: controller,
            label: 'Nome completo',
            required: true,
            validator: requiredField,
          ),
        ),
      ),
    );

    expect(find.text('Nome completo *'), findsOneWidget);
    expect(find.text('Campo obrigatório'), findsOneWidget);
    key.currentState!.validate();
    await tester.pump();
    expect(find.text('Preencha este campo obrigatório.'), findsOneWidget);
  });

  testWidgets('status comunica ícone e texto, sem depender apenas da cor', (
    tester,
  ) async {
    await tester.pumpWidget(_app(const StatusBadge('EM_ANDAMENTO')));

    expect(find.text('Em andamento'), findsOneWidget);
    expect(find.byIcon(Icons.check_circle_outline), findsOneWidget);
    expect(
      tester.getSemantics(find.byType(StatusBadge)),
      matchesSemantics(label: 'Status: Em andamento'),
    );
  });

  testWidgets('loading e erro são regiões ao vivo com recuperação', (
    tester,
  ) async {
    await tester.pumpWidget(
      _app(const LoadingState(message: 'Carregando atletas')),
    );
    expect(
      tester.getSemantics(find.byType(LoadingState)),
      matchesSemantics(label: 'Carregando atletas', isLiveRegion: true),
    );

    await tester.pumpWidget(
      _app(
        ErrorState(
          error: const ApiFailure('Falha', statusCode: 500),
          onRetry: () {},
        ),
      ),
    );
    expect(find.text('Tentar novamente'), findsOneWidget);
    expect(find.textContaining('servidor'), findsOneWidget);
  });

  testWidgets('cartão métrico anuncia nome, valor e ação', (tester) async {
    await tester.pumpWidget(
      _app(
        SizedBox(
          width: 180,
          height: 160,
          child: MetricCard(
            label: 'Atletas',
            value: 24,
            icon: Icons.directions_run,
            onTap: () {},
          ),
        ),
      ),
    );

    final semantics = tester.getSemantics(find.byType(SurfaceCard));
    expect(semantics.label, contains('Atletas. 24'));
    expect(semantics.flagsCollection.isButton, isTrue);
  });

  testWidgets('botões de ícone respeitam alvo mínimo de 48 dp', (tester) async {
    await tester.pumpWidget(
      _app(
        IconButton(
          tooltip: 'Atualizar',
          onPressed: () {},
          icon: const Icon(Icons.refresh),
        ),
      ),
    );
    expect(
      tester.getSize(find.byType(IconButton)).width,
      greaterThanOrEqualTo(48),
    );
    expect(
      tester.getSize(find.byType(IconButton)).height,
      greaterThanOrEqualTo(48),
    );
  });
}
