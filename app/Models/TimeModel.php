<?php

class Time extends Model 
{
    private Filtro $filtro;

    public function __construct()
    {
        parent::__construct();
        $this->filtro = new Filtro();
    }

    public function criar(array $dados): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO time(
                nm_time,
                path_brasao
            ) VALUES (
                :nm_time,
                :path_brasao
            )
        ");

        $stmt->execute([
            ':nm_time' => $dados['nm_time'],
            ':path_brasao' => $dados['path_brasao']
        ]);
    }

    public function listar(array $filtros = []): array
    {
        $stmt = "
            SELECT
                t.cd_time,
                t.nm_time,
                t.path_brasao,
                t.ativo,
                t.criado_em,
                t.atualizado
            FROM time t";

        $filtrosSql = $this->filtro->filtrosTime($filtros);

        if (!empty($filtrosSql['onde'])) {
            $stmt .= ' WHERE ' . implode(' AND ', $filtrosSql['onde']);
        }

        $ordem = $this->filtro->ordem($filtros);
        $stmt .= " ORDER BY t.cd_time {$ordem}";

        $stmt = $this->pdo->prepare($stmt);
        $stmt->execute($filtrosSql['parametros']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function remover(int $id)
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM time
            WHERE cd_time = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);
    }

}