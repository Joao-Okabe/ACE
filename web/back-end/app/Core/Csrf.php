<?php

/*
    Csrf
    ->  Proteção CSRF centralizada para todas as requisições mutantes
        (POST, PUT, PATCH, DELETE), tanto nas rotas web quanto nas de API.

    token():
        ->  Garante que exista um token CSRF na sessão e o retorna.

    validarRequest():
        ->  Valida o token da requisição atual.
            - Formulários HTML: campo `csrf_token` (POST) ou header
              `X-CSRF-Token`.
            - Requisições de API (JSON): header `X-CSRF-Token` OU campo
              `csrf_token` no corpo JSON (para compatibilidade com clientes
              que não podem definir headers).

    campo():
        ->  Retorna o input hidden pronto para embutir em formulários HTML
            (uso comum em todas as views que enviam POST).

    exigirToken():
        ->  Valida e responde 403 automaticamente (JSON para rotas de API,
            texto simples para rotas web) caso o token seja inválido.
*/

class Csrf
{
    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Campo oculto pronto para <form> (helper usado pelas views).
     */
    public static function campo(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
            . '">';
    }

    public static function validarRequest(): bool
    {
        $tokenRecebido = '';

        // 1. Header X-CSRF-Token (AJAX/fetch).
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (is_string($header) && $header !== '') {
            $tokenRecebido = $header;
        }

        // 2. Campo csrf_token em $_POST (formulários HTML e multipart).
        if ($tokenRecebido === '' && isset($_POST['csrf_token'])) {
            $tokenRecebido = (string) $_POST['csrf_token'];
        }

        // 3. Campo csrf_token em corpo JSON (clientes de API sem header).
        if (
            $tokenRecebido === ''
            && str_contains(
                strtolower($_SERVER['CONTENT_TYPE'] ?? ''),
                'application/json'
            )
        ) {
            $corpo = file_get_contents('php://input');
            $json = json_decode($corpo, true);

            if (is_array($json) && isset($json['csrf_token'])) {
                $tokenRecebido = (string) $json['csrf_token'];
            }
        }

        $tokenSessao = self::token();

        return is_string($tokenRecebido)
            && $tokenRecebido !== ''
            && hash_equals($tokenSessao, $tokenRecebido);
    }

    public static function exigirToken(): void
    {
        if (self::validarRequest()) {
            return;
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        if (str_starts_with($uri, '/api/')) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Token CSRF ausente ou inválido.'
            ], 403);
        }

        http_response_code(403);
        echo 'Token CSRF ausente ou inválido.';
        exit;
    }
}
