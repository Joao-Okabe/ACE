<?php

class UsuarioController
{
    private ?UsuarioService $service = null;

    private function service(): UsuarioService
    {
        if ($this->service === null) {
            $this->service = new UsuarioService();
        }

        return $this->service;
    }

    public function create(): void
    {
        $escolaService = new EscolaService();
        $escolas = $escolaService->listar();
        require __DIR__ . '/../Views/usuario/cadastrar.php';
    }

    public function store(): void
    {
        try {
            $this->service()->cadastrar($_POST);

            header("Location: /usuarios/cadastrar?sucesso=1");
            exit;
        } catch (Exception $e) {
            $erro = $e->getMessage();
            $dados = $_POST;

            $escolaService = new EscolaService();
            $escolas = $escolaService->listar();

            require __DIR__ . '/../Views/usuario/cadastrar.php';
        }
    }
}
