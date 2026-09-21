<?php

class Time extends Model 
{
    private Filtro $filtro;

    public function __construct()
    {
        parent::__construct();
        $this->filtro = new Filtro();
    }

    public function criar(array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO time(
                nm_time,
                cd_esporte,
                path_escudo
            ) VALUES (
                :nm_time,
                :cd_esporte,
                :path_escudo
            )
            RETURNING cd_time
        ");

        $stmt->execute([
            ':nm_time' => $dados['nm_time'],
            ':cd_esporte' => $dados['cd_esporte'],
            ':path_escudo' => $dados['path_escudo']
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function listar(array $filtros = []): array
    {
        $stmt = "
            SELECT
                t.cd_time,
                t.nm_time,
                t.path_escudo,
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

    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT cd_time, nm_time, path_escudo, principal, ativo, criado_em, atualizado
            FROM time
            WHERE cd_time = :id
        ');

        $stmt->execute([':id' => $id]);
        $time = $stmt->fetch(PDO::FETCH_ASSOC);

        return $time ?: null;
    }

    public function atualizar(int $id, array $dados): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE time
            SET nm_time = :nm_time,
                path_escudo = :path_escudo,
                atualizado = CURRENT_TIMESTAMP
            WHERE cd_time = :id
        ');

        $stmt->execute([
            ':id' => $id,
            ':nm_time' => $dados['nm_time'],
            ':path_escudo' => $dados['path_escudo'],
        ]);
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

    public function listarTime()
    {
        $stmt = $this->pdo->query("
            SELECT * FROM time
        "); 

        $time = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $time?: null;
    }
}