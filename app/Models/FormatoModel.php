<?php

class Formato extends Model
{
    public function listar()
    {
        $stmt = $this->pdo->query("
            SELECT * FROM formato
        "); 

        $formato = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $formato?: null;
    }
}