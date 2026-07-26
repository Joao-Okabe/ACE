<?php
require_once __DIR__ . '/../config/env.php';
loadEnv(__DIR__ . '/../.env');

require_once __DIR__ . '/../app/Core/Autoloader.php';
Autoloader::register();

require_once __DIR__ . '/../routes/web.php';
$router->dispatch();
