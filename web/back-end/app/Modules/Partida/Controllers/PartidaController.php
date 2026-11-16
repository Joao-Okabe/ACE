<?php

class PartidaController
{
    private ?PartidaService $service = null;

    private function service(): PartidaService
    {
        if ($this->service === null) {
            $this->service = new PartidaService();
        }

        return $this->service;
    }

    private function usuarioAtual(): int
    {
        $id = AuthContext::id() ?? 0;

        if ($id <= 0) {
            throw new RuntimeException('Sessão inválida. Faça login novamente.');
        }

        return $id;
    }

    public function create(): void
    {
        try {
            $idCompeticao = isset($_GET['id_competicao']) ? (int) $_GET['id_competicao'] : null;

            if ($idCompeticao) {
                $this->service()->exigirAcessoCompeticao($this->usuarioAtual(), $idCompeticao);
            }

            $esporteService = new EsporteService();
            $modalidadeService = new ModalidadeService();
            $timeService = new TimeService();

            renderView('partida/criar', [
                'idCompeticao' => $idCompeticao,
                'esportes' => $esporteService->listar(),
                'modalidades' => $modalidadeService->listar(),
                'times' => $timeService->listar(),
            ]);
        } catch (Throwable $e) {
            http_response_code(403);
            renderView('erro', ['erro' => $e->getMessage()]);
        }
    }

    public function store(): void
    {
        try {
            $dados = $_POST;

            // Campos opcionais: sem competição = partida avulsa;
            // sem confronto = partida independente dentro da competição.
            $idPartida = $this->service()->criar($dados);

            $idCompeticao = (int) ($dados['cd_competicao'] ?? 0);

            if ($idCompeticao > 0) {
                header("Location: /competicoes/visualizar?id={$idCompeticao}&partida_criada=1");
            } else {
                header("Location: /partidas/visualizar?id={$idPartida}&partida_criada=1");
            }
            exit;
        } catch (Throwable $e) {
            http_response_code(422);
            renderView('partida/criar', [
                'erro' => $e->getMessage(),
                'dados' => $_POST,
            ]);
        }
    }

