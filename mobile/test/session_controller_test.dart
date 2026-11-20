import 'dart:convert';

import 'package:aether_mobile/core/session_controller.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

const _secureStorageChannel = MethodChannel(
  'plugins.it_nomads.com/flutter_secure_storage',
);

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  test('migra, atualiza e limpa tokens no armazenamento seguro', () async {
    final secureValues = <String, String>{};
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(_secureStorageChannel, (call) async {
          final arguments = call.arguments as Map<dynamic, dynamic>;
          final key = arguments['key'] as String?;
          switch (call.method) {
            case 'read':
              return secureValues[key];
            case 'write':
              secureValues[key!] = arguments['value'] as String;
              return null;
            case 'delete':
              secureValues.remove(key);
              return null;
            default:
              throw MissingPluginException(
                'Método de armazenamento não suportado: ${call.method}',
              );
          }
        });
    SharedPreferences.setMockInitialValues({
      'aether_token': 'legacy-access',
      'aether_refresh_token': 'legacy-refresh',
      'aether_api_url': 'https://api.example.test',
      'aether_user': jsonEncode({'id': 7, 'nome': 'Usuário'}),
    });
    final controller = SessionController();
    addTearDown(() {
      controller.dispose();
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(_secureStorageChannel, null);
    });

    await controller.initialize();

    expect(controller.client.token, 'legacy-access');
    expect(controller.client.refreshToken, 'legacy-refresh');
    expect(controller.isAuthenticated, isTrue);
    expect(secureValues, {
      'aether_token': 'legacy-access',
      'aether_refresh_token': 'legacy-refresh',
    });
    final preferences = await SharedPreferences.getInstance();
    expect(preferences.containsKey('aether_token'), isFalse);
    expect(preferences.containsKey('aether_refresh_token'), isFalse);
    expect(preferences.getString('aether_api_url'), 'https://api.example.test');

    controller.client.token = 'rotated-access';
    controller.client.refreshToken = 'rotated-refresh';
    await controller.client.onTokensChanged!.call(
      controller.client.token,
      controller.client.refreshToken,
    );
    expect(secureValues, {
      'aether_token': 'rotated-access',
      'aether_refresh_token': 'rotated-refresh',
    });

    controller.client.refreshToken = null;
    await controller.logout();
    expect(secureValues, isEmpty);
    expect(controller.client.token, isNull);
    expect(preferences.containsKey('aether_user'), isFalse);
  });
}
