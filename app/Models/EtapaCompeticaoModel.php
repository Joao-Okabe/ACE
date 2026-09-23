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
}