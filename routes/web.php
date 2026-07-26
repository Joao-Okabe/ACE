<?php 

$router = new Router();

$router->get(
    '/escolas/cadastrar',
    [escolaController::class, 'create']
);

$router->post(
    '/escolas',
    [escolaController::class, 'store']
);