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

// Configuração segura de cookies de sessão
$params = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => 0,
    'path' => $params['path'] ?? '/',
    'domain' => $params['domain'] ?? '',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Suporte a CORS
$origem = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!empty($origem)) {
    header("Access-Control-Allow-Origin: {$origem}");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new Router();

// Rota raiz para status da API
$router->get('/', function () {
    Response::json([
        'sucesso' => true,
        'mensagem' => 'ACE API REST está online.',
        'versao' => '1.0.0'
    ]);
});

// Rotas da API
require_once dirname(__DIR__) . '/routes/api.php';

$router->dispatch();
