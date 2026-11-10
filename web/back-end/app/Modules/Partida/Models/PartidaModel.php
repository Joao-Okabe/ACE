<?php

class Partida extends Model
{
        public function criar(array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO partida (
                cd_competicao,
                cd_confronto,
                cd_criador,
                cd_esporte,
                cd_modalidade,
                visibilidade,
                status,
                inicio_agendado
            ) VALUES (
                :cd_competicao,
                :cd_confronto,
                :cd_criador,
                :cd_esporte,
                :cd_modalidade,
                :visibilidade,
                'AGENDADA',
                :inicio_agendado
            )
            RETURNING cd_partida
        ");

        $stmt->execute([
            ':cd_competicao' => $dados['cd_competicao'],
            ':cd_confronto' => $dados['cd_confronto'],
            ':cd_criador' => $dados['cd_criador'],
            ':cd_esporte' => $dados['cd_esporte'],
            ':cd_modalidade' => $dados['cd_modalidade'],
            ':visibilidade' => $dados['visibilidade'],
            ':inicio_agendado' => $dados['inicio_agendado'],
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function criarInfo(int $idPartida, array $dados): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO info_partida (
                cd_partida,
                local_partida,
                observacoes
            ) VALUES (
                :cd_partida,
                :local_partida,
                :observacoes
            )
        ");

        $stmt->execute([
            ':cd_partida' => $idPartida,
            ':local_partida' => $dados['local_partida'],
            ':observacoes' => $dados['observacoes'],
        ]);
    }

    public function vincularTimes(int $idPartida, array $idsTimes): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO vinculo_partida_time (cd_partida, cd_time, mandante)
            VALUES (:cd_partida, :cd_time, :mandante)
        ");

        foreach (array_values($idsTimes) as $posicao => $idTime) {
            $stmt->execute([
                ':cd_partida' => $idPartida,
                ':cd_time' => (int) $idTime,
                ':mandante' => $posicao === 0,
            ]);
        }
    }

    public function buscarConfronto(int $idConfronto): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                c.cd_confronto,
                ec.cd_competicao,
                c.status,
                COUNT(cp.posicao) AS total_participantes,
                COUNT(*) FILTER (
                    WHERE cp.status = 'DEFINIDO'
                      AND opc.tipo_origem = 'TIME'
                      AND opc.cd_time IS NOT NULL
                ) AS total_definidos
            FROM confronto c
            JOIN rodada_competicao r ON r.cd_rodada = c.cd_rodada
            JOIN etapa_competicao ec ON ec.cd_etapa_competicao = r.cd_etapa_competicao
            LEFT JOIN confronto_participante cp ON cp.cd_confronto = c.cd_confronto
            LEFT JOIN origem_participante_confronto opc
                ON opc.cd_origem_participante = cp.cd_origem_participante
            WHERE c.cd_confronto = :id
            GROUP BY c.cd_confronto, ec.cd_competicao, c.status
        ");
        $stmt->execute([':id' => $idConfronto]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function buscarTimesDefinidosConfronto(int $idConfronto): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT opc.cd_time
            FROM confronto_participante cp
            JOIN origem_participante_confronto opc
              ON opc.cd_origem_participante = cp.cd_origem_participante
            WHERE cp.cd_confronto = :id
              AND cp.status = 'DEFINIDO'
              AND opc.tipo_origem = 'TIME'
              AND opc.cd_time IS NOT NULL
            ORDER BY opc.cd_time
        ");
        $stmt->execute([':id' => $idConfronto]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function confrontoTemPartida(int $idConfronto): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT EXISTS (
                SELECT 1 FROM partida WHERE cd_confronto = :id
            )
        ");
        $stmt->execute([':id' => $idConfronto]);

        return (bool) $stmt->fetchColumn();
    }

    public function validarTimesCompeticao(int $idCompeticao, array $idsTimes): bool
    {
        // A validação é feita individualmente para manter compatibilidade com PDO_PGSQL.
        $count = 0;
        $check = $this->pdo->prepare("
            SELECT EXISTS (
                SELECT 1
                FROM inscricao_competicao ic
                JOIN vinculo_inscricao_time vit
                  ON vit.cd_inscricao_competicao = ic.cd_inscricao_competicao
                WHERE ic.cd_competicao = :competicao
                  AND vit.ativo = TRUE
                  AND vit.cd_time = :time
            )
        ");

        foreach ($idsTimes as $idTime) {
            $check->execute([
                ':competicao' => $idCompeticao,
                ':time' => (int) $idTime,
            ]);
            if ($check->fetchColumn()) {
                $count++;
            }
        }

        return $count === count($idsTimes);
    }

    public function buscar(int $idPartida): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                p.*,
                ip.dt_partida,
                ip.inicio_em,
                ip.fim_em,
                ip.local_partida,
                ip.observacoes,
                c.nm_competicao,
                e.nm_esporte,
                m.sexo,
                m.categoria
            FROM partida p
            LEFT JOIN info_partida ip ON ip.cd_partida = p.cd_partida
            LEFT JOIN competicao c ON c.cd_competicao = p.cd_competicao
            LEFT JOIN esporte e ON e.cd_esporte = COALESCE(p.cd_esporte, c.cd_esporte)
            LEFT JOIN modalidade m ON m.cd_modalidade = COALESCE(p.cd_modalidade, c.cd_modalidade)
            WHERE p.cd_partida = :id
        ");
        $stmt->execute([':id' => $idPartida]);
        $partida = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$partida) {
            return null;
        }

        $times = $this->pdo->prepare("
            SELECT t.cd_time, t.nm_time, vpt.mandante
            FROM vinculo_partida_time vpt
            JOIN time t ON t.cd_time = vpt.cd_time
            WHERE vpt.cd_partida = :id
            ORDER BY vpt.mandante DESC, t.nm_time
        ");
        $times->execute([':id' => $idPartida]);
        $partida['times'] = $times->fetchAll(PDO::FETCH_ASSOC);

        return $partida;
    }

    public function listarVisiveis(int $idUsuario, array $filtros = []): array
    {
        $sql = "
            SELECT DISTINCT p.cd_partida
            FROM partida p
            LEFT JOIN competicao c ON c.cd_competicao = p.cd_competicao
            WHERE
                -- Primeiro: o usuário precisa ter acesso à competição-pai.
                (
                    c.cd_competicao IS NULL
                    OR c.visibilidade = 'PUBLICA'
                    OR c.cd_criador = :usuario_criador_competicao
                    OR EXISTS (
                        SELECT 1 FROM participante_competicao pc
                        WHERE pc.cd_competicao = c.cd_competicao
                          AND pc.cd_usuario = :usuario_participante_competicao
                    )
                )
                -- Segundo: também precisa ter acesso à própria partida.
                AND (
                    p.visibilidade = 'PUBLICA'
                    OR p.cd_criador = :usuario_criador_partida
                    OR c.cd_criador = :usuario_criador_da_competicao
                    OR EXISTS (
                        SELECT 1 FROM participante_partida pp
                        WHERE pp.cd_partida = p.cd_partida
                          AND pp.cd_usuario = :usuario_participante_partida
                    )
                )
        ";

        $params = [
            ':usuario_criador_competicao' => $idUsuario,
            ':usuario_participante_competicao' => $idUsuario,
            ':usuario_criador_partida' => $idUsuario,
            ':usuario_criador_da_competicao' => $idUsuario,
            ':usuario_participante_partida' => $idUsuario,
        ];

        if (!empty($filtros['cd_competicao'])) {
            $sql .= " AND p.cd_competicao = :competicao";
            $params[':competicao'] = (int) $filtros['cd_competicao'];
        }

        if (!empty($filtros['status'])) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filtros['status'];
        }

        if (!empty($filtros['visibilidade'])) {
            $sql .= " AND p.visibilidade = :visibilidade";
            $params[':visibilidade'] = $filtros['visibilidade'];
        }

        $sql .= " ORDER BY p.cd_partida DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $partidas = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $partida = $this->buscar((int) $id);
            if ($partida !== null) {
                $partidas[] = $partida;
            }
        }

        return $partidas;
    }

