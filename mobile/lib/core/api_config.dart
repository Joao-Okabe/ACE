import 'package:flutter/foundation.dart';

abstract final class ApiConfig {
  static const environmentUrl = String.fromEnvironment('API_BASE_URL');

  static String defaultBaseUrl() {
    if (environmentUrl.trim().isNotEmpty) {
      return normalize(environmentUrl);
    }
    if (kIsWeb) return 'http://localhost:8080/api/mobile';
    return switch (defaultTargetPlatform) {
      TargetPlatform.android => 'http://10.0.2.2:8080/api/mobile',
      _ => 'http://localhost:8080/api/mobile',
    };
  }

  static String normalize(String value) {
    var url = value.trim();
    while (url.endsWith('/')) {
      url = url.substring(0, url.length - 1);
    }
    if (!url.endsWith('/api/mobile')) {
      if (url.endsWith('/api')) {
        url = '$url/mobile';
      } else {
        url = '$url/api/mobile';
      }
    }
    return url;
  }
}
