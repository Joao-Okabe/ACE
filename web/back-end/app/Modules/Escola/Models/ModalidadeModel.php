<?php

class Modalidade extends Model
{
    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM modalidade
        "); 

        $modalidade = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $modalidade?: null;
    }
}