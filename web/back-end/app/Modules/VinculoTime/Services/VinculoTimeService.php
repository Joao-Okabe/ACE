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
        $this->exigirPermissaoGerenciarTime($idTime);
        try {
            $this->pdo->beginTransaction();
            $this->vinculoTimeModel->bloquearTime($idTime);

            $this->vinculoTimeModel->vincularTimeResponsavel(
                $idResponsavel,
                $idTime
            );

            $this->pdo->commit();

        } catch (Throwable $e) {

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
        $idUsuario ??= AuthContext::id();

        if (!$idUsuario || $idUsuario <= 0 || $idTime <= 0) {
            return false;
        }

        if (Permissoes::temPapel('ADM')) {
            return true;
        }

        return $this->vinculoTimeModel->usuarioPodeGerenciarTime(
            $idUsuario,
            $idTime
        );
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

    public function listarTecnicosDisponiveis(int $idTime): array
    {
        return $this->vinculoTimeModel->listarTecnicosDisponiveis($idTime);
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
        $this->exigirPermissaoGerenciarTime($idTime);
        if ($idUsuario <= 0) {
            throw new Exception('Aluno inválido.');
        }

        try {
            $this->pdo->beginTransaction();
            $this->vinculoTimeModel->bloquearTime($idTime);

            $this->vinculoTimeModel->vincularTimeIntegrante(
                $idUsuario,
                $idTime,
                $idFuncaoIntegrante,
                $numeroCamisa,
                $capitao
            );

            $this->pdo->commit();

        } catch (Throwable $e) {

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
        $this->exigirPermissaoGerenciarTime($idTime);
        if ($idUsuario <= 0 || $idTime <= 0) {
            throw new Exception('Integrante ou time inválido.');
        }

        try {
            $this->pdo->beginTransaction();
            $this->vinculoTimeModel->bloquearTime($idTime);
            $this->vinculoTimeModel->removerTimeIntegrante($idUsuario, $idTime);
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function tornarCapitao(int $idUsuario, int $idTime): void
    {
        $this->exigirPermissaoGerenciarTime($idTime);
        if ($idUsuario <= 0 || $idTime <= 0) {
            throw new Exception('Integrante ou time inválido.');
        }

        try {
            $this->pdo->beginTransaction();
            $this->vinculoTimeModel->tornarCapitao($idUsuario, $idTime);
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function removerCapitao(int $idUsuario, int $idTime): void
    {
        $this->exigirPermissaoGerenciarTime($idTime);
        if ($idUsuario <= 0 || $idTime <= 0) {
            throw new Exception('Integrante ou time inválido.');
        }

        try {
            $this->pdo->beginTransaction();
            $this->vinculoTimeModel->removerCapitao($idUsuario, $idTime);
            $this->pdo->commit();
        } catch (Throwable $e) {
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

    public function vincularTecnicoTime (int $idUsuario, int $idTime) 
    {
        $this->exigirPermissaoGerenciarTime($idTime);
        if ($idUsuario <= 0) {
            throw new Exception('Usuário inválido.');
        }

        try {
            $this->pdo->beginTransaction();
            $this->vinculoTimeModel->bloquearTime($idTime);

            $this->vinculoTimeModel->vincularTecnicoTime(
                $idUsuario,
                $idTime
            );

            $this->pdo->commit();

        } catch (Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function salvarEscalacao(int $idTime, array $idUsuariosTitulares): void
    {
        $this->exigirPermissaoGerenciarTime($idTime);
        if ($idTime <= 0) {
            throw new Exception('Time inválido.');
        }

        if (count($idUsuariosTitulares) > 5) {
            throw new Exception('Apenas 5 jogadores podem ser titulares.');
        }

        // Uma lista vazia é válida: deixa todos os integrantes ativos como reservas.

        $idsValidados = [];
        foreach ($idUsuariosTitulares as $idUsuario) {
            if (!(is_int($idUsuario) || (is_string($idUsuario) && ctype_digit($idUsuario)))) {
                throw new InvalidArgumentException('Cada titular deve ser um ID inteiro positivo.');
            }
            $idUsuario = (int) $idUsuario;
            if ($idUsuario <= 0 || isset($idsValidados[$idUsuario])) {
                throw new InvalidArgumentException('A lista de titulares contém IDs inválidos ou repetidos.');
            }
            $idsValidados[$idUsuario] = true;
        }

        try {
            $this->pdo->beginTransaction();

            $this->vinculoTimeModel->bloquearTime($idTime);
            $vinculos = $this->vinculoTimeModel->listarVinculosTime($idTime);
            $mapa = [];
            foreach ($vinculos as $vinculo) {
                $mapa[(int) $vinculo['cd_usuario']] = (int) $vinculo['cd_vinculo_time_integrante'];
            }

            $titulares = [];
            foreach (array_keys($idsValidados) as $idUsuario) {
                if (!isset($mapa[$idUsuario])) {
                    throw new Exception('Integrante não pertence a este time.');
                }
                $titulares[$mapa[$idUsuario]] = true;
            }

            $this->vinculoTimeModel->limparEscalacao($idTime);

            foreach ($mapa as $cdVinculo) {
                $this->vinculoTimeModel->inserirEscalacao($cdVinculo, isset($titulares[$cdVinculo]));
            }

            $this->pdo->commit();

        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

}
