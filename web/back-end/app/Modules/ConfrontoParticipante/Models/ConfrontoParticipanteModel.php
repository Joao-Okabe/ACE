<?php

class ConfrontoParticipante extends Model
{
    public function criar(array $dados): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO confronto_participante (
                cd_confronto,
                posicao,
                cd_origem_participante,
                status
            )
            VALUES (
                :cd_confronto,
                :posicao,
                :cd_origem_participante,
                :status
            )
        ");

        $stmt->execute([
            ':cd_confronto' => $dados['cd_confronto'],
            ':posicao' => $dados['posicao'],
            ':cd_origem_participante' =>
                $dados['cd_origem_participante'],
            ':status' => $dados['status'] ?? 'PENDENTE'
        ]);
    }
}