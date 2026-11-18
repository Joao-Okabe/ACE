import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'api_client.dart';
import 'api_config.dart';
import 'data_helpers.dart';

class SessionController extends ChangeNotifier {
  SessionController()
    : client = ApiClient(baseUrl: ApiConfig.defaultBaseUrl()) {
    client.onTokensChanged = _saveTokens;
  }

  static const _tokenKey = 'aether_token';
  static const _userKey = 'aether_user';
  static const _refreshTokenKey = 'aether_refresh_token';
  static const _apiUrlKey = 'aether_api_url';

  final ApiClient client;
  bool initialized = false;
  bool busy = false;
  String? error;
  Map<String, dynamic>? user;

  bool get isAuthenticated => client.token?.isNotEmpty == true && user != null;
  String get profile {
    final roles = (user?['roles'] as List? ?? const [])
        .map((role) => role.toString().toUpperCase())
        .toSet();
    for (final role in const [
      'ADM',
      'DIR',
      'CRD',
      'ARB',
      'ALU',
      'PRF',
      'AGR',
      'VIS',
    ]) {
      if (roles.contains(role)) return _uiRole(role);
    }
    return user?['perfil']?.toString().toUpperCase() ?? '';
  }

  String get userName => user?['nome']?.toString() ?? 'Usuário';
  String? get schoolId => user?['escolaId']?.toString();
  String? get studentId => user?['alunoId']?.toString();

  bool hasAnyProfile(Iterable<String> profiles) {
    final roles = <String>[
      profile,
      ...((user?['roles'] as List? ?? const []).map((role) => role.toString())),
    ];
    return profiles.any(
      (expected) => roles.any(
        (role) => _profilesForRole(role).contains(expected.toUpperCase()),
      ),
    );
  }

  static String _uiRole(String value) => switch (value.toUpperCase()) {
    'ADM' => 'ADMINISTRADOR',
    'DIR' => 'ESCOLA',
    'ALU' => 'ALUNO',
    'ARB' => 'ARBITRO',
    'CRD' => 'COORDENADOR',
    'PRF' => 'PROFESSOR',
    'AGR' => 'RESPONSAVEL',
    'VIS' => 'VISITANTE',
    _ => value.toUpperCase(),
  };

  static Set<String> _profilesForRole(String value) {
    final role = value.toUpperCase();
    if (role == 'ESCOLA') return {'ESCOLA', 'DIR', 'CRD'};
    if (role == 'CRD') return {'CRD', 'COORDENADOR', 'ESCOLA'};
    if (role == 'ADMINISTRADOR') return {'ADMINISTRADOR', 'ADM'};
    if (role == 'ALUNO') return {'ALUNO', 'ALU'};
    if (role == 'ARBITRO') return {'ARBITRO', 'ARB'};
    return {role, _uiRole(role)};
  }

  Map<String, dynamic> _normalizeUser(Map<String, dynamic> value) {
    final roles = (value['roles'] as List? ?? const [])
        .map((role) => role.toString().toUpperCase())
        .toList();
    final firstRole = roles.isEmpty ? '' : roles.first;
    return {
      ...value,
      'nome': value['name'] ?? value['nome'] ?? 'Usuário',
      'perfil': value['perfil'] ?? _uiRole(firstRole),
      'roles': roles,
    };
  }

  Future<void> _saveTokens(String? accessToken, String? refreshToken) async {
    final prefs = await SharedPreferences.getInstance();
    if (accessToken == null || accessToken.isEmpty) {
      await prefs.remove(_tokenKey);
    } else {
      await prefs.setString(_tokenKey, accessToken);
    }
    if (refreshToken == null || refreshToken.isEmpty) {
      await prefs.remove(_refreshTokenKey);
    } else {
      await prefs.setString(_refreshTokenKey, refreshToken);
    }
    notifyListeners();
  }

  Future<void> initialize() async {
    final prefs = await SharedPreferences.getInstance();
    client.baseUrl = ApiConfig.normalize(
      prefs.getString(_apiUrlKey) ?? ApiConfig.defaultBaseUrl(),
    );
    client.token = prefs.getString(_tokenKey);
    client.refreshToken = prefs.getString(_refreshTokenKey);
    final rawUser = prefs.getString(_userKey);
    if (rawUser != null) {
      try {
        user = _normalizeUser(asMap(jsonDecode(rawUser)));
      } catch (_) {
        user = null;
      }
    }
    initialized = true;
    notifyListeners();
  }

  Future<void> login(String email, String password) async {
    busy = true;
    error = null;
    notifyListeners();
    try {
      final response = asMap(
        await client.post(
          '/auth/login',
          body: {'email': email.trim(), 'senha': password},
        ),
      );
      client.token = response['access_token']?.toString();
      client.refreshToken = response['refresh_token']?.toString();
      user = _normalizeUser(asMap(response['user']));
      if (client.token == null ||
          client.refreshToken == null ||
          user!['id'] == null) {
        throw const ApiFailure('A API não devolveu uma sessão válida.');
      }
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_tokenKey, client.token!);
      await prefs.setString(_refreshTokenKey, client.refreshToken!);
      await prefs.setString(_userKey, jsonEncode(user));
    } on ApiFailure catch (failure) {
      error = failure.message;
      rethrow;
    } finally {
      busy = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    final refresh = client.refreshToken;
    if (refresh != null && refresh.isNotEmpty) {
      try {
        await client.post('/auth/logout', body: {'refresh_token': refresh});
      } catch (_) {
        // A sessão local ainda deve ser encerrada quando a API estiver indisponível.
      }
    }
    client.token = null;
    client.refreshToken = null;
    user = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_refreshTokenKey);
    await prefs.remove(_userKey);
    notifyListeners();
  }

  Future<void> updateBaseUrl(String value) async {
    final normalized = ApiConfig.normalize(value);
    client.baseUrl = normalized;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_apiUrlKey, normalized);
    notifyListeners();
  }
}
