<?php

class Modalidade extends Model
{
    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM modalidade
        "); 

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
