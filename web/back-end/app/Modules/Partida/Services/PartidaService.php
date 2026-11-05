<?php

class PartidaService
{
    private PDO $pdo;
    private Partida $partidaModel;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->partidaModel = new Partida();
    }

    public function criar(array $dados, int $idCriador): int
    {
        if ($idCriador <= 0) {
            throw new InvalidArgumentException('Criador inválido.');
        }

        $idCompeticao = !empty($dados['cd_competicao']) ? (int) $dados['cd_competicao'] : null;
        $idConfronto = !empty($dados['cd_confronto']) ? (int) $dados['cd_confronto'] : null;
        $visibilidade = strtoupper(trim($dados['visibilidade'] ?? 'PUBLICA'));

        if (!in_array($visibilidade, ['PUBLICA', 'PRIVADA'], true)) {
            throw new InvalidArgumentException('Visibilidade inválida.');
        }

        $idEsporte = !empty($dados['cd_esporte']) ? (int) $dados['cd_esporte'] : null;
        $idModalidade = !empty($dados['cd_modalidade']) ? (int) $dados['cd_modalidade'] : null;

        $inicio = $this->normalizarDataHora($dados['inicio_em'] ?? null);
        $data = trim($dados['dt_partida'] ?? '');
        $local = trim($dados['local_partida'] ?? '');

        if ($data === '' || !$this->dataValida($data)) {
            throw new InvalidArgumentException('Informe uma data válida para a partida.');
        }

        if ($local === '') {
            throw new InvalidArgumentException('Informe o local da partida.');
        }

        $idsTimes = $this->normalizarTimes($dados['cd_times'] ?? []);

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

                if (!$this->partidaModel->validarTimesCompeticao($idCompeticao, $idsTimes)) {
                    throw new DomainException('As duas equipes precisam estar inscritas na competição.');
                }
            } else {
                if (!$idEsporte || !$idModalidade) {
                    throw new InvalidArgumentException('Partidas avulsas precisam informar esporte e modalidade.');
                }

                $this->validarEsporteDosTimes($idsTimes, $idEsporte);
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
                'observacoes' => trim($dados['observacoes'] ?? '') ?: null,
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
            && !$this->partidaModel->usuarioParticipaCompeticao($idUsuario, $idCompeticao)) {
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
            return [];
        }

        $ids = array_values(array_unique(array_filter(
            array_map('intval', $times),
            static fn ($id) => $id > 0
        )));

        return $ids;
    }

    private function normalizarDataHora($valor): ?string
    {
        $valor = trim((string) $valor);
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
