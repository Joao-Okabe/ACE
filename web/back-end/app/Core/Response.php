<?php

class Response
{
    public static function success(array|null $data = null, string $message = 'Operação realizada.', int $status = 200): void
    {
        self::json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    public static function error(string $code, string $message, int $status, array|null $details = null): void
    {
        $error = ['code' => $code, 'message' => $message];
        if ($details !== null) {
            $error['details'] = $details;
        }
        self::json(['success' => false, 'error' => $error], $status);
    }

    public static function json(
        array $dados,
        int $status = 200
    ): void {
        if (str_starts_with(
            parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
            '/api/v1/'
        ) && array_key_exists('sucesso', $dados)) {
            if ($dados['sucesso'] === true) {
                $dados = [
                    'success' => true,
                    'data' => $dados['dados'] ?? null,
                    'message' => $dados['mensagem'] ?? 'Operação realizada.',
                ];
            } else {
                $codigo = match ($status) {
                    400 => 'BAD_REQUEST',
                    401 => 'UNAUTHENTICATED',
                    403 => 'FORBIDDEN',
                    404 => 'NOT_FOUND',
                    409 => 'CONFLICT',
                    422 => 'VALIDATION_ERROR',
                    default => 'INTERNAL_ERROR',
                };
                $dados = [
                    'success' => false,
                    'error' => [
                        'code' => $codigo,
                        'message' => $dados['mensagem'] ?? 'Não foi possível concluir a operação.',
                    ],
                ];
            }
        }

        // Evita que espaços acidentais emitidos ao carregar arquivos PHP
        // corrompam o JSON ou enviem os cabeçalhos antes da resposta.
        if (ob_get_level() > 0) {
            ob_clean();
        }

        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $dados,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        exit;
    }
}
