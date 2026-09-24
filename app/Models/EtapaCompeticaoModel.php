<?php

class EtapaCompeticao extends Model
{
    public function buscarPorCompeticao(
        int $idEtapa,
        int $idCompeticao
    ): ?array {
        $stmt = $this->pdo->prepare("
            SELECT
                ec.cd_etapa_competicao,
                ec.cd_competicao,
                ec.cd_tipo_etapa,
                ec.nm_etapa,
                ec.ordem,
                ec.descricao,
                te.nm_tipo_etapa
            FROM etapa_competicao ec
            INNER JOIN tipo_etapa te
                ON te.cd_tipo_etapa = ec.cd_tipo_etapa
            WHERE ec.cd_etapa_competicao = :etapa
              AND ec.cd_competicao = :competicao
        ");

        $stmt->execute([
            ':etapa' => $idEtapa,
            ':competicao' => $idCompeticao
        ]);

        $etapa = $stmt->fetch(PDO::FETCH_ASSOC);

        return $etapa ?: null;
    }

    public function buscarEliminatoriaPorCompeticao(int $idCompeticao): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                ec.cd_etapa_competicao,
                ec.cd_competicao,
                ec.cd_tipo_etapa,
                ec.nm_etapa,
                ec.ordem,
                ec.descricao,
                te.nm_tipo_etapa
            FROM etapa_competicao ec
            INNER JOIN tipo_etapa te
                ON te.cd_tipo_etapa = ec.cd_tipo_etapa
            WHERE ec.cd_competicao = :competicao
              AND te.nm_tipo_etapa = 'ELIMINATORIA'
            ORDER BY ec.ordem
            LIMIT 1
        ");

        $stmt->execute([
            ':competicao' => $idCompeticao
        ]);

        $etapa = $stmt->fetch(PDO::FETCH_ASSOC);

        return $etapa ?: null;
    }

    public function criar(array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO etapa_competicao (
                cd_competicao,
                cd_tipo_etapa,
                nm_etapa,
                ordem,
                descricao
            )
            VALUES (
                :cd_competicao,
                :cd_tipo_etapa,
                :nm_etapa,
                :ordem,
                :descricao
            )
            RETURNING cd_etapa_competicao
        ");

        $stmt->execute([
            ':cd_competicao' => $dados['cd_competicao'],
            ':cd_tipo_etapa' => $dados['cd_tipo_etapa'],
            ':nm_etapa' => $dados['nm_etapa'],
            ':ordem' => $dados['ordem'] ?? 1,
            ':descricao' => $dados['descricao'] ?? null
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function buscarTipoPorNome(string $nomeTipo): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT cd_tipo_etapa
            FROM tipo_etapa
            WHERE nm_tipo_etapa = :nome
        ");

        $stmt->execute([':nome' => $nomeTipo]);

        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }
}