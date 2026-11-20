<?php

// Autoload do Composer (dompdf etc.), instalado no back-end.
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../config/env.php';
loadEnv(__DIR__ . '/../.env');

// Configura os cookies antes de iniciar a sessão.
$params = session_get_cookie_params();

session_set_cookie_params([
    'lifetime' => 0,
    'path' => $params['path'],
    'domain' => $params['domain'],
    'secure' => !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Inicia ou recupera a sessão.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/Core/Autoloader.php';
Autoloader::register();

require_once __DIR__ . '/../app/Core/View.php';

// Garante um token CSRF por sessão, inclusive para sessões anônimas.
Csrf::token();

require_once __DIR__ . '/../routes/web.php';
require_once __DIR__ . '/../routes/api.php';

$router->dispatch();