<?php

class VinculoTimeService
{
    private PDO $pdo;

    private VinculoTime $vinculoTimeModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->vinculoTimeModel = new VinculoTime();
    }

    public function VincularTimeResponsavel
    (
        int $idResponsavel,
        int $idTime
    )
    {
        

        try {
            $this->pdo->beginTransaction();

            $this->vinculoTimeModel->vincularTimeResponsavel(
                $idResponsavel,
                $idTime
            );

            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function listarResponsaveis(): array
    {
        return $this->vinculoTimeModel->listarResponsaveis();
    }

    public function VincularTimeIntegrante
    (
        int $idTime,
        int $idFuncaoIntegrante
    )
    {
        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);
        if ($idUsuario <= 0) {
            throw new Exception('Usuário não autenticado.');
        }

        try {
            $this->pdo->beginTransaction();

            $this->vinculoTimeModel->vincularTimeIntegrante(
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