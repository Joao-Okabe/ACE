<?php

class VinculoTimeIntegranteService
{
    private PDO $pdo;

    private VinculoTimeIntegrante $vinculoTimeIntegranteModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->vinculoTimeIntegranteModel = new VinculoTimeIntegrante();
    }

    public function VincularTimeIntegranteService
    (
        int $idUsuario,
        int $idTime,
        int $idFuncaoIntegrante
    )
    {
        try {
            $this->pdo->beginTransaction();

            $this->vinculoTimeIntegranteModel->vincularTimeIntegrante(
                $idUsuario,
                $idTime,
                $idFuncaoIntegrante
            );

            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

}