<?php

class Partida extends Model 
{
    private Filtro $filtro;

    public function __construct()
    {
        parent::__construct();
        $this->filtro = new Filtro();
    }

    public function criarBase(array $dados, int $idCompeticao){
        $stmt = $this->pdo->prepare("
            INSERT INTO partida 
            (
                cd_competicao,
                cd_esporte,
                cd_modalidade,
                cd_formato
            )
            VALUES
            (
                :cd_competicao,
                :cd_esporte,
                :cd_modalidade,
                :cd_formato
            ) 
            RETURNING cd_partida
        ");

        $stmt->execute([
            ':cd_competicao' => $idCompeticao,
            ':cd_esporte' => $dados['cd_esporte'],
            ':cd_modalidade' => $dados['cd_modalidade'],
            ':cd_formato' => $dados['cd_formato'],
        ]);
    }

    public function listar(array $filtros = []): array
    {
        $stmt = "
            SELECT
                p.cd_partida,
                p.cd_competicao,
                p.cd_esporte,
                p.cd_modalidade,
                p.cd_formato
            FROM partida p";

        $filtrosSql = $this->filtro->filtrosPartidas($filtros);

        if (!empty($filtrosSql['onde'])) {
            $stmt .= ' WHERE ' . implode(' AND ', $filtrosSql['onde']);
        }

        $ordem = $this->filtro->ordem($filtros);
        $stmt .= " ORDER BY p.cd_partida {$ordem}";

        $stmt = $this->pdo->prepare($stmt);
        $stmt->execute($filtrosSql['parametros']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}