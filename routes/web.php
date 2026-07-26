<?php 

$router = new Router();

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