    public function usuarioPodeAcessar(int $idUsuario, array $partida): bool
    {
        $ehCriadorPartida = (int) $partida['cd_criador'] === $idUsuario;
        $ehCriadorCompeticao = false;
        $participaCompeticao = false;

        if (!empty($partida['cd_competicao'])) {
            $stmt = $this->pdo->prepare("
                SELECT cd_criador, visibilidade
                FROM competicao
                WHERE cd_competicao = :id
            ");
            $stmt->execute([':id' => $partida['cd_competicao']]);
            $competicao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$competicao) {
                return false;
            }

            $ehCriadorCompeticao = (int) $competicao['cd_criador'] === $idUsuario;
            $participaCompeticao = $this->usuarioParticipaCompeticao(
                $idUsuario,
                (int) $partida['cd_competicao']
            );

            // A privacidade da competição sempre prevalece.
            if ($competicao['visibilidade'] === 'PRIVADA'
                && !$ehCriadorCompeticao
                && !$participaCompeticao) {
                return false;
            }
        }

        // A partida pública pode ser vista por quem já passou pela regra da competição.
        if ($partida['visibilidade'] === 'PUBLICA') {
            return true;
        }

        // Para partida privada, é necessário ser criador da partida,
        // criador da competição ou participante registrado na partida.
        return $ehCriadorPartida
            || $ehCriadorCompeticao
            || $this->usuarioParticipaPartida($idUsuario, (int) $partida['cd_partida']);
    }

