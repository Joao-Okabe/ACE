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
            ':cd_confronto_anterior_a' =>
                $dados['cd_confronto_anterior_a'] ?? null,
            ':cd_confronto_anterior_b' =>
                $dados['cd_confronto_anterior_b'] ?? null
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function definirVencedor(
        int $idConfronto,
        int $idTime
    ): void {
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
}