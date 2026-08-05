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

$router->post(
    '/login',
    [AuthController::class, 'autenticar']
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
    '/escolas/editar', [EscolaController::class, 'edit']
);

$router->post(
    '/escolas/atualizar', [EscolaController::class, 'update']
);

$router->get(
    '/escolas/remover', [EscolaController::class, 'destroy']
);

$router->get(
    '/alunos/cadastrar', [AlunoController::class, 'create']
);

$router->post(
    '/alunos', [AlunoController::class, 'store']
);

$router->get(
    '/alunos/listar', [AlunoController::class, 'list']
);

$router->get(
    '/alunos/editar', [AlunoController::class, 'edit']
);

$router->post(
    '/alunos/atualizar', [AlunoController::class, 'update']
);

$router->get(
    '/alunos/remover', [AlunoController::class, 'destroy']
);