    public function show(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $partida = $this->service()->buscar($id, $this->usuarioAtual());

            renderView('partida/visualizar', ['partida' => $partida]);
        } catch (Throwable $e) {
            http_response_code(404);
            renderView('erro', ['erro' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filtros = [
                'cd_competicao' => isset($_GET['cd_competicao']) ? (int) $_GET['cd_competicao'] : null,
                'status' => trim($_GET['status'] ?? ''),
                'visibilidade' => trim($_GET['visibilidade'] ?? ''),
            ];

            $partidas = $this->service()->listar($filtros, $this->usuarioAtual());

            renderView('partida/listar', [
                'partidas' => $partidas,
                'filtros' => $filtros,
            ]);
        } catch (Throwable $e) {
            http_response_code(403);
            renderView('erro', ['erro' => $e->getMessage()]);
        }
    }

    public function calendarioV1(): void
    {
        $filtros = [
            'cd_competicao' => isset($_GET['competicaoId']) ? (int) $_GET['competicaoId'] : null,
            'cd_time' => isset($_GET['timeId']) ? (int) $_GET['timeId'] : null,
            'de' => isset($_GET['de']) && is_string($_GET['de']) ? $_GET['de'] : null,
            'ate' => isset($_GET['ate']) && is_string($_GET['ate']) ? $_GET['ate'] : null,
            'local' => isset($_GET['local']) && is_string($_GET['local']) ? $_GET['local'] : null,
            'limite' => min(100, max(1, (int) ($_GET['limite'] ?? 30))),
        ];
        if (isset($_GET['localId']) && !isset($_GET['local'])) {
            Response::error('NOT_IMPLEMENTED', 'O ACE usa o local como texto sem ID próprio; envie local como texto.', 501);
        }
        try {
            $itens = $this->service()->listar($filtros, $this->usuarioAtual());
            Response::success([
                'pagina' => max(1, (int) ($_GET['pagina'] ?? 1)),
                'limite' => $filtros['limite'],
                'itens' => $itens,
            ], 'Calendário consultado.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar o calendário.', 500);
        }
    }

    public function partidasAoVivoV1(): void
    {
        $this->listarPorStatusV1('EM_ANDAMENTO', 'Partidas ao vivo consultadas.');
    }

    public function historicoV1(): void
    {
        $this->listarPorStatusV1('FINALIZADA', 'Histórico de partidas consultado.');
    }

    public function aoVivoV1(int $id): void
    {
        try {
            Response::success(
                $this->service()->buscar($id, $this->usuarioAtual()),
                'Partida consultada.'
            );
        } catch (Throwable $e) {
            Response::error('NOT_FOUND', 'Partida não encontrada.', 404);
        }
    }

    public function locaisV1(): void
    {
        try {
            $stmt = Database::connect()->query("
                SELECT DISTINCT local_partida AS nome
                FROM info_partida
                WHERE NULLIF(BTRIM(local_partida), '') IS NOT NULL
                ORDER BY nome
            ");
            Response::success($stmt->fetchAll(PDO::FETCH_ASSOC), 'Locais consultados.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar os locais.', 500);
        }
    }

    public function agendarV1(int $id): void
    {
        $dados = $this->lerJsonV1();
        $inicio = $dados['inicio_agendado'] ?? $dados['inicio'] ?? $dados['dataHora'] ?? null;
        if (!is_string($inicio) || trim($inicio) === '') {
            Response::error('VALIDATION_ERROR', 'Informe a data e hora da partida.', 400);
        }
        $this->alterarPartidaV1($id, function (PDO $pdo) use ($id, $inicio): void {
            $stmt = $pdo->prepare('UPDATE partida SET inicio_agendado = :inicio WHERE cd_partida = :id');
            $stmt->execute([':inicio' => $inicio, ':id' => $id]);
            $stmt = $pdo->prepare("
                INSERT INTO info_partida (cd_partida, inicio_em)
                VALUES (:id, :inicio) ON CONFLICT (cd_partida)
                DO UPDATE SET inicio_em = EXCLUDED.inicio_em
            ");
            $stmt->execute([':id' => $id, ':inicio' => $inicio]);
        }, 'Calendário da partida atualizado.');
    }

    public function atribuirLocalV1(int $id): void
    {
        $dados = $this->lerJsonV1();
        $local = $dados['local_partida'] ?? $dados['local'] ?? $dados['nome'] ?? null;
        if (!is_string($local) || trim($local) === '' || mb_strlen($local) > 150) {
            Response::error('VALIDATION_ERROR', 'Informe um local com até 150 caracteres.', 400);
        }
        $this->alterarPartidaV1($id, function (PDO $pdo) use ($id, $local): void {
            $stmt = $pdo->prepare("
                INSERT INTO info_partida (cd_partida, local_partida)
                VALUES (:id, :local) ON CONFLICT (cd_partida)
                DO UPDATE SET local_partida = EXCLUDED.local_partida
            ");
            $stmt->execute([':id' => $id, ':local' => trim($local)]);
        }, 'Local da partida atualizado.');
    }

    public function iniciarV1(int $id): void
    {
        $this->alterarPartidaV1($id, function (PDO $pdo) use ($id): void {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE partida SET status = 'EM_ANDAMENTO' WHERE cd_partida = :id AND status = 'AGENDADA'");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() !== 1) {
                $pdo->rollBack();
                throw new DomainException('Somente partidas agendadas podem ser iniciadas.');
            }
            $stmt = $pdo->prepare("
                INSERT INTO info_partida (cd_partida, inicio_em)
                VALUES (:id, CURRENT_TIMESTAMP) ON CONFLICT (cd_partida)
                DO UPDATE SET inicio_em = COALESCE(info_partida.inicio_em, EXCLUDED.inicio_em)
            ");
            $stmt->execute([':id' => $id]);
            $pdo->commit();
        }, 'Partida iniciada.');
    }

    public function operacaoSemSuporteV1(int $id): void
    {
        Response::error(
            'NOT_IMPLEMENTED',
            'O ACE ainda não possui estrutura ou regra aprovada para registrar esta operação de partida.',
            501
        );
    }

    public function registrarResultadoV1(int $id): void
    {
        $dados = $this->lerJsonV1();
        $placarCasa = filter_var($dados['placarCasa'] ?? null, FILTER_VALIDATE_INT);
        $placarVisitante = filter_var($dados['placarVisitante'] ?? null, FILTER_VALIDATE_INT);
        if ($placarCasa === false || $placarVisitante === false || $placarCasa < 0 || $placarVisitante < 0) {
            Response::error('VALIDATION_ERROR', 'Informe dois placares inteiros não negativos.', 400);
        }
        $this->alterarPartidaV1($id, function (PDO $pdo) use ($id, $placarCasa, $placarVisitante): void {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT cd_time, mandante FROM vinculo_partida_time WHERE cd_partida = :id ORDER BY mandante DESC, cd_time');
            $stmt->execute([':id' => $id]);
            $times = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($times) !== 2) {
                $pdo->rollBack();
                throw new DomainException('A partida precisa ter exatamente dois times para registrar o resultado.');
            }
            $stmt = $pdo->prepare("
                INSERT INTO etapa_partida (cd_partida, nr_etapa, inicio_em, fim_em)
                VALUES (:partida, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON CONFLICT (cd_partida, nr_etapa)
                DO UPDATE SET fim_em = EXCLUDED.fim_em
                RETURNING cd_etapa_partida
            ");
            $stmt->execute([':partida' => $id]);
            $idEtapa = (int) $stmt->fetchColumn();
            $stmtPonto = $pdo->prepare("
                INSERT INTO resultado_etapa_partida (cd_etapa_partida, cd_time, pontuacao)
                VALUES (:etapa, :time, :pontuacao)
                ON CONFLICT (cd_etapa_partida, cd_time)
                DO UPDATE SET pontuacao = EXCLUDED.pontuacao
            ");
            foreach ([[$times[0], $placarCasa], [$times[1], $placarVisitante]] as [$time, $pontos]) {
                $stmtPonto->execute([
                    ':etapa' => $idEtapa,
                    ':time' => (int) $time['cd_time'],
                    ':pontuacao' => $pontos,
                ]);
            }
            $pdo->prepare("UPDATE partida SET status = 'FINALIZADA' WHERE cd_partida = :id")
                ->execute([':id' => $id]);
            $stmt = $pdo->prepare('INSERT INTO info_partida (cd_partida, fim_em) VALUES (:id, CURRENT_TIMESTAMP) ON CONFLICT (cd_partida) DO UPDATE SET fim_em = EXCLUDED.fim_em');
            $stmt->execute([':id' => $id]);
            if ($placarCasa !== $placarVisitante) {
                $vencedor = (int) $times[$placarCasa > $placarVisitante ? 0 : 1]['cd_time'];
                $stmt = $pdo->prepare("UPDATE confronto SET cd_vencedor = :vencedor, status = 'FINALIZADO' WHERE cd_confronto = (SELECT cd_confronto FROM partida WHERE cd_partida = :id)");
                $stmt->execute([':vencedor' => $vencedor, ':id' => $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE confronto SET cd_vencedor = NULL, status = 'FINALIZADO' WHERE cd_confronto = (SELECT cd_confronto FROM partida WHERE cd_partida = :id)");
                $stmt->execute([':id' => $id]);
            }
            $pdo->commit();
        }, 'Resultado registrado e partida finalizada.');
    }

    public function registrarPenalidadeV1(int $id): void
    {
        $dados = $this->lerJsonV1();
        $idTime = (int) ($dados['timeId'] ?? $dados['cd_time'] ?? 0);
        $idAluno = (int) ($dados['alunoId'] ?? $dados['cd_aluno'] ?? 0);
        $tipo = $dados['tipo'] ?? '';
        if ($idTime <= 0 || $idAluno <= 0 || !is_string($tipo) || trim($tipo) === '') {
            Response::error('VALIDATION_ERROR', 'Informe timeId, alunoId e tipo da penalidade.', 400);
        }
        $stmt = Database::connect()->prepare('SELECT cd_usuario FROM aluno WHERE cd_aluno = :id');
        $stmt->execute([':id' => $idAluno]);
        $idUsuario = $stmt->fetchColumn();
        if ($idUsuario === false) {
            Response::error('NOT_FOUND', 'Atleta não encontrado.', 404);
        }
        $this->alterarPartidaV1($id, function (PDO $pdo) use ($id, $idTime, $idUsuario, $tipo, $dados): void {
            $partida = $pdo->prepare('SELECT cd_competicao FROM partida WHERE cd_partida = :id');
            $partida->execute([':id' => $id]);
            $idCompeticao = $partida->fetchColumn();
            if ($idCompeticao === false || $idCompeticao === null) {
                throw new DomainException('Penalidades ACE só podem ser vinculadas a partidas de competição.');
            }
            $stmt = $pdo->prepare('SELECT cd_funcao_integrante FROM vinculo_time_integrante WHERE cd_time = :time AND cd_usuario = :usuario AND ativo = TRUE');
            $stmt->execute([':time' => $idTime, ':usuario' => (int) $idUsuario]);
            $funcao = $stmt->fetchColumn();
            if ($funcao === false) {
                throw new DomainException('O atleta não está vinculado ao time informado.');
            }
            $stmt = $pdo->prepare('SELECT cd_tipo_penalidade FROM tipo_penalidade WHERE LOWER(nm_tipo_penalidade) = LOWER(:tipo)');
            $stmt->execute([':tipo' => trim($tipo)]);
            $tipoId = $stmt->fetchColumn();
            if ($tipoId === false) {
                throw new DomainException('O tipo de penalidade não está cadastrado no ACE.');
            }
            $stmt = $pdo->prepare('INSERT INTO penalidade (cd_competicao, cd_time, cd_usuario, cd_funcao_integrante, cd_tipo_penalidade, descricao) VALUES (:competicao, :time, :usuario, :funcao, :tipo, :descricao)');
            $stmt->execute([
                ':competicao' => (int) $idCompeticao,
                ':time' => $idTime,
                ':usuario' => (int) $idUsuario,
                ':funcao' => (int) $funcao,
                ':tipo' => (int) $tipoId,
                ':descricao' => isset($dados['observacao']) && is_string($dados['observacao']) ? trim($dados['observacao']) : null,
            ]);
        }, 'Penalidade registrada.');
    }

    public function publicarResultadoV1(int $id): void
    {
        $this->alterarPartidaV1($id, function (PDO $pdo) use ($id): void {
            $stmt = $pdo->prepare("UPDATE partida SET visibilidade = 'PUBLICA' WHERE cd_partida = :id AND status = 'FINALIZADA'");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() !== 1) {
                throw new DomainException('Finalize a partida antes de publicar o resultado.');
            }
        }, 'Resultado publicado.');
    }

    public function criarLocalNaoSuportadoV1(): void
    {
        Response::error(
            'NOT_IMPLEMENTED',
            'O ACE guarda o local como texto da partida e não possui cadastro separado de locais.',
            501
        );
    }

    private function listarPorStatusV1(string $status, string $mensagem): void
    {
        try {
            $itens = $this->service()->listar([
                'status' => $status,
                'limite' => min(100, max(1, (int) ($_GET['limite'] ?? 30))),
            ], $this->usuarioAtual());
            Response::success($itens, $mensagem);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar as partidas.', 500);
        }
    }

    private function alterarPartidaV1(int $id, callable $operacao, string $mensagem): void
    {
        $usuario = $this->usuarioAtual();
        $pdo = Database::connect();
        try {
            $stmt = $pdo->prepare('SELECT * FROM partida WHERE cd_partida = :id');
            $stmt->execute([':id' => $id]);
            $partida = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$partida) {
                Response::error('NOT_FOUND', 'Partida não encontrada.', 404);
            }
            if (!Permissoes::temPapel('ADM') && (int) $partida['cd_criador'] !== $usuario) {
                if (empty($partida['cd_competicao'])) {
                    Response::error('FORBIDDEN', 'Sem permissão para alterar esta partida.', 403);
                }
                $competicao = (new Competicao())->buscar((int) $partida['cd_competicao']);
                if (!$competicao) {
                    Response::error('NOT_FOUND', 'Competição não encontrada.', 404);
                }
                (new CompeticaoService())->exigirPermissao($competicao);
            }
            $operacao($pdo);
            Response::success(null, $mensagem);
        } catch (DomainException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Response::error('CONFLICT', $e->getMessage(), 409);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if (str_contains(mb_strtolower($e->getMessage()), 'permissão')) {
                Response::error('FORBIDDEN', $e->getMessage(), 403);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível alterar a partida.', 500);
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
}
