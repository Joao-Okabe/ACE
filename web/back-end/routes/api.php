<?php
/*
    Status da API
*/
$router->get(
    '/api',
    function () {
        Response::json([
            'sucesso' => true,
            'mensagem' => 'ACE API REST está online.',
            'versao' => '0.1.0'
        ]);
    }
);

/*
    Rotas de proteção CSRF
*/
$router->get(
    '/api/csrf',
    [CsrfApiController::class, 'token']
);

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
$router->post(
    '/api/alunos/{id}',
    [AlunoApiController::class, 'update']
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
$router->post(
    '/api/escolas/{id}',
    [EscolaApiController::class, 'update']
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
    Rotas de esportes
*/
$router->get(
    '/api/esportes',
    [EsporteApiController::class, 'index']
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
    Rotas de competições
    1. listar competições visíveis ao usuário
    2. consultar uma competição
    3. cadastrar competição
    4. atualizar competição
    5. excluir competição
*/
$router->get(
    '/api/competicoes',
    [CompeticaoControllerApi::class, 'listar']
);
$router->get(
    '/api/competicoes/{id}',
    [CompeticaoControllerApi::class, 'show']
);
$router->post(
    '/api/competicoes',
    [CompeticaoControllerApi::class, 'store']
);
$router->put(
    '/api/competicoes/{id}',
    [CompeticaoControllerApi::class, 'update']
);
$router->delete(
    '/api/competicoes/{id}',
    [CompeticaoControllerApi::class, 'remover']
);
$router->get(
    '/api/competicoes/opcoes/criacao',
    [CompeticaoControllerApi::class, 'criar']
);
$router->get(
    '/api/competicoes/{id}/editar',
    [CompeticaoControllerApi::class, 'edit']
);
$router->get(
    '/api/competicoes/{id}/visualizar',
    [CompeticaoControllerApi::class, 'visualizar']
);
$router->get(
    '/api/competicoes/{id}/etapas',
    [CompeticaoControllerApi::class, 'editEtapa']
);
$router->post(
    '/api/competicoes/{id}/etapas',
    [CompeticaoControllerApi::class, 'storeEtapa']
);
$router->get(
    '/api/competicoes/{id}/etapas/nova',
    [CompeticaoControllerApi::class, 'createEtapa']
);
$router->put(
    '/api/competicoes/{id}/etapas/{idEtapa}',
    [CompeticaoControllerApi::class, 'updateEtapa']
);
$router->post(
    '/api/competicoes/{id}/chaveamento',
    [CompeticaoControllerApi::class, 'gerarChaveamento']
);
$router->get(
    '/api/competicoes/{id}/inscricao',
    [CompeticaoControllerApi::class, 'editInscricao']
);
$router->post(
    '/api/competicoes/{id}/inscricao',
    [CompeticaoControllerApi::class, 'criarInscricao']
);
$router->post(
    '/api/competicoes/{id}/inscricao/times',
    [CompeticaoControllerApi::class, 'inscreverTime']
);
$router->delete(
    '/api/competicoes/{id}/inscricao/times/{idTime}',
    [CompeticaoControllerApi::class, 'removerTimeInscrito']
);
/*
    Rotas de autenticação
    1. login (autentica e inicia sessão)
    2. me (dados do usuário logado)
    3. logout (encerra a sessão)
*/
$router->get(
    '/api/auth/me',
    [AuthApiController::class, 'me']
);
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

$router->get(
    '/api/times/{id}/integrantes',
    [VinculoTimeApiController::class, 'listarIntegrantes']
);

$router->get(
    '/api/times/{id}/funcoes',
    [VinculoTimeApiController::class, 'listarFuncoesIntegrante']
);

$router->get(
    '/api/times/{id}/responsaveis',
    [VinculoTimeApiController::class, 'listarResponsaveis']
);

$router->get(
    '/api/times/{id}/responsaveis/disponiveis',
    [VinculoTimeApiController::class, 'listarResponsaveisDisponiveis']
);

$router->get(
    '/api/times/{id}/tecnicos',
    [VinculoTimeApiController::class, 'listarTecnicos']
);

$router->get(
    '/api/times/{id}/tecnicos/disponiveis',
    [VinculoTimeApiController::class, 'listarTecnicosDisponiveis']
);

// Zona de Teste!!!!!!!1
$authV1 = new AuthV1Controller();

$router->post('/api/v1/auth/login', [$authV1, 'login']);
$router->get('/api/v1/auth/me', [$authV1, 'me']);
$router->post('/api/v1/auth/refresh', [$authV1, 'refresh']);
$router->post('/api/v1/auth/logout', [$authV1, 'logout']);
