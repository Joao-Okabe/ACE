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

    public function listarResponsaveisTime(int $idTime): array
    {
        return $this->vinculoTimeModel->listarResponsaveisTime($idTime);
    }

    public function VincularTimeIntegrante
    (
        int $idUsuario,
        int $idTime,
        int $idFuncaoIntegrante,
        ?int $numeroCamisa = null,
        bool $capitao = false
    )
    {
        if ($idUsuario <= 0) {
            throw new Exception('Aluno inválido.');
        }

        try {
            $this->pdo->beginTransaction();

            $this->vinculoTimeModel->vincularTimeIntegrante(
                $idUsuario,
                $idTime,
                $idFuncaoIntegrante,
                $numeroCamisa,
                $capitao
            );

            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function listarIntegrantesTime (int $idTime)
    {
        return $this->vinculoTimeModel->listarIntegrantesTime($idTime);
    }

    public function removerTimeIntegrante(int $idUsuario, int $idTime): void
    {
        if ($idUsuario <= 0 || $idTime <= 0) {
            throw new Exception('Integrante ou time inválido.');
        }

        try {
            $this->pdo->beginTransaction();
            $this->vinculoTimeModel->removerTimeIntegrante($idUsuario, $idTime);
            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function listarAlunosTime(int $idTime): array
    {
        return $this->vinculoTimeModel->listarAlunosTime($idTime);
    }

    public function listarFuncoesIntegranteTime(int $idTime): array
    {
        return $this->vinculoTimeModel->listarFuncoesIntegranteTime($idTime);
    }

}