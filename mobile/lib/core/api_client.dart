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
  String? refreshToken;
  Future<void> Function(String? accessToken, String? refreshToken)?
  onTokensChanged;
  Future<void>? _refreshing;

  Future<dynamic> get(String path, {Map<String, Object?>? query}) =>
      _send('GET', path, query: query);

  Future<dynamic> post(
    String path, {
    Map<String, Object?>? body,
    Map<String, Object?>? query,
  }) => _send('POST', path, body: body, query: query);

  Future<dynamic> postMultipart(
    String path, {
    required Map<String, String> fields,
    Map<String, List<int>> files = const {},
    Map<String, String> fileNames = const {},
    Map<String, Object?>? query,
  }) => _sendMultipart(
    path,
    fields: fields,
    files: files,
    fileNames: fileNames,
    query: query,
  );

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
    bool retried = false,
  }) async {
    final cleanedPath = path.startsWith('/') ? path : '/$path';
    final queryParams = <String, String>{};
    for (final entry in (query ?? const <String, Object?>{}).entries) {
      final value = entry.value;
      if (value != null && value.toString().trim().isNotEmpty) {
        queryParams[entry.key] = value.toString();
      }
    }
    var uri = Uri.parse('$baseUrl$cleanedPath');
    if (queryParams.isNotEmpty) {
      uri = uri.replace(
        queryParameters: {...uri.queryParameters, ...queryParams},
      );
    }
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
        if (decoded is Map && decoded['success'] == true) {
          return decoded['data'];
        }
        return decoded;
      }

      if (response.statusCode == 401 &&
          !retried &&
          refreshToken?.isNotEmpty == true &&
          !path.endsWith('/auth/login') &&
          !path.endsWith('/auth/refresh') &&
          !path.endsWith('/auth/logout')) {
        await _refreshAccessToken();
        return _send(method, path, body: body, query: query, retried: true);
      }

      var message = 'Não foi possível concluir a operação.';
      if (decoded is Map) {
        final error = decoded['error'];
        message =
            ((error is Map ? error['message'] : null) ??
                    decoded['mensagem'] ??
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

  Future<void> _refreshAccessToken() async {
    final activeRefresh = _refreshing;
    if (activeRefresh != null) {
      await activeRefresh;
      return;
    }

    final refresh = refreshToken;
    if (refresh == null || refresh.isEmpty) {
      throw const ApiFailure(
        'A sessão expirou. Entre novamente.',
        statusCode: 401,
      );
    }

    final operation = () async {
      final uri = Uri.parse('$baseUrl/auth/refresh');
      final response = await http
          .post(
            uri,
            headers: const {
              'accept': 'application/json',
              'content-type': 'application/json; charset=utf-8',
            },
            body: jsonEncode({'refresh_token': refresh}),
          )
          .timeout(const Duration(seconds: 20));

      dynamic decoded;
      if (response.body.trim().isNotEmpty) {
        try {
          decoded = jsonDecode(utf8.decode(response.bodyBytes));
        } on FormatException {
          decoded = null;
        }
      }
      final data = decoded is Map ? decoded['data'] : null;
      if (response.statusCode < 200 ||
          response.statusCode >= 300 ||
          data is! Map) {
        final error = decoded is Map ? decoded['error'] : null;
        final message = error is Map ? error['message']?.toString() : null;
        token = null;
        refreshToken = null;
        await onTokensChanged?.call(null, null);
        throw ApiFailure(
          message ?? 'A sessão expirou. Entre novamente.',
          statusCode: response.statusCode,
          details: decoded,
        );
      }

      token = data['access_token']?.toString();
      refreshToken = data['refresh_token']?.toString();
      if (token == null ||
          token!.isEmpty ||
          refreshToken == null ||
          refreshToken!.isEmpty) {
        throw const ApiFailure('A API não devolveu uma sessão válida.');
      }
      await onTokensChanged?.call(token, refreshToken);
    }();

    _refreshing = operation;
    try {
      await operation;
    } finally {
      _refreshing = null;
    }
  }

  Future<dynamic> _sendMultipart(
    String path, {
    required Map<String, String> fields,
    required Map<String, List<int>> files,
    required Map<String, String> fileNames,
    Map<String, Object?>? query,
    bool retried = false,
  }) async {
    final cleanedPath = path.startsWith('/') ? path : '/$path';
    final uri = Uri.parse('$baseUrl$cleanedPath');
    final queryParams = <String, String>{
      ...uri.queryParameters,
      for (final entry in (query ?? const <String, Object?>{}).entries)
        if (entry.value != null && entry.value.toString().trim().isNotEmpty)
          entry.key: entry.value.toString(),
    };
    final request =
        http.MultipartRequest(
            'POST',
            uri.replace(
              queryParameters: queryParams.isEmpty ? null : queryParams,
            ),
          )
          ..headers['accept'] = 'application/json'
          ..fields.addAll(fields);
    if (token?.isNotEmpty == true) {
      request.headers['authorization'] = 'Bearer $token';
    }
    for (final entry in files.entries) {
      request.files.add(
        http.MultipartFile.fromBytes(
          entry.key,
          entry.value,
          filename: fileNames[entry.key] ?? 'upload.bin',
        ),
      );
    }

    try {
      final streamedResponse = await request.send().timeout(
        const Duration(seconds: 20),
      );
      final response = await http.Response.fromStream(
        streamedResponse,
      ).timeout(const Duration(seconds: 20));
      dynamic decoded;
      if (response.body.trim().isNotEmpty) {
        try {
          decoded = jsonDecode(response.body);
        } on FormatException {
          decoded = response.body;
        }
      }
      if (response.statusCode >= 200 && response.statusCode < 300) {
        if (decoded is Map && decoded['success'] == true) {
          return decoded['data'];
        }
        return decoded;
      }
      if (response.statusCode == 401 &&
          !retried &&
          refreshToken?.isNotEmpty == true) {
        await _refreshAccessToken();
        return _sendMultipart(
          path,
          fields: fields,
          files: files,
          fileNames: fileNames,
          query: query,
          retried: true,
        );
      }
      final error = decoded is Map ? decoded['error'] : null;
      final message = error is Map
          ? error['message']?.toString()
          : decoded is Map
          ? (decoded['mensagem'] ?? decoded['message'])?.toString()
          : decoded?.toString();
      throw ApiFailure(
        message?.trim().isNotEmpty == true
            ? message!
            : 'Não foi possível concluir a operação.',
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
