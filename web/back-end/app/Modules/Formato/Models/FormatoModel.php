<?php

class Formato extends Model
{
    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM formato
        "); 

        $formato = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $formato ?: null;
    }

    public function buscarPorCompeticao(int $idCompeticao): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT f.*
            FROM formato f
            INNER JOIN competicao c
                ON c.cd_formato = f.cd_formato
            WHERE c.cd_competicao = :competicao
        ");

        $stmt->execute([':competicao' => $idCompeticao]);

        $formato = $stmt->fetch(PDO::FETCH_ASSOC);

        return $formato ?: null;
    }
}
