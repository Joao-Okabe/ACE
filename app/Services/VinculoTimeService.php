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

    public function vincularTimeResponsavel
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

    public function listarUsuarios(): array
    {
        return $this->vinculoTimeModel->listarUsuarios();
    }

    public function usuarioPodeGerenciarTime(int $idTime, ?int $idUsuario = null): bool
    {
        $idUsuario ??= (int) ($_SESSION['usuario']['id'] ?? 0);

        if ($idUsuario <= 0 || $idTime <= 0) {
            return false;
        }

        return $this->vinculoTimeModel->usuarioPodeGerenciarTime($idUsuario, $idTime);
    }

    public function exigirPermissaoGerenciarTime(int $idTime): void
    {
        if (!$this->usuarioPodeGerenciarTime($idTime)) {
            throw new Exception('Acesso negado para este time.');
        }
    }

    public function listarResponsaveisTime(int $idTime): array
    {
        return $this->vinculoTimeModel->listarResponsaveisTime($idTime);
    }

    public function listarTecnicosTime(int $idTime): array
    {
        return $this->vinculoTimeModel->listarTecnicosTime($idTime);
    }

    public function vincularTimeIntegrante
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

    public function tornarCapitao(int $idUsuario, int $idTime): void
    {
        if ($idUsuario <= 0 || $idTime <= 0) {
            throw new Exception('Integrante ou time inválido.');
        }

        try {
            $this->pdo->beginTransaction();
            $this->vinculoTimeModel->tornarCapitao($idUsuario, $idTime);
            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function removerCapitao(int $idUsuario, int $idTime): void
    {
        if ($idUsuario <= 0 || $idTime <= 0) {
            throw new Exception('Integrante ou time inválido.');
        }

        $this->vinculoTimeModel->removerCapitao($idUsuario, $idTime);
    }

    public function listarAlunosTime(int $idTime): array
    {
        return $this->vinculoTimeModel->listarAlunosTime($idTime);
    }

    public function listarFuncoesIntegranteTime(int $idTime): array
    {
        return $this->vinculoTimeModel->listarFuncoesIntegranteTime($idTime);
    }

    public function vincularTecnicoTime (int $idUsuario, int $idTime) 
    {
        if ($idUsuario <= 0) {
            throw new Exception('Usuário inválido.');
        }

        try {
            $this->pdo->beginTransaction();

            $this->vinculoTimeModel->vincularTecnicoTime(
                $idUsuario,
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

    public function salvarEscalacao(int $idTime, array $idUsuariosTitulares): void
    {
        if ($idTime <= 0) {
            throw new Exception('Time inválido.');
        }

        if (count($idUsuariosTitulares) > 5) {
            throw new Exception('Apenas 5 jogadores podem ser titulares.');
        }

        $vinculos = $this->vinculoTimeModel->listarVinculosTime($idTime);
        $mapa = [];
        foreach ($vinculos as $vinculo) {
            $mapa[(int) $vinculo['cd_usuario']] = (int) $vinculo['cd_vinculo_time_integrante'];
        }

        $titulares = [];
        foreach ($idUsuariosTitulares as $idUsuario) {
            $idUsuario = (int) $idUsuario;
            if ($idUsuario <= 0) {
                continue;
            }
            if (!isset($mapa[$idUsuario])) {
                throw new Exception('Integrante não pertence a este time.');
            }
            $titulares[$mapa[$idUsuario]] = true;
        }

        try {
            $this->pdo->beginTransaction();

            $this->vinculoTimeModel->limparEscalacao($idTime);

            foreach ($mapa as $cdVinculo) {
                $this->vinculoTimeModel->inserirEscalacao($cdVinculo, isset($titulares[$cdVinculo]));
            }

            $this->pdo->commit();

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function tornarTitular (int $idIntegrante) 
    {
        if ($idIntegrante <= 0) {
            throw new Exception('Integrante inválido.');
        }

        $this->vinculoTimeModel->inserirEscalacao($idIntegrante, true);
    }
}