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
