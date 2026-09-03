<?php

class Competicao extends Model
{
    //Cria uma COMPETIÇÃO nova
    public function criar(array $dados):void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO competicao(
                nm_competicao,
                dt_inicio,
                dt_encerramento
            )
            VALUES(
                :nm_competicao,
                :dt_inicio,
                :dt_encerramento
            )
        ");

        $stmt->execute([
            ":nm_competicao" => $dados["nm_competicao"],
            ":dt_inicio" => $dados["dt_inicio"],
            ":dt_encerramento" => $dados["dt_encerramento"]
        ]);
    }

    //Lista COMPETIÇÕES
    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                cd_competicao,
                nm_competicao,
                criado_em,
                dt_inicio,
                dt_encerramento,
            FROM competicao
            ORDER BY cd_competicao
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}