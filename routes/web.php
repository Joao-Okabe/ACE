<?php 

$router = new Router();

$router->get(
    '/',
    [AuthController::class, 'login']
);

$router->get(
    '/login',
    [AuthController::class, 'login']
);

$router->get(
    '/logout',
    [AuthController::class, 'logout']
);

$router->get(
    '/usuarios/cadastrar',
    [UsuarioController::class, 'create']
);

$router->post(
    '/usuarios',
    [UsuarioController::class, 'store']
);

$router->get(
    '/escolas/cadastrar',
    [EscolaController::class, 'create']
);

$router->post(
    '/escolas',
    [EscolaController::class, 'store']
);

$router->get(
    '/dashboard', [DashboardController::class, 'index']
);

$router->get(
    '/escolas/listar', [EscolaController::class, 'list']
);

$router->get(
    '/escolas/editar', [escolaController::class, 'edit']
);

$router->post(
    '/escolas/atualizar', [escolaController::class, 'update']
);

$router->get(
    '/escolas/remover', [escolaController::class, 'destroy']
);

$router->get(
    '/alunos/cadastrar', [alunoController::class, 'create']
);

$router->post(
    '/alunos', [alunoController::class, 'store']
);

$router->get(
    '/alunos/listar', [alunoController::class, 'list']
);

$router->get(
    '/alunos/editar', [alunoController::class, 'edit']
);

$router->post(
    '/alunos/atualizar', [alunoController::class, 'update']
);

$router->get(
    '/alunos/remover', [alunoController::class, 'destroy']
);