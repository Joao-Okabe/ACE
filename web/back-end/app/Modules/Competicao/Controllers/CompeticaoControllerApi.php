<?php

class CompeticaoControllerApi
{
    private ?CompeticaoService $service = null;

    private function service(): CompeticaoService
    {
        if ($this->service === null) {
            $this->service = new CompeticaoService();
        }

        return $this->service;
    }

    public function index(): void
    {
        try {
            $filtros = [
                'nome' => trim((string) ($_GET['nome'] ?? '')),
                'escola' => (int) ($_GET['escola'] ?? 0),
                'ordem' => strtolower((string) ($_GET['ordem'] ?? 'asc')),
            ];

            if (!in_array($filtros['ordem'], ['asc', 'desc'], true)) {
                $filtros['ordem'] = 'asc';
            }

            Response::json([
                'sucesso' => true,
                'dados' => $this->service()->listar($filtros),
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível listar as competições.',
            ], 500);
        }
    }

    public function listar(): void
    {
        $this->index();
    }

    public function indexV1(): void
    {
        $pagina = isset($_GET['pagina'])
            ? $this->parametroInteiro('pagina', 1, 1, 100000)
            : $this->parametroInteiro('page', 1, 1, 100000);
        $porPagina = isset($_GET['limite'])
            ? $this->parametroInteiro('limite', 20, 1, 100)
            : $this->parametroInteiro('per_page', 20, 1, 100);
        $nome = $_GET['busca'] ?? $_GET['nome'] ?? '';
        $ordemParametro = $_GET['ordem'] ?? 'DESC';
        $ordem = is_string($ordemParametro) ? strtoupper($ordemParametro) : '';
        if (!is_string($nome) || !in_array($ordem, ['ASC', 'DESC'], true)) {
            Response::error('VALIDATION_ERROR', 'Parâmetro de consulta inválido.', 400);
        }

        $escolaFiltro = isset($_GET['escolaId'])
            ? $this->parametroInteiro('escolaId', 1, 1, PHP_INT_MAX)
            : (isset($_GET['escola'])
                ? $this->parametroInteiro('escola', 1, 1, PHP_INT_MAX)
                : null);
        $filtros = [
            'nome' => trim($nome),
            'escola' => $escolaFiltro,
            'status' => isset($_GET['status']) && is_string($_GET['status']) ? strtoupper($_GET['status']) : null,
            'modalidade_id' => isset($_GET['modalidadeId'])
                ? $this->parametroInteiro('modalidadeId', 1, 1, PHP_INT_MAX)
                : null,
            'de' => isset($_GET['de']) && is_string($_GET['de']) ? $_GET['de'] : null,
            'ate' => isset($_GET['ate']) && is_string($_GET['ate']) ? $_GET['ate'] : null,
            'ordem' => $ordem,
            'pagina' => $pagina,
            'per_page' => $porPagina,
            'paginado' => true,
        ];

        try {
            $itens = $this->service()->listar($filtros);
            $temProxima = count($itens) > $porPagina;
            if ($temProxima) {
                array_pop($itens);
            }
            Response::success([
                'items' => $itens,
                'page' => $pagina,
                'per_page' => $porPagina,
                'has_more' => $temProxima,
            ], 'Competições listadas.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível listar as competições.', 500);
        }
    }

    public function showV1(int $id): void
    {
        $this->show($id);
    }

    public function classificacaoV1(int $id): void
    {
        if ($id <= 0) {
            Response::error('VALIDATION_ERROR', 'ID da competição inválido.', 400);
        }
        try {
            Response::success($this->buscarClassificacao($id), 'Classificação consultada.');
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Competição não encontrada.') {
                Response::error('NOT_FOUND', 'Competição não encontrada.', 404);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar a classificação.', 500);
        }
    }

    public function tabelaV1(int $id): void
    {
        try {
            $dados = [
                'classificacao' => $this->buscarClassificacao($id),
                'chaveamento' => $this->service()->listarChaveamento($id),
            ];
            Response::success($dados, 'Tabela da competição consultada.');
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Competição não encontrada.') {
                Response::error('NOT_FOUND', 'Competição não encontrada.', 404);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar a tabela.', 500);
        }
    }

