<?php

class OrigemParticipanteConfronto extends Model
{
    public function criarTime(int $idTime): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO origem_participante_confronto (
                tipo_origem,
                cd_time
            )
            VALUES (
                'TIME',
                :time
            )
            RETURNING cd_origem_participante
        ");

        $stmt->execute([
            ':time' => $idTime
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function criarBye(): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO origem_participante_confronto (
                tipo_origem
            )
            VALUES (
                'BYE'
            )
            RETURNING cd_origem_participante
        ");

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function criarVencedorConfronto(
        int $idConfronto
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO origem_participante_confronto (
                tipo_origem,
                cd_confronto,
                resultado_confronto
            )
            VALUES (
                'CONFRONTO',
                :confronto,
                'VENCEDOR'
            )
            RETURNING cd_origem_participante
        ");

        $stmt->execute([
            ':confronto' => $idConfronto
        ]);

        return (int) $stmt->fetchColumn();
    }
}