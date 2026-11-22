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
$router->get(
    '/api/modalidades',
    [EsporteApiController::class, 'modalidadesV1']
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
$router->get('/api/competicoes/{id}/arbitros/usuarios', [CompeticaoControllerApi::class, 'usuariosArbitroV1']);
$router->get('/api/competicoes/{id}/arbitros', [CompeticaoControllerApi::class, 'listarArbitrosV1']);
$router->post('/api/competicoes/{id}/arbitros', [CompeticaoControllerApi::class, 'atribuirArbitroV1']);
$router->delete('/api/competicoes/{id}/arbitros/{idUsuario}', [CompeticaoControllerApi::class, 'removerArbitroV1']);
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

$authV1 = new AuthV1Controller();
$esporteV1 = new EsporteApiController();
$competicaoV1 = new CompeticaoControllerApi();
$escolaV1 = new EscolaApiController();
$alunoV1 = new AlunoApiController();
$timeV1 = new TimeApiController();
$vinculoTimeV1 = new VinculoTimeApiController();
$partidaController = new PartidaController();
$partidaV1 = new PartidaApiController();
$dashboardV1 = new DashboardController();
$saudeV1 = new SaudeApiController();
$rankingApi = new RankingController();

$router->get(
    '/api/v1/saude', 
    [$saudeV1, 'show']
);
$router->post(
    '/api/v1/auth/login', 
    [$authV1, 'login']
);
$router->post(
    '/api/v1/auth/recuperacao/solicitar', 
    [$authV1, 'solicitarRecuperacao']
);
$router->post('/api/v1/auth/recuperacao/confirmar', [$authV1, 'confirmarRecuperacao']);
$router->get('/api/v1/auth/me', [$authV1, 'me']);
$router->post('/api/v1/auth/refresh', [$authV1, 'refresh']);
$router->post('/api/v1/auth/logout', [$authV1, 'logout']);
$importacaoCsvV1 = new ImportacaoCsvApiController();
$router->get('/api/importacoes/escolas-disponiveis', [$importacaoCsvV1, 'escolasDisponiveis']);
$router->post('/api/importacoes/{tipo}/csv', [$importacaoCsvV1, 'importar']);
$router->patch('/api/importacoes/{id}/linhas/{linha}', [$importacaoCsvV1, 'editarLinha']);
$router->post('/api/importacoes/{id}/confirmar', [$importacaoCsvV1, 'confirmar']);
$router->post('/api/v1/importacoes/{tipo}/csv', [$importacaoCsvV1, 'importar']);
$router->patch('/api/v1/importacoes/{id}/linhas/{linha}', [$importacaoCsvV1, 'editarLinha']);
$router->post('/api/v1/importacoes/{id}/confirmar', [$importacaoCsvV1, 'confirmar']);
$router->get('/api/v1/importacoes/escolas-disponiveis', [$importacaoCsvV1, 'escolasDisponiveis']);
$router->get('/api/v1/dashboard', [$dashboardV1, 'apiV1']);
$router->get('/api/v1/escolas', [$escolaV1, 'indexV1']);
$router->get('/api/v1/perfil/escola', [$escolaV1, 'perfilV1']);
$router->put('/api/v1/perfil/escola', [$escolaV1, 'atualizarPerfilV1']);
$router->get('/api/v1/escolas/codigo/{codigo}', [$escolaV1, 'buscarPorCodigoV1']);
$router->get('/api/v1/escolas/{id}', [$escolaV1, 'showV1']);
$router->get('/api/v1/alunos', [$alunoV1, 'indexV1']);
$router->post('/api/v1/alunos', [$alunoV1, 'store']);
$router->get('/api/v1/rankings/individual', [$rankingApi, 'atletas']);
$router->get('/api/v1/rankings/atletas', [$rankingApi, 'atletas']);
$router->get('/api/v1/esportes/{id}/metricas-atleta', [$rankingApi, 'metricasV1']);
$router->get('/api/v1/alunos/{id}/historico', [$alunoV1, 'historicoV1']);
$router->get('/api/v1/alunos/{id}/estatisticas', [$alunoV1, 'estatisticasV1']);
$router->get('/api/v1/alunos/{id}', [$alunoV1, 'show']);
$router->put('/api/v1/alunos/{id}', [$alunoV1, 'update']);
$router->get('/api/v1/times', [$timeV1, 'indexV1']);
$router->post('/api/v1/times', [$timeV1, 'storeV1']);
$router->get('/api/v1/times/{id}/ranking', [$timeV1, 'rankingV1']);
$router->put('/api/v1/times/{id}/capitao', [$vinculoTimeV1, 'definirCapitaoV1']);
$router->put('/api/v1/times/{id}/escalacao', [$vinculoTimeV1, 'salvarEscalacaoV1']);
$router->post('/api/v1/times/{id}/atletas/{alunoId}', [$vinculoTimeV1, 'vincularAtletaV1']);
$router->patch('/api/v1/times/{id}/atletas/{alunoId}', [$vinculoTimeV1, 'atualizarAtletaV1']);
$router->delete('/api/v1/times/{id}/atletas/{alunoId}', [$vinculoTimeV1, 'removerAtletaV1']);
$router->get('/api/v1/times/{id}', [$timeV1, 'showV1']);
$router->put('/api/v1/times/{id}', [$timeV1, 'updateV1']);
$router->get('/api/v1/esportes', [$esporteV1, 'indexV1']);
$router->get('/api/v1/modalidades', [$esporteV1, 'modalidadesV1']);
$router->get('/api/v1/categorias', [$esporteV1, 'categoriasV1']);
$router->get('/api/v1/competicoes', [$competicaoV1, 'indexV1']);
$router->get('/api/v1/competicoes/{id}/arbitros/usuarios', [$competicaoV1, 'usuariosArbitroV1']);
$router->get('/api/v1/competicoes/{id}/arbitros', [$competicaoV1, 'listarArbitrosV1']);
$router->post('/api/v1/competicoes/{id}/arbitros', [$competicaoV1, 'atribuirArbitroV1']);
$router->delete('/api/v1/competicoes/{id}/arbitros/{idUsuario}', [$competicaoV1, 'removerArbitroV1']);
$router->get('/api/v1/competicoes/{id}/classificacao', [$competicaoV1, 'classificacaoV1']);
$router->get('/api/competicoes/{id}/classificacao', [$competicaoV1, 'classificacaoV1']);
$router->get('/api/v1/competicoes/{id}/tabela', [$competicaoV1, 'tabelaV1']);
$router->post('/api/v1/competicoes/{id}/inscricoes', [$competicaoV1, 'inscricaoMobileV1']);
$router->get('/api/v1/competicoes/{id}', [$competicaoV1, 'showV1']);
$router->get('/api/v1/calendario', [$partidaV1, 'calendarioV1']);
$router->get('/api/v1/partidas', [$partidaV1, 'indexApi']);
$router->post('/api/v1/partidas', [$partidaV1, 'storeV1']);
$router->get('/api/v1/partidas/ao-vivo', [$partidaV1, 'partidasAoVivoV1']);
$router->get('/api/v1/partidas/historico', [$partidaV1, 'historicoV1']);
$router->get('/api/v1/partidas/{id}', [$partidaV1, 'showV1']);
$router->put('/api/v1/partidas/{id}/calendario', [$partidaV1, 'agendarV1']);
$router->get('/api/v1/locais', [$partidaV1, 'locaisV1']);
$router->post('/api/v1/locais', [$partidaV1, 'criarLocalNaoSuportadoV1']);
$router->put('/api/v1/partidas/{id}/local', [$partidaV1, 'atribuirLocalV1']);
$router->get('/api/v1/partidas/{id}/ao-vivo', [$partidaV1, 'showV1']);
$router->get('/api/v1/partidas/{id}/arbitragem/usuarios', [$partidaV1, 'listarArbitrosElegiveisV1']);
$router->patch('/api/v1/partidas/{id}/iniciar', [$partidaV1, 'iniciarV1']);
$router->post('/api/v1/partidas/{id}/arbitragem', [$partidaV1, 'registrarArbitragemV1']);
$router->put('/api/v1/partidas/{id}/resultado', [$partidaV1, 'registrarResultadoV1']);
$router->put('/api/v1/partidas/{id}/estatisticas-atletas', [$partidaV1, 'registrarEstatisticasAtletasV1']);
$router->post('/api/v1/partidas/{id}/pontuacoes', [$partidaV1, 'registrarPontuacaoV1']);
$router->post('/api/v1/partidas/{id}/penalidades', [$partidaV1, 'registrarPenalidadeV1']);
$router->post('/api/v1/partidas/{id}/publicar', [$partidaV1, 'publicarResultadoV1']);
$router->get('/api/v1/rankings/escolas', [$rankingApi, 'escolas']);
$router->get('/api/v1/rankings/equipes', [$rankingApi, 'equipes']);
$router->get('/api/rankings/escolas', [$rankingApi, 'escolas']);
$router->get('/api/rankings/equipes', [$rankingApi, 'equipes']);
$router->get('/api/rankings/atletas', [$rankingApi, 'atletas']);
$router->get('/api/rankings/individual', [$rankingApi, 'atletas']);
$router->get('/api/esportes/{id}/metricas-atleta', [$rankingApi, 'metricasV1']);
$router->get('/api/partidas', [$partidaController, 'index']);
$router->post('/api/partidas', [$partidaController, 'store']);
$router->get('/api/calendario', [$partidaV1, 'calendarioV1']);
$router->get('/api/partidas/calendario', [$partidaV1, 'calendarioV1']);
$router->get('/api/partidas/ao-vivo', [$partidaV1, 'partidasAoVivoV1']);
$router->get('/api/partidas/historico', [$partidaV1, 'historicoV1']);
$router->get('/api/partidas/{id}/ao-vivo', [$partidaV1, 'showV1']);
$router->get('/api/partidas/{id}', [$partidaController, 'show']);
$router->put('/api/partidas/{id}/calendario', [$partidaV1, 'agendarV1']);
$router->put('/api/partidas/{id}/local', [$partidaV1, 'atribuirLocalV1']);
$router->patch('/api/partidas/{id}/iniciar', [$partidaV1, 'iniciarV1']);
$router->get('/api/partidas/{id}/arbitragem/usuarios', [$partidaV1, 'listarArbitrosElegiveisV1']);
$router->post('/api/partidas/{id}/arbitragem', [$partidaV1, 'registrarArbitragemV1']);
$router->put('/api/partidas/{id}/resultado', [$partidaV1, 'registrarResultadoV1']);
$router->post('/api/partidas/{id}/pontuacoes', [$partidaV1, 'registrarPontuacaoV1']);
$router->post('/api/partidas/{id}/penalidades', [$partidaV1, 'registrarPenalidadeV1']);
$router->post('/api/partidas/{id}/publicar', [$partidaV1, 'publicarResultadoV1']);
$router->get('/api/locais', [$partidaV1, 'locaisV1']);
$router->post('/api/locais', [$partidaV1, 'criarLocalNaoSuportadoV1']);
$router->put('/api/partidas/{id}/estatisticas-atletas', [$partidaV1, 'registrarEstatisticasAtletasV1']);
