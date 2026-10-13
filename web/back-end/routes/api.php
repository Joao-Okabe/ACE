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
/*
    Rotas de times
    1. listar todos
    2. buscar um
    3. guardar(no banco)
    4. atualiza time
    5. deleta time
*/
$router->get(
    '/api/times',
    [TimeApiController::class, 'index']
);
$router->get(
    '/api/times/{id}',
    [TimeApiController::class, 'show']
);
$router->post(
    '/api/times',
    [TimeApiController::class, 'store']
);
$router->put(
    '/api/times/{id}',
    [TimeApiController::class, 'update']
);
$router->delete(
    '/api/times/{id}',
    [TimeApiController::class, 'destroy']
);
/*
    Rotas de autenticação
    1. login (autentica e inicia sessão)
    2. logout (encerra a sessão)
*/
$router->post(
    '/api/auth/login',
    [AuthApiController::class, 'login']
);
$router->post(
    '/api/auth/logout',
    [AuthApiController::class, 'logout']
);
/*
    Rotas de vínculo de time
    1. vincular responsável
    2. vincular integrante
    3. remover integrante
    4. tornar capitão / remover capitão
    5. vincular técnico
    6. salvar escalação
*/
$router->post(
    '/api/times/{id}/responsaveis',
    [VinculoTimeApiController::class, 'vincularResponsavel']
);
$router->post(
    '/api/times/{id}/integrantes',
    [VinculoTimeApiController::class, 'vincularIntegrante']
);
$router->delete(
    '/api/times/{id}/integrantes/{idUsuario}',
    [VinculoTimeApiController::class, 'removerIntegrante']
);
$router->put(
    '/api/times/{id}/integrantes/{idUsuario}/capitao',
    [VinculoTimeApiController::class, 'tornarCapitao']
);
$router->delete(
    '/api/times/{id}/integrantes/{idUsuario}/capitao',
    [VinculoTimeApiController::class, 'removerCapitao']
);
$router->post(
    '/api/times/{id}/tecnicos',
    [VinculoTimeApiController::class, 'vincularTecnico']
);
$router->put(
    '/api/times/{id}/escalacao',
    [VinculoTimeApiController::class, 'salvarEscalacao']
);