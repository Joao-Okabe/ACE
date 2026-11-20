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
        $stmt = $this->pdo->prepare("
            UPDATE confronto
            SET
                cd_vencedor = :time,
                status = 'FINALIZADO'
            WHERE cd_confronto = :confronto
        ");

        $stmt->execute([
            ':time' => $idTime,
            ':confronto' => $idConfronto
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