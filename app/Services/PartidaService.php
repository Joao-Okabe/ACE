<?php

class PartidaService
{

    private PDO $pdo;

    private Partida $partidaModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->partidaModel = new Partida();
    }

    public function criar(array $dados, int $idCompeticao){

        if (empty($idCompeticao)) {
            throw new Exception("Informe a competição da partida.");
        }

        if (empty($dados['cd_esporte'])) {
            throw new Exception("Informe o formato da partida.");
        }

        if (empty($dados['cd_modalidade'])) {
            throw new Exception("Informe o formato da partida.");
        }

        if (empty($dados['cd_formato'])) {
            throw new Exception("Informe o formato da partida.");
        }

        try {

            $this->pdo->beginTransaction();

            $this->partidaModel->criarBase($dados, $idCompeticao);

            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->pdo->commit();

            throw $e;

        }
    }

    public function listar(array $filtros = []): array
    {
        return $this->partidaModel->listar($filtros);
    }
}