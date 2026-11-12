<?php

declare(strict_types=1);

/**
 * Ponto de entrada da API REST (Back-end).
 */

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
Autoloader::register();

require_once dirname(__DIR__) . '/config/env.php';
loadEnv(dirname(__DIR__) . '/.env');

/*
|--------------------------------------------------------------------------
| Identificação da rota
|--------------------------------------------------------------------------
*/

$uri = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

if (!is_string($uri) || $uri === '') {
    $uri = '/';
}

if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}

$ehApiV1 = $uri === '/api/v1'
    || str_starts_with($uri, '/api/v1/');

/*
|--------------------------------------------------------------------------
| CORS
|--------------------------------------------------------------------------
*/

$origensPermitidas = array_filter(
    array_map(
        'trim',
        explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '')
    )
);

$origem = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origem !== '') {
    header('Vary: Origin');

    if (in_array($origem, $origensPermitidas, true)) {
        header("Access-Control-Allow-Origin: {$origem}");

        // Cookies somente para web e API legada.
        if (!$ehApiV1) {
            header('Access-Control-Allow-Credentials: true');
        }

        header(
            'Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS'
        );

        header(
            'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token'
        );

        header('Access-Control-Max-Age: 86400');
    }
}

/*
|--------------------------------------------------------------------------
| Preflight
|--------------------------------------------------------------------------
*/

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/*
|--------------------------------------------------------------------------
| Sessão PHP
|--------------------------------------------------------------------------
|
| A API V1 utiliza exclusivamente autenticação JWT.
| Web e API legada continuam utilizando sessão.
|
*/

if (!$ehApiV1) {
    $params = session_get_cookie_params();

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $params['path'] ?? '/',
        'domain' => $params['domain'] ?? '',
        'secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/

$router = new Router();

// Rota raiz para status da API.
$router->get('/', function () {
    Response::json([
        'sucesso' => true,
        'mensagem' => 'ACE API REST está online.',
        'versao' => '1.0.0'
    ]);
});

// Rotas da API.
require_once dirname(__DIR__) . '/routes/api.php';

$router->dispatch();
