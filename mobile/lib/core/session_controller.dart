import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'api_client.dart';
import 'api_config.dart';
import 'data_helpers.dart';

class SessionController extends ChangeNotifier {
  SessionController() : client = ApiClient(baseUrl: ApiConfig.defaultBaseUrl());

  static const _tokenKey = 'aether_token';
  static const _userKey = 'aether_user';
  static const _apiUrlKey = 'aether_api_url';

  final ApiClient client;
  bool initialized = false;
  bool busy = false;
  String? error;
  Map<String, dynamic>? user;

  bool get isAuthenticated => client.token?.isNotEmpty == true && user != null;
  String get profile => user?['perfil']?.toString().toUpperCase() ?? '';
  String get userName => user?['nome']?.toString() ?? 'Usuário';
  String? get schoolId => user?['escolaId']?.toString();
  String? get studentId => user?['alunoId']?.toString();

  bool hasAnyProfile(Iterable<String> profiles) =>
      profiles.map((e) => e.toUpperCase()).contains(profile);

  Future<void> initialize() async {
    final prefs = await SharedPreferences.getInstance();
    client.baseUrl = ApiConfig.normalize(
      prefs.getString(_apiUrlKey) ?? ApiConfig.defaultBaseUrl(),
    );
    client.token = prefs.getString(_tokenKey);
    final rawUser = prefs.getString(_userKey);
    if (rawUser != null) {
      try {
        user = asMap(jsonDecode(rawUser));
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
      client.token = response['token']?.toString();
      user = asMap(response['usuario']);
      if (client.token == null || user!.isEmpty) {
        throw const ApiFailure('A API não devolveu uma sessão válida.');
      }
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_tokenKey, client.token!);
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
    client.token = null;
    user = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
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
