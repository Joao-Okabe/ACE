<?php

class listarEscolasController
{
    private EscolaService $escolaService;

    public function __construct()
    {
        $this->escolaService = new EscolaService();
    }

    public function index(): void
    {
        $usuario = $_SESSION['usuario'] ?? null;
        $escolas = $this->escolaService->listar();

        require __DIR__ . '/../Views/escola/listar.php';
    }
}