import 'package:flutter/material.dart';
import 'package:flutter/semantics.dart';

import '../api_client.dart';

enum AccessibleMessageKind {
  connection,
  server,
  validation,
  forbidden,
  session,
  notFound,
  unknown,
}

abstract final class AccessibilityUtils {
  static String ordinal(int position) => '$positionº lugar';

  static String errorMessage(Object error) {
    if (error is! ApiFailure) {
      return 'Ocorreu um problema inesperado. Tente novamente.';
    }
    final code = error.statusCode;
    if (code == 400 || code == 422) {
      return error.message.isEmpty
          ? 'Verifique os dados informados e tente novamente.'
          : error.message;
    }
    if (code == 401) {
      return 'Sua sessão expirou. Entre novamente para continuar.';
    }
    if (code == 403) return 'Você não tem permissão para realizar esta ação.';
    if (code == 404) return 'O conteúdo solicitado não foi encontrado.';
    if (code != null && code >= 500) {
      return 'O servidor não conseguiu concluir a solicitação. Tente novamente.';
    }
    return error.message;
  }

  static void announce(BuildContext context, String message) {
    if (!MediaQuery.supportsAnnounceOf(context)) return;
    final direction = Directionality.maybeOf(context) ?? TextDirection.ltr;
    SemanticsService.announce(message, direction);
  }
}
