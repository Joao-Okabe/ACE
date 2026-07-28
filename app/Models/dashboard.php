<?php

class Dashboard extends Model
{
    
    public function listar(): array
    {
        $sql = "SELECT * FROM escola";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

}