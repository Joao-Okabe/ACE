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
}