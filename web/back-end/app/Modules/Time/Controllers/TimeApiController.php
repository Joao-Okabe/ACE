<?php

class TimeApiController
{
    private ?TimeService $service = null;

    private function service(): TimeService
    {
        if ($this->service === null) {
            $this->service = new TimeService();
        }

        return $this->service;
    }

    public function rankingV1(int $id): void
    {
        Response::error(
            'NOT_IMPLEMENTED',
            'O ACE não possui uma regra consolidada de ranking de equipes para esta consulta.',
            501
        );
    }

    public function rankingEquipesV1(): void
    {
        Response::error(
            'NOT_IMPLEMENTED',
            'O ACE não possui uma regra consolidada de ranking global de equipes.',
            501
        );
    }

    public function index(): void
    {
        try {
            $filtros = [
                'nome' => trim($_GET['nome'] ?? ''),
                'categoria' => trim($_GET['categoria'] ?? ''),
                'ordem' => strtolower($_GET['ordem'] ?? 'asc'),
            ];

            if (!in_array($filtros['ordem'], ['asc', 'desc'], true)) {
                $filtros['ordem'] = 'asc';
            }

            $times = $this->service()->listar($filtros);

            Response::json([
                'sucesso' => true,
                'dados' => $times
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível listar os times.'
            ], 500);
        }
    }

