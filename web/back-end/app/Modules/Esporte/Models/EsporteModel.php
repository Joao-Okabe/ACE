<?php

class Esporte extends Model
{
    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM esporte
        "); 

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}