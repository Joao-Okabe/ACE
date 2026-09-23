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
    '/usuarios/editar',
    [UsuarioController::class, 'edit']
);

$router->post(
    '/usuarios/atualizar',
    [UsuarioController::class, 'update']
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

$router->get(
    '/escolas/visualizar', [EscolaController::class, 'visualizar']
);

$router->post(
    '/escolas/trancar', [EscolaController::class, 'trancar']
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

$router->get(
    '/alunos/visualizar', [AlunoController::class, 'visualizar']
);

$router->post(
    '/alunos/atualizar', [AlunoController::class, 'update']
);

$router->post(
    '/alunos/remover', [AlunoController::class, 'destroy']
);

$router->get(
    '/competicoes/listar', [CompeticaoController::class, 'listar']
);

$router->get(
    '/competicoes/visualizar', [CompeticaoController::class, 'visualizar']
);

$router->get(
    '/competicoes/editar', [CompeticaoController::class, 'edit']
);

$router->post(
    '/competicoes/atualizar', [CompeticaoController::class, 'update']
);

$router->post(
    '/competicoes/remover', [CompeticaoController::class, 'remover']
);

$router->get(
    '/competicoes/criar', [CompeticaoController::class, 'criar']
);

$router->post(
    '/competicoes', [CompeticaoController::class, 'store']
);

$router->get(
    '/times/criar', [TimeController::class, 'create']
);

$router->post(
    '/times', [TimeController::class, 'store']
);

$router->get(
    '/times/listar', [TimeController::class, 'list']
);

$router->get(
    '/times/visualizar', [TimeController::class, 'visualizar']
);

$router->get(
    '/times/editar', [TimeController::class, 'edit']
);

$router->post(
    '/times/atualizar', [TimeController::class, 'update']
);

$router->post(
    '/times/remover', [TimeController::class, 'destroy']
);

$router->post(
    '/times/adicionar-integrante', [VinculoTimeController::class, 'adicionarIntegrante']
);

$router->get(
    '/times/adicionar-integrante', [VinculoTimeController::class, 'adicionarIntegrante']
);

$router->get(
    '/times/adicionar-responsavel', [VinculoTimeController::class, 'adicionarResponsavel']
);

$router->post(
    '/times/remover-integrante', [VinculoTimeController::class, 'removerIntegrante']
);

$router->post(
    '/times/adicionar-responsavel', [VinculoTimeController::class, 'adicionarResponsavel']
);

$router->get(
    '/partidas/criar', [PartidaController::class, 'create']
);

$router->post(
    '/partidas', [PartidaController::class, 'storeBase']
);

$router->get(
    '/partidas/listar', [PartidaController::class, 'list']
);

$router->get(
    '/partidas/editar', [PartidaController::class, 'edit']
);

$router->post(
    '/partidas/atualizar', [PartidaController::class, 'update']
);

$router->post(
    '/partidas/remover', [PartidaController::class, 'destroy']
);