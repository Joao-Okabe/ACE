<?php

class VinculoUsuarioEscolaService 
{
    private PDO $pdo;

    private VinculoUsuarioEscola $vinculoUsuarioEscolaModel;

    private Escola $escolaModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->vinculoUsuarioEscolaModel = new VinculoUsuarioEscola();
    }

    public function isUsuarioDiretor(int $idUsuario, int $idEscola)
    {
        return $this->vinculoUsuarioEscolaModel->isUsuarioDiretor($idUsuario, $idEscola);
    }

    public function usuarioPodeGerenciarEscola(int $idUsuario, int $idEscola): bool
    {
        return $this->vinculoUsuarioEscolaModel->usuarioPodeGerenciarEscola($idUsuario, $idEscola);
    }

    public function escolaGerenciavelPorUsuario(int $idUsuario): ?int
    {
        return $this->vinculoUsuarioEscolaModel->escolaGerenciavelPorUsuario($idUsuario);
    }
}