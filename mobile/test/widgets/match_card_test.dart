import 'package:aether_mobile/widgets/common.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('shows the match creator email', (tester) async {
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: MatchCard(
            data: {
              'email_criador': 'criador@example.test',
              'status': 'AGENDADA',
            },
          ),
        ),
      ),
    );

    expect(
      find.text('E-mail do criador: criador@example.test'),
      findsOneWidget,
    );
  });
}
