import 'package:aether_mobile/core/data_helpers.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('converte mapas e valores da API', () {
    final data = asMap({'nome': 'ACE', 'pontos': '12', 'ativo': true});
    expect(textOf(data, ['nome']), 'ACE');
    expect(intOf(data, ['pontos']), 12);
    expect(boolOf(data, ['ativo']), isTrue);
  });

  test('formata os status do domínio', () {
    expect(statusLabel('EM_ANDAMENTO'), 'Em andamento');
    expect(statusLabel('INSCRICOES_ABERTAS'), 'Inscrições abertas');
  });

  test('remove caracteres não numéricos', () {
    expect(digitsOnly('(13) 99999-0000'), '13999990000');
  });
}
