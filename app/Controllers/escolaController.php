<?php

class escolaController
{
    private ?EscolaService $service = null;

    private function service(): EscolaService
    {
        if ($this->service === null) {
            $this->service = new EscolaService();
        }

        return $this->service;
    }

    //Lista todas as escolas
    public function list(): void
    {
        $usuario = $_SESSION['usuario'] ?? null;
        $escolas = $this->service()->listar();

        require __DIR__ . '/../Views/escola/listar.php';
    }

    //Exibe o formulário de cadastro
    public function create(): void
    {
        require __DIR__ . '/../Views/escola/cadastrar.php';
    }

    //Salva uma nova escola
    public function store(): void
    {
        try {

            $this->service()->cadastrar($_POST);

            header("Location: /escolas/cadastrar?sucesso=1");
            exit;

        } catch (Exception $e) {

            $erro = $e->getMessage();
            $dados = $_POST;

            require __DIR__ . '/../Views/escola/cadastrar.php';

        }
    }

    //Exibe formulário de edição
    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $escola = $this->service()->buscar($id);

        require __DIR__ . '/../Views/escola/editar.php';
    }

    //Atualiza escola
    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        try {

            $this->service()->atualizar($id, $_POST);

            header("Location: /escolas/listar");
            exit;

        } catch (Exception $e) {

            echo $e->getMessage();

        }
    }

    //Remove escola
    public function destroy(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        try {

            $this->service()->remover($id);

            header("Location: /escolas/listar");
            exit;

        } catch (Exception $e) {

            echo $e->getMessage();

        }
    }
}
