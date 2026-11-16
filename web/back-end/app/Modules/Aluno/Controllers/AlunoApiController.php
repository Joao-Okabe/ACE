<?php

class AlunoApiController
{
    private ?AlunoService $service = null;

    private function service(): AlunoService
    {
        if ($this->service === null) {
            $this->service = new AlunoService();
        }

        return $this->service;
    }

    public function index(): void
    {
        try {
            $filtros = $this->filtrosListaV1();
            $filtros = [
                ...$filtros,
            ];
            $alunos = $this->service()->listar($filtros);

            Response::json([
                'sucesso' => true,
                'dados' => $alunos
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível listar os alunos.'
            ], 500);
        }
    }

    public function indexV1(): void
    {
        try {
            $filtros = $this->filtrosListaV1();
            $porPagina = (int) ($_GET['limite'] ?? $_GET['per_page'] ?? 30);
            $pagina = (int) ($_GET['pagina'] ?? $_GET['page'] ?? 1);
            $alunos = $this->service()->listar($filtros);
            $temProxima = count($alunos) > $porPagina;
            if ($temProxima) {
                array_pop($alunos);
            }
            Response::success([
                'pagina' => $pagina,
                'limite' => $porPagina,
                'itens' => $alunos,
                'tem_proxima' => $temProxima,
            ], 'Alunos listados.');
        } catch (Throwable $e) {
            if (str_contains(mb_strtolower($e->getMessage()), 'permissão')) {
                Response::error('FORBIDDEN', $e->getMessage(), 403);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível listar os alunos.', 500);
        }
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do aluno inválido.'
            ], 400);
        }

        if (!Permissoes::estaLogado()) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Autenticação necessária.'
            ], 401);
        }

        try {
            $service = $this->service();

            $aluno = $service->buscar($id);
            $this->exigirAcessoLeitura($aluno);

            Response::json([
                'sucesso' => true,
                'dados' => $aluno
            ]);
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Aluno(a) não encontrado(a).') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Aluno não encontrado.'
                ], 404);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível consultar o aluno.'
            ], 500);
        }
    }

    public function historicoV1(int $id): void
    {
        try {
            $aluno = $this->service()->buscar($id);
            $this->exigirAcessoLeitura($aluno);
            $stmt = Database::connect()->prepare("
                SELECT DISTINCT
                    p.cd_partida,
                    p.status,
                    p.inicio_agendado,
                    ip.inicio_em,
                    ip.fim_em,
                    ip.local_partida,
                    c.cd_competicao,
                    c.nm_competicao,
                    t.cd_time,
                    t.nm_time,
                    pp.origem,
                    CASE WHEN cf.cd_vencedor = pp.cd_time THEN 'VITORIA'
                         WHEN cf.cd_vencedor IS NULL THEN NULL
                         ELSE 'DERROTA' END AS resultado
                FROM participante_partida pp
                JOIN partida p ON p.cd_partida = pp.cd_partida
                LEFT JOIN info_partida ip ON ip.cd_partida = p.cd_partida
                LEFT JOIN competicao c ON c.cd_competicao = p.cd_competicao
                LEFT JOIN confronto cf ON cf.cd_confronto = p.cd_confronto
                LEFT JOIN time t ON t.cd_time = pp.cd_time
                WHERE pp.cd_usuario = :usuario
                ORDER BY p.inicio_agendado DESC NULLS LAST, p.cd_partida DESC
            ");
            $stmt->execute([':usuario' => (int) $aluno['cd_usuario']]);
            Response::success($stmt->fetchAll(PDO::FETCH_ASSOC), 'Histórico esportivo consultado.');
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Aluno(a) não encontrado(a).') {
                Response::error('NOT_FOUND', 'Aluno não encontrado.', 404);
            }
            if (str_contains(mb_strtolower($e->getMessage()), 'permissão')) {
                Response::error('FORBIDDEN', 'Sem permissão para consultar o histórico.', 403);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar o histórico.', 500);
        }
    }

    public function estatisticasV1(int $id): void
    {
        try {
            $aluno = $this->service()->buscar($id);
            $this->exigirAcessoLeitura($aluno);
            $stmt = Database::connect()->prepare("
                SELECT
                    COUNT(DISTINCT p.cd_partida) FILTER (WHERE p.status = 'FINALIZADA') AS partidas,
                    COUNT(DISTINCT p.cd_partida) FILTER (
                        WHERE p.status = 'FINALIZADA' AND cf.cd_vencedor = pp.cd_time
                    ) AS vitorias,
                    COUNT(DISTINCT p.cd_partida) FILTER (
                        WHERE p.status = 'FINALIZADA' AND cf.cd_vencedor IS NOT NULL
                          AND cf.cd_vencedor <> pp.cd_time
                    ) AS derrotas
                FROM participante_partida pp
                JOIN partida p ON p.cd_partida = pp.cd_partida
                LEFT JOIN confronto cf ON cf.cd_confronto = p.cd_confronto
                WHERE pp.cd_usuario = :usuario
            ");
            $stmt->execute([':usuario' => (int) $aluno['cd_usuario']]);
            Response::success($stmt->fetch(PDO::FETCH_ASSOC) ?: [], 'Estatísticas consultadas.');
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Aluno(a) não encontrado(a).') {
                Response::error('NOT_FOUND', 'Aluno não encontrado.', 404);
            }
            if (str_contains(mb_strtolower($e->getMessage()), 'permissão')) {
                Response::error('FORBIDDEN', 'Sem permissão para consultar as estatísticas.', 403);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar as estatísticas.', 500);
        }
    }

    public function rankingIndividualV1(): void
    {
        Response::error(
            'NOT_IMPLEMENTED',
            'O ACE não registra pontuação individual por atleta; o ranking individual depende de uma regra esportiva aprovada.',
            501
        );
    }

    private function filtrosListaV1(): array
    {
        $idUsuario = AuthContext::id() ?? 0;
        $papeisGestores = ['DIR', 'CRD'];
        if (Permissoes::temPapel('ADM')) {
            $escolas = null;
        } elseif (array_intersect(AuthContext::papeis(), $papeisGestores) !== []) {
            $escolas = (new VinculoUsuarioEscola())->escolasAtivasPorPapeis(
                $idUsuario,
                array_values(array_intersect(AuthContext::papeis(), $papeisGestores))
            );
        } else {
            Response::error('FORBIDDEN', 'Sem permissão para listar alunos.', 403);
        }

        $busca = $_GET['busca'] ?? '';
        if (!is_string($busca)) {
            Response::error('VALIDATION_ERROR', 'Parâmetro busca inválido.', 400);
        }

        $pagina = min(100000, max(1, (int) ($_GET['pagina'] ?? $_GET['page'] ?? 1)));
        $porPagina = min(100, max(1, (int) ($_GET['limite'] ?? $_GET['per_page'] ?? 30)));
        return [
            'nome' => trim($busca),
            'ordem' => 'ASC',
            'limite' => $porPagina + 1,
            'offset' => ($pagina - 1) * $porPagina,
            'escolas' => $escolas,
        ];
    }

    private function exigirAcessoLeitura(array $aluno): void
    {
        $idUsuarioAtual = AuthContext::id() ?? 0;
        $idUsuarioAluno = (int) ($aluno['cd_usuario'] ?? 0);
        if (Permissoes::temPapel('ADM') || $idUsuarioAtual === $idUsuarioAluno) {
            return;
        }

        $idAluno = (int) ($aluno['cd_aluno'] ?? 0);
        if (array_intersect(AuthContext::papeis(), ['DIR', 'CRD']) !== []) {
            $escolasGeridas = (new VinculoUsuarioEscola())->escolasAtivasPorPapeis(
                $idUsuarioAtual,
                array_values(array_intersect(AuthContext::papeis(), ['DIR', 'CRD']))
            );
            $escolasAluno = $this->service()->obterEscolasDoAluno($idAluno);
            if (array_intersect($escolasGeridas, $escolasAluno) !== []) {
                return;
            }
        }

        $stmt = Database::connect()->prepare("
            SELECT EXISTS (
                SELECT 1 FROM vinculo_tecnico_time vt
                JOIN vinculo_time_integrante vi ON vi.cd_time = vt.cd_time
                WHERE vt.cd_usuario = :tecnico AND vt.ativo = TRUE
                  AND vi.cd_usuario = :aluno AND vi.ativo = TRUE
            ) OR EXISTS (
                SELECT 1 FROM responsavel r
                JOIN vinculo_time_responsavel vr ON vr.cd_responsavel = r.cd_responsavel
                JOIN vinculo_time_integrante vi ON vi.cd_time = vr.cd_time
                WHERE r.cd_usuario = :responsavel AND r.ativo = TRUE
                  AND vr.ativo = TRUE AND vi.cd_usuario = :aluno_responsavel AND vi.ativo = TRUE
            )
        ");
        $stmt->execute([
            ':tecnico' => $idUsuarioAtual,
            ':aluno' => $idUsuarioAluno,
            ':responsavel' => $idUsuarioAtual,
            ':aluno_responsavel' => $idUsuarioAluno,
        ]);
        if ((bool) $stmt->fetchColumn()) {
            return;
        }

        throw new DomainException('Sem permissão para consultar este aluno.');
    }

    public function store(): void
    {
        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para cadastrar alunos.'
            ], 403);
        }

        $contentType = strtolower(
            $_SERVER['CONTENT_TYPE'] ?? ''
        );

        if (str_contains($contentType, 'application/json')) {
            $corpo = file_get_contents('php://input');
            $dados = json_decode($corpo, true);

            if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'O corpo da requisição contém JSON inválido.'
                ], 400);
            }
        } else {
            // Aceita multipart/form-data e application/x-www-form-urlencoded.
            $dados = $_POST;
        }

        try {
            $this->service()->cadastrar($dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Aluno cadastrado com sucesso.'
            ], 201);
        } catch (InvalidArgumentException $e) {
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 422);
        } catch (PDOException $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível cadastrar o aluno.'
            ], 500);
        } catch (Exception $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
                || str_contains(mb_strtolower($mensagem), 'somente diretor')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            // Regras de negócio e validações do serviço.
            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Ocorreu um erro interno ao cadastrar o aluno.'
            ], 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do aluno inválido.'
            ], 400);
        }

        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para editar alunos.'
            ], 403);
        }

        $contentType = strtolower(
            $_SERVER['CONTENT_TYPE'] ?? ''
        );

        if (str_contains($contentType, 'application/json')) {
            $corpo = file_get_contents('php://input');
            $dados = json_decode($corpo, true);

            if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'O corpo da requisição contém JSON inválido.'
                ], 400);
            }
        } else {
            $dados = $_POST;
        }

        try {
            // O serviço confirma que o diretor gerencia ao menos uma escola do aluno.
            $this->service()->atualizar($id, $dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Aluno atualizado com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Aluno não encontrado.') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 404);
            }

            if (
                str_contains(
                    mb_strtolower($mensagem),
                    'permissão'
                )
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            if ($e instanceof InvalidArgumentException) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 422);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível atualizar o aluno.'
            ], 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do aluno inválido.'
            ], 400);
        }

        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para excluir alunos.'
            ], 403);
        }

        try {
            $idEscola = isset($_GET['escola']) ? (int) $_GET['escola'] : null;
            $this->service()->remover($id, $idEscola);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Aluno excluído com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($e instanceof InvalidArgumentException) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 422);
            }

            if ($mensagem === 'Aluno não encontrado.') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 404);
            }

            if (
                str_contains(
                    mb_strtolower($mensagem),
                    'permissão'
                )
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível excluir o aluno.'
            ], 500);
        }
    }

}
