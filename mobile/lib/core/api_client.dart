import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiFailure implements Exception {
  const ApiFailure(this.message, {this.statusCode, this.details});

  final String message;
  final int? statusCode;
  final Object? details;

  @override
  String toString() => message;
}

class ApiClient {
  ApiClient({required this.baseUrl});

  String baseUrl;
  String? token;

  Future<dynamic> get(String path, {Map<String, Object?>? query}) =>
      _send('GET', path, query: query);

  Future<dynamic> post(
    String path, {
    Map<String, Object?>? body,
    Map<String, Object?>? query,
  }) => _send('POST', path, body: body, query: query);

  Future<dynamic> put(
    String path, {
    Map<String, Object?>? body,
    Map<String, Object?>? query,
  }) => _send('PUT', path, body: body, query: query);

  Future<dynamic> patch(
    String path, {
    Map<String, Object?>? body,
    Map<String, Object?>? query,
  }) => _send('PATCH', path, body: body, query: query);

  Future<dynamic> delete(
    String path, {
    Map<String, Object?>? body,
    Map<String, Object?>? query,
  }) => _send('DELETE', path, body: body, query: query);

  Future<dynamic> _send(
    String method,
    String path, {
    Map<String, Object?>? body,
    Map<String, Object?>? query,
  }) async {
    final cleanedPath = path.startsWith('/') ? path : '/$path';
    final queryParams = <String, String>{};
    for (final entry in (query ?? const <String, Object?>{}).entries) {
      final value = entry.value;
      if (value != null && value.toString().trim().isNotEmpty) {
        queryParams[entry.key] = value.toString();
      }
    }
    final uri = Uri.parse(
      '$baseUrl$cleanedPath',
    ).replace(queryParameters: queryParams.isEmpty ? null : queryParams);
    final headers = <String, String>{
      'accept': 'application/json',
      'content-type': 'application/json; charset=utf-8',
      if (token != null && token!.isNotEmpty) 'authorization': 'Bearer $token',
    };

    try {
      final encodedBody = body == null ? null : jsonEncode(body);
      final response = await switch (method) {
        'GET' => http.get(uri, headers: headers),
        'POST' => http.post(uri, headers: headers, body: encodedBody),
        'PUT' => http.put(uri, headers: headers, body: encodedBody),
        'PATCH' => http.patch(uri, headers: headers, body: encodedBody),
        'DELETE' => http.delete(uri, headers: headers, body: encodedBody),
        _ => throw StateError('Método HTTP inválido: $method'),
      }.timeout(const Duration(seconds: 20));

      dynamic decoded;
      if (response.body.trim().isNotEmpty) {
        try {
          decoded = jsonDecode(utf8.decode(response.bodyBytes));
        } on FormatException {
          decoded = response.body;
        }
      }

      if (response.statusCode >= 200 && response.statusCode < 300) {
        return decoded;
      }

      var message = 'Não foi possível concluir a operação.';
      if (decoded is Map) {
        message =
            (decoded['mensagem'] ??
                    decoded['message'] ??
                    decoded['erro'] ??
                    message)
                .toString();
      } else if (decoded is String && decoded.trim().isNotEmpty) {
        message = decoded;
      }
      throw ApiFailure(
        message,
        statusCode: response.statusCode,
        details: decoded,
      );
    } on TimeoutException {
      throw const ApiFailure(
        'A API demorou para responder. Verifique a conexão.',
      );
    } on ApiFailure {
      rethrow;
    } catch (error) {
      throw ApiFailure(
        'Não foi possível conectar à API em $baseUrl. Confirme se o back-end está rodando.',
        details: error,
      );
    }
  }
}
