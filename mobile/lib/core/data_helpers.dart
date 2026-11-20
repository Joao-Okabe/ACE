import 'package:intl/intl.dart';

Map<String, dynamic> asMap(Object? value) {
  final result = value is Map<String, dynamic>
      ? Map<String, dynamic>.from(value)
      : value is Map
      ? value.map((key, item) => MapEntry(key.toString(), item))
      : <String, dynamic>{};
  result.putIfAbsent(
    'id',
    () =>
        result['cd_aluno'] ??
        result['cd_time'] ??
        result['cd_escola'] ??
        result['cd_competicao'] ??
        result['cd_partida'] ??
        result['cd_modalidade'] ??
        result['cd_esporte'] ??
        result['aluno_id'] ??
        result['time_id'] ??
        result['escola_id'],
  );
  result.putIfAbsent(
    'nome',
    () =>
        result['name'] ??
        result['nm_usuario'] ??
        result['nm_time'] ??
        result['nm_escola'] ??
        result['nm_competicao'] ??
        result['nm_esporte'] ??
        result['nm_modalidade'],
  );
  result.putIfAbsent('competicao', () => result['nm_competicao']);
  if (result['nome'] == null && result['categoria'] != null) {
    final sexo = result['sexo']?.toString();
    result['nome'] = sexo == null || sexo.isEmpty
        ? result['categoria']
        : '${result['categoria']} • $sexo';
  }
  final teams = result['times'];
  if (teams is List) {
    final parsedTeams = teams.whereType<Map>().map(asMap).toList();
    final home = parsedTeams.where((team) => boolOf(team, ['mandante']));
    final away = parsedTeams.where((team) => !boolOf(team, ['mandante']));
    final homeTeam = home.isEmpty ? null : home.first;
    final awayTeam = away.isEmpty ? null : away.first;
    result.putIfAbsent('time_casa', () => homeTeam?['nome']);
    result.putIfAbsent('time_casa_id', () => homeTeam?['id']);
    result.putIfAbsent('time_visitante', () => awayTeam?['nome']);
    result.putIfAbsent('time_visitante_id', () => awayTeam?['id']);
  }
  return result;
}

List<Map<String, dynamic>> asList(Object? value, {String key = 'itens'}) {
  Object? source = value;
  if (source is Map) {
    source = source[key] ?? source['items'] ?? source['itens'];
  }
  if (source is List) return source.map(asMap).toList();
  return const [];
}

String textOf(
  Map<String, dynamic> data,
  List<String> keys, {
  String fallback = '—',
}) {
  for (final key in keys) {
    final value = data[key];
    if (value != null && value.toString().trim().isNotEmpty) {
      return value.toString();
    }
  }
  return fallback;
}

int intOf(Map<String, dynamic> data, List<String> keys, {int fallback = 0}) {
  for (final key in keys) {
    final value = data[key];
    if (value is num) return value.toInt();
    final parsed = int.tryParse(value?.toString() ?? '');
    if (parsed != null) return parsed;
  }
  return fallback;
}

double doubleOf(
  Map<String, dynamic> data,
  List<String> keys, {
  double fallback = 0,
}) {
  for (final key in keys) {
    final value = data[key];
    if (value is num) return value.toDouble();
    final parsed = double.tryParse(value?.toString() ?? '');
    if (parsed != null) return parsed;
  }
  return fallback;
}

bool boolOf(
  Map<String, dynamic> data,
  List<String> keys, {
  bool fallback = false,
}) {
  for (final key in keys) {
    final value = data[key];
    if (value is bool) return value;
    if (value?.toString().toLowerCase() == 'true') return true;
    if (value?.toString().toLowerCase() == 'false') return false;
  }
  return fallback;
}

String formatDate(Object? value, {bool withTime = false}) {
  final raw = value?.toString();
  if (raw == null || raw.isEmpty) return 'A definir';
  final parsed = DateTime.tryParse(raw)?.toLocal();
  if (parsed == null) return raw;
  return DateFormat(
    withTime ? 'dd/MM/yyyy • HH:mm' : 'dd/MM/yyyy',
    'pt_BR',
  ).format(parsed);
}

String statusLabel(Object? value) {
  final status = value?.toString().trim().toUpperCase() ?? '';
  if (status.isEmpty) return 'Não informado';
  const names = <String, String>{
    'EM_ANDAMENTO': 'Em andamento',
    'INSCRICOES_ABERTAS': 'Inscrições abertas',
    'PLANEJADA': 'Planejada',
    'AGENDADA': 'Agendada',
    'ENCERRADA': 'Encerrada',
    'FINALIZADA': 'Finalizada',
    'CANCELADA': 'Cancelada',
    'PENDENTE': 'Pendente',
    'APROVADA': 'Aprovada',
    'REJEITADA': 'Rejeitada',
    'ATIVA': 'Ativa',
    'INATIVA': 'Inativa',
  };
  return names[status] ??
      status
          .toLowerCase()
          .split('_')
          .map((word) {
            if (word.isEmpty) return word;
            return '${word[0].toUpperCase()}${word.substring(1)}';
          })
          .join(' ');
}

bool canPublishMatch(Object? value) {
  final status = value?.toString().trim().toUpperCase() ?? '';
  return status == 'FINALIZADA' || status == 'EM_ANDAMENTO';
}

String digitsOnly(String value) => value.replaceAll(RegExp(r'\D'), '');