    public function inscricaoMobileV1(int $id): void
    {
        $dados = $this->lerDadosRequisicao();
        $idTime = (int) ($dados['timeId'] ?? $dados['cd_time'] ?? 0);
        if ($idTime <= 0) {
            Response::error('VALIDATION_ERROR', 'Informe um ID inteiro de time.', 400);
        }
        try {
            $this->service()->inscreverTime($id, $idTime);
            Response::success(null, 'Time inscrito na competição.');
        } catch (Throwable $e) {
            $status = str_contains(mb_strtolower($e->getMessage()), 'permissão') ? 403 : 422;
            Response::error($status === 403 ? 'FORBIDDEN' : 'VALIDATION_ERROR', $e->getMessage(), $status);
        }
    }

    private function buscarClassificacao(int $id): array
    {
        $this->service()->buscar($id);
        $stmt = Database::connect()->prepare("
            SELECT
                g.cd_grupo,
                g.nm_grupo,
                cg.posicao,
                cg.cd_time,
                t.nm_time,
                COALESCE(
                    jsonb_object_agg(mc.nm_metrica_classificacao, est.valor)
                        FILTER (WHERE mc.cd_metrica_classificacao IS NOT NULL),
                    '{}'::jsonb
                ) AS estatisticas
            FROM etapa_competicao ec
            JOIN grupo_competicao g ON g.cd_etapa_competicao = ec.cd_etapa_competicao
            JOIN classificacao_grupo cg ON cg.cd_grupo = g.cd_grupo
            JOIN time t ON t.cd_time = cg.cd_time
            LEFT JOIN estatistica_classificacao est
                ON est.cd_classificacao_grupo = cg.cd_classificacao_grupo
            LEFT JOIN metrica_classificacao mc
                ON mc.cd_metrica_classificacao = est.cd_metrica_classificacao
            WHERE ec.cd_competicao = :competicao
            GROUP BY ec.cd_etapa_competicao, g.cd_grupo, g.nm_grupo, cg.posicao,
                     cg.cd_time, t.nm_time, g.ordem
            ORDER BY ec.ordem, g.ordem NULLS LAST, cg.posicao
        ");
        $stmt->execute([':competicao' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function parametroInteiro(string $nome, int $padrao, int $minimo, int $maximo): int
    {
        if (!isset($_GET[$nome])) {
            return $padrao;
        }
        $valor = filter_var($_GET[$nome], FILTER_VALIDATE_INT);
        if ($valor === false || $valor < $minimo || $valor > $maximo) {
            Response::error('VALIDATION_ERROR', "Parâmetro {$nome} inválido.", 400);
        }
        return (int) $valor;
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID da competição inválido.',
            ], 400);
        }

        try {
            Response::json([
                'sucesso' => true,
                'dados' => $this->service()->buscar($id),
            ]);
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Competição não encontrada.') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Competição não encontrada.',
                ], 404);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível consultar a competição.',
            ], 500);
        }
    }

    public function store(): void
    {
        $dados = $this->lerDadosRequisicao();

        try {
            $this->service()->criar($dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Competição cadastrada com sucesso.',
            ], 201);
        } catch (InvalidArgumentException $e) {
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage(),
            ], 422);
        } catch (PDOException $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível cadastrar a competição.',
            ], 500);
        } catch (Exception $e) {
            $mensagem = $e->getMessage();

            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem,
            ], $this->ehErroPermissao($mensagem) ? 403 : 422);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Ocorreu um erro interno ao cadastrar a competição.',
            ], 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID da competição inválido.',
            ], 400);
        }

        $dados = $this->lerDadosRequisicao();

        try {
            $this->service()->atualizar($id, $dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Competição atualizada com sucesso.',
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Competição não encontrada.') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem,
                ], 404);
            }

            if ($this->ehErroPermissao($mensagem)) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem,
                ], 403);
            }

            if ($e instanceof PDOException) {
                error_log($e->getMessage());

                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Não foi possível atualizar a competição.',
                ], 500);
            }

            if ($e instanceof InvalidArgumentException || $e instanceof Exception) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem,
                ], 422);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível atualizar a competição.',
            ], 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID da competição inválido.',
            ], 400);
        }

        try {
            $this->service()->remover($id);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Competição excluída com sucesso.',
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Competição não encontrada.') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem,
                ], 404);
            }

            if ($this->ehErroPermissao($mensagem)) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem,
                ], 403);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível excluir a competição.',
            ], 500);
        }
    }

    public function remover(int $id): void
    {
        $this->destroy($id);
    }

    /** Dados usados pelo formulário de criação do controller web. */
    public function criar(): void
    {
        try {
            Response::json([
                'sucesso' => true,
                'dados' => [
                    'formatos' => (new FormatoService())->listar(),
                    'esportes' => (new EsporteService())->listar(),
                    'modalidades' => (new ModalidadeService())->listar(),
                    'escolas' => (new EscolaService())->listar(),
                    'ehAdministrador' => Permissoes::temPapel('ADM'),
                ],
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível carregar os dados para criar a competição.',
            ], 500);
        }
    }

    /** Dados completos exibidos pela tela de edição da competição. */
    public function edit(int $id): void
    {
        $this->responderCompeticaoGerenciavel($id);
    }

    /** Dados agregados da tela de visualização da competição. */
    public function visualizar(int $id): void
    {
        if ($id <= 0) {
            $this->idInvalido();
        }

        try {
            $competicao = $this->service()->buscar($id);
            Response::json([
                'sucesso' => true,
                'dados' => [
                    'competicao' => $competicao,
                    'periodoInscricao' => $this->service()->buscarPeriodoInscricao($id),
                    'timesInscritos' => $this->service()->listarTimesInscritos($id),
                    'timesDisponiveis' => $this->service()->listarTimesDisponiveisInscricao($id),
                    'chaveamento' => $this->service()->listarChaveamento($id),
                    'podeGerarChaveamento' => $this->service()->usuarioEhCriador($competicao),
                    'podeGerenciar' => $this->service()->podeGerenciar($competicao),
                ],
            ]);
        } catch (Throwable $e) {
            $this->responderErroConsulta($e, 'Não foi possível consultar a competição.');
        }
    }

    public function editEtapa(int $idCompeticao): void
    {
        if ($idCompeticao <= 0) {
            $this->idInvalido();
        }

        try {
            $competicao = $this->service()->buscar($idCompeticao);
            $this->service()->exigirPermissao($competicao);

            Response::json([
                'sucesso' => true,
                'dados' => [
                    'competicao' => $competicao,
                    'etapas' => $this->service()->listarEtapas($idCompeticao),
                ],
            ]);
        } catch (Throwable $e) {
            $this->responderErroConsulta($e, 'Não foi possível consultar as etapas da competição.');
        }
    }

    public function updateEtapa(int $idCompeticao, int $idEtapa): void
    {
        if ($idCompeticao <= 0 || $idEtapa <= 0) {
            $this->idInvalido();
        }

        $dados = $this->lerDadosRequisicao();
        try {
            $this->service()->buscarEtapa($idEtapa, $idCompeticao);
            $this->service()->atualizarEtapa($idEtapa, $idCompeticao, $dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Etapa atualizada com sucesso.',
            ]);
        } catch (Throwable $e) {
            $this->responderErroOperacao($e, 'Não foi possível atualizar a etapa.');
        }
    }

    public function createEtapa(int $idCompeticao): void
    {
        if ($idCompeticao <= 0) {
            $this->idInvalido();
        }

        try {
            $competicao = $this->service()->buscar($idCompeticao);
            $this->service()->exigirPermissao($competicao);

            Response::json([
                'sucesso' => true,
                'dados' => ['competicao' => $competicao],
            ]);
        } catch (Throwable $e) {
            $this->responderErroConsulta($e, 'Não foi possível carregar os dados da competição.');
        }
    }

    public function storeEtapa(int $idCompeticao): void
    {
        if ($idCompeticao <= 0) {
            $this->idInvalido();
        }

        $dados = $this->lerDadosRequisicao();
        try {
            $this->service()->criarEtapa($idCompeticao, $dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Etapa cadastrada com sucesso.',
            ], 201);
        } catch (Throwable $e) {
            $this->responderErroOperacao($e, 'Não foi possível cadastrar a etapa.');
        }
    }

    public function gerarChaveamento(int $idCompeticao): void
    {
        if ($idCompeticao <= 0) {
            $this->idInvalido();
        }

        try {
            $this->service()->gerarChaveamento($idCompeticao);
            Response::json([
                'sucesso' => true,
                'mensagem' => 'Chaveamento gerado com sucesso.',
            ], 201);
        } catch (Throwable $e) {
            $this->responderErroOperacao($e, 'Não foi possível gerar o chaveamento.');
        }
    }

    public function criarInscricao(int $idCompeticao): void
    {
        if ($idCompeticao <= 0) {
            $this->idInvalido();
        }

        $dados = $this->lerDadosRequisicao();
        try {
            $this->service()->criarInscricao($idCompeticao, $dados);
            Response::json([
                'sucesso' => true,
                'mensagem' => 'Período de inscrição cadastrado com sucesso.',
            ], 201);
        } catch (Throwable $e) {
            $this->responderErroOperacao($e, 'Não foi possível cadastrar o período de inscrição.');
        }
    }

    public function editInscricao(int $idCompeticao): void
    {
        if ($idCompeticao <= 0) {
            $this->idInvalido();
        }

        try {
            $competicao = $this->service()->buscar($idCompeticao);
            $this->service()->exigirPermissao($competicao);
            Response::json([
                'sucesso' => true,
                'dados' => [
                    'competicao' => $competicao,
                    'periodoInscricao' => $this->service()->buscarPeriodoInscricao($idCompeticao),
                ],
            ]);
        } catch (Throwable $e) {
            $this->responderErroConsulta($e, 'Não foi possível consultar o período de inscrição.');
        }
    }

    public function inscreverTime(int $idCompeticao): void
    {
        if ($idCompeticao <= 0) {
            $this->idInvalido();
        }

        $dados = $this->lerDadosRequisicao();
        try {
            $this->service()->inscreverTime($idCompeticao, (int) ($dados['id_time'] ?? $dados['cd_time'] ?? 0));
            Response::json([
                'sucesso' => true,
                'mensagem' => 'Time inscrito com sucesso.',
            ], 201);
        } catch (Throwable $e) {
            $this->responderErroOperacao($e, 'Não foi possível inscrever o time.');
        }
    }

    public function removerTimeInscrito(int $idCompeticao, int $idTime): void
    {
        if ($idCompeticao <= 0 || $idTime <= 0) {
            $this->idInvalido();
        }

        try {
            $this->service()->removerTimeInscrito($idCompeticao, $idTime);
            Response::json([
                'sucesso' => true,
                'mensagem' => 'Time removido da competição com sucesso.',
            ]);
        } catch (Throwable $e) {
            $this->responderErroOperacao($e, 'Não foi possível remover o time da competição.');
        }
    }

    private function responderCompeticaoGerenciavel(int $id): void
    {
        if ($id <= 0) {
            $this->idInvalido();
        }

        try {
            $competicao = $this->service()->buscar($id);
            $this->service()->exigirPermissao($competicao);
            Response::json(['sucesso' => true, 'dados' => $competicao]);
        } catch (Throwable $e) {
            $this->responderErroConsulta($e, 'Não foi possível consultar a competição.');
        }
    }

    private function idInvalido(): void
    {
        Response::json([
            'sucesso' => false,
            'mensagem' => 'ID inválido.',
        ], 400);
    }

    private function responderErroConsulta(Throwable $e, string $mensagemPadrao): void
    {
        $mensagem = $e->getMessage();
        if ($mensagem === 'Competição não encontrada.' || $mensagem === 'Etapa não encontrada nesta competição.') {
            Response::json(['sucesso' => false, 'mensagem' => $mensagem], 404);
        }
        if ($this->ehErroPermissao($mensagem)) {
            Response::json(['sucesso' => false, 'mensagem' => $mensagem], 403);
        }

        error_log($mensagem);
        Response::json(['sucesso' => false, 'mensagem' => $mensagemPadrao], 500);
    }

    private function responderErroOperacao(Throwable $e, string $mensagemPadrao): void
    {
        $mensagem = $e->getMessage();
        if ($mensagem === 'Competição não encontrada.' || $mensagem === 'Etapa não encontrada nesta competição.') {
            Response::json(['sucesso' => false, 'mensagem' => $mensagem], 404);
        }
        if ($this->ehErroPermissao($mensagem)) {
            Response::json(['sucesso' => false, 'mensagem' => $mensagem], 403);
        }
        if ($e instanceof PDOException) {
            error_log($mensagem);
            Response::json(['sucesso' => false, 'mensagem' => $mensagemPadrao], 500);
        }
        if ($e instanceof Exception) {
            Response::json(['sucesso' => false, 'mensagem' => $mensagem], 422);
        }

        error_log($mensagem);
        Response::json(['sucesso' => false, 'mensagem' => $mensagemPadrao], 500);
    }

    private function lerDadosRequisicao(): array
    {
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

        if (str_contains($contentType, 'application/json')) {
            $corpo = file_get_contents('php://input');
            $dados = json_decode($corpo, true);

            if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'O corpo da requisição contém JSON inválido.',
                ], 400);
            }

            return $dados;
        }

        if (in_array(strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')), ['PUT', 'PATCH'], true)) {
            $corpo = file_get_contents('php://input');
            parse_str($corpo, $dados);
            return is_array($dados) ? $dados : [];
        }

        return $_POST;
    }

    private function ehErroPermissao(string $mensagem): bool
    {
        $mensagem = mb_strtolower($mensagem);

        return str_contains($mensagem, 'permissão')
            || str_contains($mensagem, 'não autenticado');
    }
}
