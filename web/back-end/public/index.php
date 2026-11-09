<?php

declare(strict_types=1);

/**
 * Front controller da aplicação.
 * Todo o tráfego passa por aqui (veja public/.htaccess).
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
Autoloader::register();

require_once dirname(__DIR__) . '/config/env.php';

loadEnv(dirname(__DIR__) . '/.env');

$router = new Router();

require_once dirname(__DIR__) . '/routes/web.php';
require_once dirname(__DIR__) . '/routes/api.php';

$router->dispatch();
