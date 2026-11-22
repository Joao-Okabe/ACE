<?php

class Confronto extends Model
{
    public function criar(array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO confronto (
                cd_rodada,
                nr_confronto,
                nome,
                status,
                cd_confronto_anterior_a,
                cd_confronto_anterior_b
            )
            VALUES (
                :cd_rodada,
                :nr_confronto,
                :nome,
                :status,
                :cd_confronto_anterior_a,
                :cd_confronto_anterior_b
            )
            RETURNING cd_confronto
        ");

        $stmt->execute([
            ':cd_rodada' => $dados['cd_rodada'],
            ':nr_confronto' => $dados['nr_confronto'],
            ':nome' => $dados['nome'] ?? null,
            ':status' => $dados['status'] ?? 'PENDENTE',
            ':cd_confronto_anterior_a' => $dados['cd_confronto_anterior_a'] ?? null,
            ':cd_confronto_anterior_b' => $dados['cd_confronto_anterior_b'] ?? null
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function definirVencedor(int $idConfronto, int $idTime): void
    {
        $this->definirResultado($idConfronto, $idTime, null);
    }

    public function definirResultado(
        int $idConfronto,
        ?int $idTimeVencedor,
        ?int $idTimePerdedor
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE confronto
            SET
                cd_vencedor = :vencedor,
                status = 'FINALIZADO'
            WHERE cd_confronto = :confronto
        ");

        $stmt->execute([
            ':vencedor' => $idTimeVencedor,
            ':confronto' => $idConfronto
        ]);

        $stmt = $this->pdo->prepare("
            SELECT cp.cd_confronto, cp.posicao, opc.resultado_confronto
            FROM confronto_participante cp
            JOIN origem_participante_confronto opc
              ON opc.cd_origem_participante = cp.cd_origem_participante
            WHERE opc.tipo_origem = 'CONFRONTO'
              AND opc.cd_confronto = :confronto
        ");
        $stmt->execute([':confronto' => $idConfronto]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $participante) {
            $idTime = match ($participante['resultado_confronto']) {
                'VENCEDOR' => $idTimeVencedor,
                'PERDEDOR' => $idTimePerdedor,
                default => throw new UnexpectedValueException(
                    'Resultado de origem do confronto inválido.'
                ),
            };

            if ($idTime === null) {
                $origem = $this->pdo->prepare("
                    INSERT INTO origem_participante_confronto (tipo_origem)
                    VALUES ('BYE')
                    RETURNING cd_origem_participante
                ");
                $origem->execute();
                $status = 'BYE';
            } else {
                $origem = $this->pdo->prepare("
                    INSERT INTO origem_participante_confronto (
                        tipo_origem,
                        cd_time
                    )
                    VALUES ('TIME', :time)
                    RETURNING cd_origem_participante
                ");
                $origem->execute([':time' => $idTime]);
                $status = 'DEFINIDO';
            }

            $atualizar = $this->pdo->prepare("
                UPDATE confronto_participante
                SET cd_origem_participante = :origem,
                    status = :status
                WHERE cd_confronto = :confronto
                  AND posicao = :posicao
            ");
            $atualizar->execute([
                ':origem' => (int) $origem->fetchColumn(),
                ':status' => $status,
                ':confronto' => (int) $participante['cd_confronto'],
                ':posicao' => (int) $participante['posicao'],
            ]);

            $this->atualizarStatusConfronto(
                (int) $participante['cd_confronto']
            );
        }
    }

    public function processarByesEtapa(int $idEtapaCompeticao): void
    {
        if ($idEtapaCompeticao <= 0) {
            throw new InvalidArgumentException('Etapa inválida.');
        }

        do {
            $stmt = $this->pdo->prepare("
                SELECT
                    c.cd_confronto,
                    cp.status AS status_participante,
                    opc.tipo_origem,
                    opc.cd_time
                FROM confronto c
                JOIN rodada_competicao r
                  ON r.cd_rodada = c.cd_rodada
                JOIN confronto_participante cp
                  ON cp.cd_confronto = c.cd_confronto
                JOIN origem_participante_confronto opc
                  ON opc.cd_origem_participante = cp.cd_origem_participante
                WHERE r.cd_etapa_competicao = :etapa
                  AND c.status NOT IN ('FINALIZADO', 'CANCELADO')
                ORDER BY c.nr_confronto, cp.posicao
            ");
            $stmt->execute([':etapa' => $idEtapaCompeticao]);

            $confrontos = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
                $confrontos[(int) $linha['cd_confronto']][] = $linha;
            }

            $processouBye = false;
            foreach ($confrontos as $idConfronto => $participantes) {
                if (count($participantes) !== 2) {
                    continue;
                }

                $times = [];
                $temBye = false;
                $participantesResolvidos = true;

                foreach ($participantes as $participante) {
                    if (
                        $participante['status_participante'] === 'DEFINIDO'
                        && $participante['tipo_origem'] === 'TIME'
                        && $participante['cd_time'] !== null
                    ) {
                        $times[] = (int) $participante['cd_time'];
                    } elseif (
                        $participante['status_participante'] === 'BYE'
                        && $participante['tipo_origem'] === 'BYE'
                    ) {
                        $temBye = true;
                    } else {
                        $participantesResolvidos = false;
                        break;
                    }
                }

                if (!$participantesResolvidos || !$temBye) {
                    continue;
                }

                $this->definirResultado(
                    $idConfronto,
                    $times[0] ?? null,
                    null
                );
                $processouBye = true;
            }
        } while ($processouBye);
    }

    private function atualizarStatusConfronto(int $idConfronto): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE confronto
            SET status = CASE
                WHEN (
                    SELECT COUNT(*)
                    FROM confronto_participante cp
                    JOIN origem_participante_confronto opc
                      ON opc.cd_origem_participante = cp.cd_origem_participante
                    WHERE cp.cd_confronto = :confronto
                      AND (
                        (cp.status = 'DEFINIDO' AND opc.tipo_origem = 'TIME')
                        OR (cp.status = 'BYE' AND opc.tipo_origem = 'BYE')
                      )
                ) = 2 THEN 'PENDENTE'
                ELSE 'AGUARDANDO'
            END
            WHERE cd_confronto = :confronto_update
              AND status NOT IN ('FINALIZADO', 'CANCELADO')
        ");
        $stmt->execute([
            ':confronto' => $idConfronto,
            ':confronto_update' => $idConfronto,
        ]);
    }

    public function listarChaveamento(int $idCompeticao): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                c.cd_confronto,
                c.nr_confronto,
                c.nome,
                c.status,
                c.cd_vencedor,
                ec.cd_etapa_competicao,
                ec.nm_etapa,
                ec.ordem AS ordem_etapa,
                r.cd_rodada,
                r.nr_rodada,
                r.nm_rodada,
                c.cd_confronto_anterior_a,
                c.cd_confronto_anterior_b,
                cp.posicao,
                cp.status AS status_participante,
                opc.tipo_origem,
                opc.cd_time,
                t.nm_time,
                t.path_escudo,
                opc.cd_confronto AS origem_confronto,
                opc.resultado_confronto
            FROM confronto c
            INNER JOIN rodada_competicao r
                ON r.cd_rodada = c.cd_rodada
            INNER JOIN etapa_competicao ec
                ON ec.cd_etapa_competicao = r.cd_etapa_competicao
            INNER JOIN confronto_participante cp
                ON cp.cd_confronto = c.cd_confronto
            INNER JOIN origem_participante_confronto opc
                ON opc.cd_origem_participante = cp.cd_origem_participante
            LEFT JOIN time t
                ON t.cd_time = opc.cd_time
            WHERE ec.cd_competicao = :cd_competicao
            ORDER BY
                ec.ordem,
                r.nr_rodada,
                c.nr_confronto,
                cp.posicao
        ");

        $stmt->execute([
            ':cd_competicao' => $idCompeticao
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}