import 'package:intl/intl.dart';

Map<String, dynamic> asMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, item) => MapEntry(key.toString(), item));
  }
  return <String, dynamic>{};
}

List<Map<String, dynamic>> asList(Object? value, {String key = 'itens'}) {
  Object? source = value;
  if (source is Map && source.containsKey(key)) source = source[key];
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

String digitsOnly(String value) => value.replaceAll(RegExp(r'\D'), '');
