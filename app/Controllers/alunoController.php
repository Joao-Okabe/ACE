<?php

class alunoController 
{

    private ?AlunoService $service = null;

    private function service(): AlunoService
    {
        if ($this->service === null) {
            $this->service = new AlunoService();
        }

        return $this->service;
    }

    public function create(): void
    {
        $usuario = $_SESSION['usuario'] ?? null;
        require __DIR__ . '/../Views/aluno/cadastrar.php';
    }

}