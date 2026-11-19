<?php

class PartidaService
{
    private PDO $pdo;
    private Partida $partidaModel;

    public function __construct(?PDO $pdo = null, ?Partida $partidaModel = null)
    {
        $this->pdo = $pdo ?? Database::connect();
        $this->partidaModel = $partidaModel ?? new Partida();
    }

    public function criar(array $dados): int
    {
        $idCriador = AuthContext::id() ?? 0;
        if ($idCriador <= 0) {
            throw new RuntimeException('Usuário não autenticado.');
        }

        $idCompeticao = $this->normalizarIdOpcional($dados['cd_competicao'] ?? null, 'competição');

        $idConfronto = $this->normalizarIdOpcional($dados['cd_confronto'] ?? null, 'confronto');
        $valorVisibilidade = $dados['visibilidade'] ?? 'PUBLICA';
        if (!is_string($valorVisibilidade)) {
            throw new InvalidArgumentException('Visibilidade inválida.');
        }
        $visibilidade = strtoupper(trim($valorVisibilidade));

        if (!in_array($visibilidade, ['PUBLICA', 'PRIVADA'], true)) {
            throw new InvalidArgumentException('Visibilidade inválida.');
        }

        $idEsporte = $this->normalizarIdOpcional($dados['cd_esporte'] ?? null, 'esporte');
        $idModalidade = $this->normalizarIdOpcional($dados['cd_modalidade'] ?? null, 'modalidade');

        $inicio = $this->normalizarDataHora($dados['inicio_em'] ?? null);
        $valorData = $dados['dt_partida'] ?? '';
        if (!is_string($valorData)) {
            throw new InvalidArgumentException('Informe uma data válida para a partida.');
        }
        $data = trim($valorData);

        $valorLocal = $dados['local_partida'] ?? '';
        if (!is_string($valorLocal)) {
            throw new InvalidArgumentException('Informe o local da partida.');
        }
        $local = trim($valorLocal);

        if ($data === '' || !$this->dataValida($data)) {
            throw new InvalidArgumentException('Informe uma data válida para a partida.');
        }

        if ($local === '') {
            throw new InvalidArgumentException('Informe o local da partida.');
        }

        $idsTimes = $this->normalizarTimes($dados['cd_times'] ?? []);
        $observacoes = $dados['observacoes'] ?? null;
        if ($observacoes !== null && !is_string($observacoes)) {
            throw new InvalidArgumentException('Observações inválidas.');
        }

        try {
            $this->pdo->beginTransaction();

            if ($idConfronto !== null) {
                if ($idCompeticao === null) {
                    throw new InvalidArgumentException('Uma partida vinculada a confronto precisa pertencer a uma competição.');
                }

                $confronto = $this->partidaModel->buscarConfronto($idConfronto);

                if (!$confronto) {
                    throw new DomainException('Confronto não encontrado.');
                }

                if ((int) $confronto['cd_competicao'] !== $idCompeticao) {
                    throw new DomainException('O confronto informado não pertence à competição selecionada.');
                }

                if ($confronto['status'] === 'CANCELADO' || $confronto['status'] === 'FINALIZADO') {
                    throw new DomainException('Não é possível criar uma partida para um confronto cancelado ou finalizado.');
                }

                if ($this->partidaModel->confrontoTemPartida($idConfronto)) {
                    throw new DomainException('Este confronto já possui uma partida.');
                }

                $idsTimes = $this->partidaModel->buscarTimesDefinidosConfronto($idConfronto);

                if (count($idsTimes) !== 2) {
                    throw new DomainException('O confronto ainda não possui dois participantes definidos. Confrontos com BYE não geram partidas.');
                }
            } elseif (count($idsTimes) !== 2) {
                throw new InvalidArgumentException('Informe exatamente duas equipes para uma partida sem confronto.');
            }

            if ($idCompeticao !== null) {
                $this->validarCompeticao($idCompeticao);
                $competicaoService = new CompeticaoService();
                $competicao = (new Competicao())->buscar($idCompeticao);
                if ($competicao === null) {
                    throw new DomainException('Competição não encontrada.');
                }
                $competicaoService->exigirPermissao($competicao);

                if (!$this->partidaModel->validarTimesCompeticao($idCompeticao, $idsTimes)) {
                    throw new DomainException('As duas equipes precisam estar inscritas na competição.');
                }
            } else {
                if (!$idEsporte || !$idModalidade) {
                    throw new InvalidArgumentException('Partidas avulsas precisam informar esporte e modalidade.');
                }

                $this->validarEsporteDosTimes($idsTimes, $idEsporte);

                foreach ($idsTimes as $idTime) {
                    if (!Permissoes::podeGerenciarTime($idTime)) {
                        throw new DomainException('Você não tem permissão para gerenciar as duas equipes desta partida avulsa.');
                    }
                }
            }

            $partida = [
                'cd_competicao' => $idCompeticao,
                'cd_confronto' => $idConfronto,
                'cd_criador' => $idCriador,
                'cd_esporte' => $idCompeticao === null ? $idEsporte : null,
                'cd_modalidade' => $idCompeticao === null ? $idModalidade : null,
                'visibilidade' => $visibilidade,
                'inicio_agendado' => $inicio,
            ];

            $idPartida = $this->partidaModel->criar($partida);
            $this->partidaModel->vincularTimes($idPartida, $idsTimes);

            $this->partidaModel->criarInfo($idPartida, [
                'dt_partida' => $data,
                'inicio_em' => $inicio,
                'local_partida' => $local,
                'observacoes' => trim($observacoes ?? '') ?: null,
            ]);

            $this->partidaModel->capturarParticipantes($idPartida, $idsTimes);

            if ($idCompeticao !== null) {
                $this->partidaModel->capturarParticipantesCompeticao($idCompeticao, $idsTimes);
            }

            $this->pdo->commit();

            return $idPartida;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function buscar(int $idPartida, int $idUsuario): array
    {
        if ($idPartida <= 0) {
            throw new InvalidArgumentException('Partida inválida.');
        }

        $partida = $this->partidaModel->buscar($idPartida);

        if (!$partida || !$this->partidaModel->usuarioPodeAcessar($idUsuario, $partida)) {
            throw new RuntimeException('Partida não encontrada ou sem permissão para visualizá-la.');
        }

        return $partida;
    }

    public function listar(array $filtros, int $idUsuario): array
    {
        return $this->partidaModel->listarVisiveis($idUsuario, $filtros);
    }

    public function exigirAcessoCompeticao(int $idUsuario, int $idCompeticao): void
    {
        $stmt = $this->pdo->prepare("
            SELECT cd_criador, visibilidade FROM competicao
            WHERE cd_competicao = :id
        ");
        $stmt->execute([':id' => $idCompeticao]);
        $competicao = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$competicao) {
            throw new RuntimeException('Competição não encontrada.');
        }

        if ($competicao['visibilidade'] === 'PRIVADA'
            && (int) $competicao['cd_criador'] !== $idUsuario
            && !$this->partidaModel->usuarioParticipaCompeticao($idUsuario, $idCompeticao)
            && !(new Competicao())->usuarioPodeVisualizar($idUsuario, $idCompeticao)) {
            throw new RuntimeException('Você não tem permissão para acessar esta competição.');
        }
    }

    private function validarCompeticao(int $idCompeticao): void
    {
        $stmt = $this->pdo->prepare("
            SELECT status FROM competicao WHERE cd_competicao = :id
        ");
        $stmt->execute([':id' => $idCompeticao]);
        $status = $stmt->fetchColumn();

        if ($status === false) {
            throw new DomainException('Competição não encontrada.');
        }

        if ($status === 'CANCELADA') {
            throw new DomainException('Não é possível criar partidas em uma competição cancelada.');
        }
    }

    private function validarEsporteDosTimes(array $idsTimes, int $idEsporte): void
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM time
            WHERE cd_time = :time AND cd_esporte = :esporte AND ativo = TRUE
        ");

        foreach ($idsTimes as $idTime) {
            $stmt->execute([':time' => $idTime, ':esporte' => $idEsporte]);
            if ((int) $stmt->fetchColumn() !== 1) {
                throw new DomainException('As equipes precisam ser ativas e pertencer ao esporte selecionado.');
            }
        }
    }

    private function normalizarTimes($times): array
    {
        if (!is_array($times)) {
            throw new InvalidArgumentException('Informe uma lista válida de equipes.');
        }

        $ids = [];
        foreach ($times as $time) {
            $id = filter_var($time, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                throw new InvalidArgumentException('ID de equipe inválido.');
            }
            $ids[] = $id;
        }

        return array_values(array_unique($ids));
    }

    private function normalizarIdOpcional($valor, string $campo): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $id = filter_var($valor, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            throw new InvalidArgumentException('ID de ' . $campo . ' inválido.');
        }

        return $id;
    }

    private function normalizarDataHora($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        if (!is_string($valor)) {
            throw new InvalidArgumentException('Horário de início inválido.');
        }

        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }

        $data = date_create_from_format('Y-m-d\TH:i', $valor)
            ?: date_create_from_format('Y-m-d H:i:s', $valor)
            ?: date_create_from_format('Y-m-d H:i', $valor);

        if (!$data) {
            throw new InvalidArgumentException('Horário de início inválido.');
        }

        return $data->format('Y-m-d H:i:s');
    }

    private function dataValida(string $data): bool
    {
        $obj = DateTime::createFromFormat('!Y-m-d', $data);
        return $obj && $obj->format('Y-m-d') === $data;
    }
}
