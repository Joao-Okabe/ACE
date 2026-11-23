import 'package:flutter/foundation.dart';

abstract final class ApiConfig {
  static const environmentUrl = String.fromEnvironment('API_BASE_URL');

  static String defaultBaseUrl() {
    if (environmentUrl.trim().isNotEmpty) {
      return normalize(environmentUrl);
    }
    if (kIsWeb) return 'https://aethercompany.com.br/api/v1';
    return switch (defaultTargetPlatform) {
      TargetPlatform.android => 'https://aethercompany.com.br/api/v1',
      _ => 'https://aethercompany.com.br/api/v1',
    };
  }

  static String normalize(String value) {
    var url = value.trim();
    while (url.endsWith('/')) {
      url = url.substring(0, url.length - 1);
    }
    if (url.endsWith('/api/mobile')) {
      url = '${url.substring(0, url.length - '/mobile'.length)}/v1';
      return url;
    }
    if (!url.endsWith('/api/v1')) {
      if (url.endsWith('/api')) {
        url = '$url/v1';
      } else {
        url = '$url/api/v1';
      }
    }
    return url;
  }
}
