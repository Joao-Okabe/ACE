<?php

class RodadaCompeticao extends Model
{
    public function criar(array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO rodada_competicao (
                cd_etapa_competicao,
                nr_rodada,
                nm_rodada
            )
            VALUES (
                :cd_etapa_competicao,
                :nr_rodada,
                :nm_rodada
            )
            RETURNING cd_rodada
        ");

        $stmt->execute([
            ':cd_etapa_competicao' => $dados['cd_etapa_competicao'],
            ':nr_rodada' => $dados['nr_rodada'],
            ':nm_rodada' => $dados['nm_rodada'] ?? null
        ]);

        return (int) $stmt->fetchColumn();
    }
}