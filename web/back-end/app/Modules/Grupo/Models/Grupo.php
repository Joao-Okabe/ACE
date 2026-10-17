<?php

class Grupo extends Model
{
    public function criar(array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO grupo_competicao (
                cd_etapa_competicao,
                nm_grupo,
                ordem
            )
            VALUES (
                :cd_etapa_competicao,
                :nm_grupo,
                :ordem
            )
            RETURNING cd_grupo
        ");

        $stmt->execute([
            ':cd_etapa_competicao' => $dados['cd_etapa_competicao'],
            ':nm_grupo' => $dados['nm_grupo'],
            ':ordem' => $dados['ordem'] ?? null
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function vincularTime(int $idGrupo, int $idTime): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO vinculo_grupo_time (
                cd_grupo,
                cd_time
            )
            VALUES (
                :cd_grupo,
                :cd_time
            )
            ON CONFLICT (cd_grupo, cd_time) DO NOTHING
        ");

        $stmt->execute([
            ':cd_grupo' => $idGrupo,
            ':cd_time' => $idTime
        ]);
    }

    public function criarClassificacaoInicial(int $idGrupo, int $idTime, int $posicao): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO classificacao_grupo (
                cd_grupo,
                cd_time,
                posicao
            )
            VALUES (
                :cd_grupo,
                :cd_time,
                :posicao
            )
            RETURNING cd_classificacao_grupo
        ");

        $stmt->execute([
            ':cd_grupo' => $idGrupo,
            ':cd_time' => $idTime,
            ':posicao' => $posicao
        ]);

        return (int) $stmt->fetchColumn();
    }
}
