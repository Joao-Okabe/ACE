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
