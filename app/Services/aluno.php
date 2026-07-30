<?php

class AlunoService
{
    private PDO $pdo;

    private Usuario $usuarioModel; 

    private Aluno $alunoModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->usuarioModel = new Usuario();

        $this->alunoModel = new Aluno();
    }

    public function listar(): array
    {
        return $this->alunoModel->listar();
    }

    public function buscar(int $id): array
    {
        $aluno = $this->alunoModel->buscar($id);

        if ($aluno === null) {
            throw new Exception("Aluno não encontrada.");
        }

        return $aluno;
    }
}