    public function indexV1(): void
    {
        $busca = $_GET['busca'] ?? $_GET['nome'] ?? '';
        if (!is_string($busca)) {
            Response::error('VALIDATION_ERROR', 'Parâmetro busca inválido.', 400);
        }
        $pagina = min(100000, max(1, (int) ($_GET['pagina'] ?? 1)));
        $limite = min(100, max(1, (int) ($_GET['limite'] ?? 30)));
        $offset = ($pagina - 1) * $limite;
        try {
            $pdo = Database::connect();
            $where = ['t.ativo = TRUE'];
            $params = [];
            if (trim($busca) !== '') {
                $where[] = 't.nm_time ILIKE :busca';
                $params[':busca'] = '%' . trim($busca) . '%';
            }
            if (isset($_GET['escolaId'])) {
                $idEscola = filter_var($_GET['escolaId'], FILTER_VALIDATE_INT);
                if ($idEscola === false || $idEscola <= 0) {
                    Response::error('VALIDATION_ERROR', 'Parâmetro escolaId inválido.', 400);
                }
                $where[] = 'EXISTS (SELECT 1 FROM vinculo_time_escola vte WHERE vte.cd_time = t.cd_time AND vte.cd_escola = :escola AND vte.ativo = TRUE)';
                $params[':escola'] = $idEscola;
            }
            if (!Permissoes::temPapel('ADM') && Permissoes::temPapel('DIR')) {
                $escolas = (new VinculoUsuarioEscola())->escolasAtivasPorPapeis(AuthContext::id() ?? 0, ['DIR']);
                if ($escolas === []) {
                    Response::success(['pagina' => $pagina, 'limite' => $limite, 'itens' => [], 'tem_proxima' => false], 'Times listados.');
                }
                $marks = [];
                foreach ($escolas as $index => $idEscola) {
                    $mark = ':escola_scope_' . $index;
                    $marks[] = $mark;
                    $params[$mark] = (int) $idEscola;
                }
                $where[] = 'EXISTS (SELECT 1 FROM vinculo_time_escola vte WHERE vte.cd_time = t.cd_time AND vte.ativo = TRUE AND vte.cd_escola IN (' . implode(',', $marks) . '))';
            }
            $sql = 'SELECT t.cd_time, t.nm_time, t.cd_esporte, t.path_escudo, t.ativo FROM time t WHERE ' . implode(' AND ', $where) . ' ORDER BY t.nm_time LIMIT :limite OFFSET :offset';
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':limite', $limite + 1, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $temProxima = count($itens) > $limite;
            if ($temProxima) {
                array_pop($itens);
            }
            Response::success(['pagina' => $pagina, 'limite' => $limite, 'itens' => $itens, 'tem_proxima' => $temProxima], 'Times listados.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível listar os times.', 500);
        }
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        try {
            $time = $this->service()->buscar($id);

            Response::json([
                'sucesso' => true,
                'dados' => $time
            ]);
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Time não encontrado.') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Time não encontrado.'
                ], 404);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível consultar o time.'
            ], 500);
        }
    }

    public function showV1(int $id): void
    {
        if ($id <= 0) {
            Response::error('VALIDATION_ERROR', 'ID do time inválido.', 400);
        }
        try {
            $time = $this->service()->buscar($id);
            $stmt = Database::connect()->prepare("
                SELECT a.cd_aluno AS aluno_id, vti.cd_usuario, u.nm_usuario AS nome,
                       vti.cd_funcao_integrante, fi.nm_funcao AS posicao,
                       vti.numero_camisa, vti.capitao, COALESCE(et.titular, FALSE) AS titular,
                       u.path_ft_usuario AS foto_perfil
                FROM vinculo_time_integrante vti
                JOIN usuario u ON u.cd_usuario = vti.cd_usuario
                LEFT JOIN aluno a ON a.cd_usuario = vti.cd_usuario
                LEFT JOIN funcao_integrante fi ON fi.cd_funcao_integrante = vti.cd_funcao_integrante
                LEFT JOIN escalacao_time et ON et.cd_vinculo_time_integrante = vti.cd_vinculo_time_integrante AND et.ativo = TRUE
                WHERE vti.cd_time = :time AND vti.ativo = TRUE
                ORDER BY u.nm_usuario
            ");
            $stmt->execute([':time' => $id]);
            $time['id'] = (int) $time['cd_time'];
            $time['nome'] = $time['nm_time'];
            $time['atletas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success($time, 'Time consultado.');
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Time não encontrado.') {
                Response::error('NOT_FOUND', 'Time não encontrado.', 404);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar o time.', 500);
        }
    }

    public function storeV1(): void
    {
        $dados = $this->lerJsonV1();
        $dados['nm_time'] = $dados['nm_time'] ?? $dados['nome'] ?? '';
        $dados['escola'] = $dados['escola'] ?? $dados['escolaId'] ?? $dados['cd_escola'] ?? null;
        $dados['cd_esporte'] = $dados['cd_esporte'] ?? $dados['esporteId'] ?? null;
        if (empty($dados['cd_esporte'])) {
            Response::error('VALIDATION_ERROR', 'O ACE exige esporteId numérico; modalidade e categoria não determinam o esporte no schema atual.', 422);
        }
        try {
            $this->service()->cadastrar($dados + ['path_escudo' => null]);
            Response::success(null, 'Time cadastrado com sucesso.', 201);
        } catch (Throwable $e) {
            $status = str_contains(mb_strtolower($e->getMessage()), 'permissão') ? 403 : 422;
            Response::error($status === 403 ? 'FORBIDDEN' : 'VALIDATION_ERROR', $e->getMessage(), $status);
        }
    }

    public function updateV1(int $id): void
    {
        $dados = $this->lerJsonV1();
        $dados['nm_time'] = $dados['nm_time'] ?? $dados['nome'] ?? '';
        $dados['path_escudo'] = null;
        $this->updateComDadosV1($id, $dados);
    }

    private function updateComDadosV1(int $id, array $dados): void
    {
        try {
            if (!Permissoes::temPapel('ADM')) {
                (new VinculoTimeService())->exigirPermissaoGerenciarTime($id);
            }
            $this->service()->atualizar($id, $dados);
            Response::success(null, 'Time atualizado.');
        } catch (Throwable $e) {
            $status = $e->getMessage() === 'Time não encontrado.' ? 404
                : (str_contains(mb_strtolower($e->getMessage()), 'permissão') ? 403 : 422);
            Response::error(match ($status) { 404 => 'NOT_FOUND', 403 => 'FORBIDDEN', default => 'VALIDATION_ERROR' }, $e->getMessage(), $status);
        }
    }

    private function lerJsonV1(): array
    {
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
            Response::error('BAD_REQUEST', 'O corpo contém JSON inválido.', 400);
        }
        return $dados;
    }

    public function store(): void
    {
        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
            && !Permissoes::temPapel('CRD')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para cadastrar times.'
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

            $dados['path_escudo'] = null;
        } else {
            // Aceita multipart/form-data (para envio do escudo)
            // e application/x-www-form-urlencoded.
            $dados = $_POST;
            $dados['path_escudo'] = $_FILES['path_escudo'] ?? null;
        }

        try {
            $this->service()->cadastrar($dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Time cadastrado com sucesso.'
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
                'mensagem' => 'Não foi possível cadastrar o time.'
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
                'mensagem' => 'Ocorreu um erro interno ao cadastrar o time.'
            ], 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
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

            $dados['path_escudo'] = null;
        } else {
            [$dados, $arquivos] = request_parse_body();
            $dados['path_escudo'] = $arquivos['path_escudo'] ?? null;
        }

        try {
            if (!Permissoes::temPapel('ADM')) {
                (new VinculoTimeService())->exigirPermissaoGerenciarTime($id);
            }

            $this->service()->atualizar($id, $dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Time atualizado com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Time não encontrado.') {
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
                'mensagem' => 'Não foi possível atualizar o time.'
            ], 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        try {
            if (!Permissoes::temPapel('ADM')) {
                (new VinculoTimeService())->exigirPermissaoGerenciarTime($id);
            }

            $this->service()->remover($id);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Time excluído com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Time não encontrado.') {
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
                'mensagem' => 'Não foi possível excluir o time.'
            ], 500);
        }
    }
}
