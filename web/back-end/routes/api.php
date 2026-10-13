<?php
/*
    Rotas de Alunos
    1. listar todos
    2. buscar um 
    3. guardar(no banco)
    4. atualiza aluno
    5. deleta aluno 
*/ 
$router->get(
    '/api/alunos',
    [AlunoApiController::class, 'index']
);
$router->get(
    '/api/alunos/{id}',
    [AlunoApiController::class, 'show']
);
$router->post(
    '/api/alunos',
    [AlunoApiController::class, 'store']
);
$router->put(
    '/api/alunos/{id}',
    [AlunoApiController::class, 'update']
);
$router->delete(
    '/api/alunos/{id}',
    [AlunoApiController::class, 'destroy']
);
/*
    Rotas de usuário
    1. listar todos
    2. buscar um
    3. guardar(no banco)
    4. atualiza usuário
    5. deleta usuário
*/
$router->get(
    '/api/usuarios',
    [UsuarioApiController::class, 'index']
);
$router->get(
    '/api/usuarios/{id}',
    [UsuarioApiController::class, 'show']
);
$router->post(
    '/api/usuarios',
    [UsuarioApiController::class, 'store']
);
$router->put(
    '/api/usuarios/{id}',
    [UsuarioApiController::class, 'update']
);
$router->delete(
    '/api/usuarios/{id}',
    [UsuarioApiController::class, 'destroy']
);
/*
    Rotas de escolas
    1. listar todas
    2. buscar uma
    3. guardar(no banco)
    4. atualiza escola
    5. deleta escola
*/
$router->get(
    '/api/escolas',
    [EscolaApiController::class, 'index']
);
$router->get(
    '/api/escolas/{id}',
    [EscolaApiController::class, 'show']
);
$router->post(
    '/api/escolas',
    [EscolaApiController::class, 'store']
);
$router->put(
    '/api/escolas/{id}',
    [EscolaApiController::class, 'update']
);
$router->delete(
    '/api/escolas/{id}',
    [EscolaApiController::class, 'destroy']
);