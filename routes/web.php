<?php 

$router = new Router();

$router->get(
    '/',
    [authController::class, 'login']
);

$router->get(
    '/login',
    [authController::class, 'login']
);

$router->post(
    '/login',
    [authController::class, 'autenticar']
);

$router->get(
    '/dashboard',
    [authController::class, 'dashboard']
);

$router->get(
    '/logout',
    [authController::class, 'logout']
);

$router->get(
    '/usuarios/cadastrar',
    [usuarioController::class, 'create']
);

$router->post(
    '/usuarios',
    [usuarioController::class, 'store']
);

$router->get(
    '/escolas/cadastrar',
    [escolaController::class, 'create']
);

$router->post(
    '/escolas',
    [escolaController::class, 'store']
);

$router->get(
    '/dashboard', [dashboardController::class, 'index']
);

$router->get(
    '/escolas/listar', [escolaController::class, 'list']
);

$router->get(
    '/escolas/editar', [escolaController::class, 'edit']
);