    public function usuarioParticipaPartida(int $idUsuario, int $idPartida): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT EXISTS (
                SELECT 1 FROM participante_partida
                WHERE cd_partida = :partida AND cd_usuario = :usuario
            )
        ");
        $stmt->execute([':partida' => $idPartida, ':usuario' => $idUsuario]);

        return (bool) $stmt->fetchColumn();
    }

    public function usuarioParticipaCompeticao(int $idUsuario, int $idCompeticao): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT EXISTS (
                SELECT 1 FROM participante_competicao
                WHERE cd_competicao = :competicao AND cd_usuario = :usuario
            )
        ");
        $stmt->execute([':competicao' => $idCompeticao, ':usuario' => $idUsuario]);

        return (bool) $stmt->fetchColumn();
    }

    private function competicaoPublica(int $idCompeticao): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT visibilidade = 'PUBLICA'
            FROM competicao WHERE cd_competicao = :id
        ");
        $stmt->execute([':id' => $idCompeticao]);

        return (bool) $stmt->fetchColumn();
    }

    public function capturarParticipantes(int $idPartida, array $idsTimes): void
    {
        $sql = "
            INSERT INTO participante_partida (cd_partida, cd_usuario, cd_time, origem)
            SELECT :partida, vti.cd_usuario, vti.cd_time, 'INTEGRANTE'
            FROM vinculo_time_integrante vti
            WHERE vti.ativo = TRUE AND vti.cd_time = :time
            ON CONFLICT (cd_partida, cd_usuario) DO NOTHING
        ";
        $stmt = $this->pdo->prepare($sql);

        $sqlTecnicos = "
            INSERT INTO participante_partida (cd_partida, cd_usuario, cd_time, origem)
            SELECT :partida, vtt.cd_usuario, vtt.cd_time, 'TECNICO'
            FROM vinculo_tecnico_time vtt
            WHERE vtt.ativo = TRUE AND vtt.cd_time = :time
            ON CONFLICT (cd_partida, cd_usuario) DO NOTHING
        ";
        $stmtTecnicos = $this->pdo->prepare($sqlTecnicos);

        $sqlResponsaveis = "
            INSERT INTO participante_partida (cd_partida, cd_usuario, cd_time, origem)
            SELECT :partida, r.cd_usuario, rt.cd_time, 'RESPONSAVEL'
            FROM responsavel_time rt
            JOIN responsavel r ON r.cd_responsavel = rt.cd_responsavel
            WHERE rt.cd_time = :time
            ON CONFLICT (cd_partida, cd_usuario) DO NOTHING
        ";
        $stmtResponsaveis = $this->pdo->prepare($sqlResponsaveis);

        foreach ($idsTimes as $idTime) {
            $stmt->execute([':partida' => $idPartida, ':time' => (int) $idTime]);
            $stmtTecnicos->execute([':partida' => $idPartida, ':time' => (int) $idTime]);
            $stmtResponsaveis->execute([':partida' => $idPartida, ':time' => (int) $idTime]);
        }
    }

    public function capturarParticipantesCompeticao(int $idCompeticao, array $idsTimes): void
    {
        $sql = "
            INSERT INTO participante_competicao (cd_competicao, cd_usuario, cd_time)
            SELECT :competicao, vti.cd_usuario, vti.cd_time
            FROM vinculo_time_integrante vti
            WHERE vti.ativo = TRUE AND vti.cd_time = :time
            ON CONFLICT (cd_competicao, cd_usuario) DO NOTHING
        ";
        $stmt = $this->pdo->prepare($sql);

        $sqlTecnicos = "
            INSERT INTO participante_competicao (cd_competicao, cd_usuario, cd_time)
            SELECT :competicao, vtt.cd_usuario, vtt.cd_time
            FROM vinculo_tecnico_time vtt
            WHERE vtt.ativo = TRUE AND vtt.cd_time = :time
            ON CONFLICT (cd_competicao, cd_usuario) DO NOTHING
        ";
        $stmtTecnicos = $this->pdo->prepare($sqlTecnicos);

        $sqlResponsaveis = "
            INSERT INTO participante_competicao (cd_competicao, cd_usuario, cd_time)
            SELECT :competicao, r.cd_usuario, rt.cd_time
            FROM responsavel_time rt
            JOIN responsavel r ON r.cd_responsavel = rt.cd_responsavel
            WHERE rt.cd_time = :time
            ON CONFLICT (cd_competicao, cd_usuario) DO NOTHING
        ";
        $stmtResponsaveis = $this->pdo->prepare($sqlResponsaveis);

        foreach ($idsTimes as $idTime) {
            $params = [':competicao' => $idCompeticao, ':time' => (int) $idTime];
            $stmt->execute($params);
            $stmtTecnicos->execute($params);
            $stmtResponsaveis->execute($params);
        }
    }
}
