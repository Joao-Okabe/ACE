import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:package_info_plus/package_info_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import 'update_config.dart';

class UpdateGate extends StatefulWidget {
  const UpdateGate({required this.child, super.key});

  final Widget child;

  @override
  State<UpdateGate> createState() => _UpdateGateState();
}

class _UpdateGateState extends State<UpdateGate> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _checkForUpdate());
  }

  Future<void> _checkForUpdate() async {
    try {
      final update = await UpdateChecker.check();
      if (!mounted || update == null) return;
      await showDialog<void>(
        context: context,
        barrierDismissible: !update.required,
        builder: (context) => PopScope(
          canPop: !update.required,
          child: AlertDialog(
            title: const Text('Nova versão disponível'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Versão instalada: ${update.installedVersion}'),
                Text('Nova versão: ${update.versionName}'),
                const SizedBox(height: 16),
                Text(update.message),
              ],
            ),
            actions: [
              if (!update.required)
                TextButton(
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Text('Depois'),
                ),
              FilledButton(
                onPressed: () => _openDownload(update.apkUrl),
                child: const Text('Baixar atualização'),
              ),
            ],
          ),
        ),
      );
    } catch (_) {
      // A atualização é complementar e nunca pode impedir o app de abrir.
    }
  }

  Future<void> _openDownload(Uri apkUrl) async {
    try {
      await launchUrl(apkUrl, mode: LaunchMode.externalApplication);
    } catch (_) {
      // O navegador indisponível não afeta o restante do aplicativo.
    }
  }

  @override
  Widget build(BuildContext context) => widget.child;
}

abstract final class UpdateChecker {
  static Future<AppUpdate?> check() async {
    final manifestUri = Uri.tryParse(UpdateConfig.manifestUrl.trim());
    if (manifestUri == null ||
        !manifestUri.hasScheme ||
        !{'http', 'https'}.contains(manifestUri.scheme)) {
      return null;
    }

    final response = await http
        .get(manifestUri, headers: const {'Accept': 'application/json'})
        .timeout(const Duration(seconds: 8));
    if (response.statusCode < 200 || response.statusCode >= 300) return null;

    final decoded = jsonDecode(utf8.decode(response.bodyBytes));
    if (decoded is! Map<String, dynamic>) return null;

    final remoteCode = _asInt(decoded['versionCode']);
    final versionName = decoded['versionName']?.toString().trim() ?? '';
    final apkUrl = Uri.tryParse(decoded['apkUrl']?.toString().trim() ?? '');
    if (remoteCode == null ||
        versionName.isEmpty ||
        apkUrl == null ||
        !apkUrl.hasScheme ||
        !{'http', 'https'}.contains(apkUrl.scheme)) {
      return null;
    }

    final installed = await PackageInfo.fromPlatform();
    final installedCode = int.tryParse(installed.buildNumber) ?? 0;
    if (remoteCode <= installedCode) return null;

    return AppUpdate(
      versionCode: remoteCode,
      versionName: versionName,
      apkUrl: apkUrl,
      message: decoded['mensagem']?.toString().trim().isNotEmpty == true
          ? decoded['mensagem'].toString().trim()
          : 'Correções e melhorias disponíveis.',
      required: decoded['obrigatoria'] == true,
      installedVersion: installed.version,
    );
  }

  static int? _asInt(Object? value) {
    if (value is int) return value;
    return int.tryParse(value?.toString() ?? '');
  }
}

class AppUpdate {
  const AppUpdate({
    required this.versionCode,
    required this.versionName,
    required this.apkUrl,
    required this.message,
    required this.required,
    required this.installedVersion,
  });

  final int versionCode;
  final String versionName;
  final Uri apkUrl;
  final String message;
  final bool required;
  final String installedVersion;
}
