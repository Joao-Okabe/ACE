<?php

class EscolaController
{
    private ?EscolaService $service = null;

    private function service(): EscolaService
    {
        if ($this->service === null) {
            $this->service = new EscolaService();
        }

        return $this->service;
    }

    //Exibe o formulário de cadastro
    public function create(): void
    {
        renderView('escola/cadastrar');
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

            $escolaService = new EscolaService();
            $escolas = $escolaService->listar();

            renderView('usuario/cadastrar', ['erro' => $erro, 'dados' => $dados, 'escolas' => $escolas]);

        }
    }

    //Lista todas as escolas
    public function list(): void
    {
        $escolas = $this->service()->listar();

        renderView('escola/listar', ['escolas' => $escolas]);
    }

    //Exibe formulário de edição de Escola
    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $escola = $this->service()->buscar($id);

        renderView('escola/editar', ['escola' => $escola]);
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

    //Visualiza perfil da escola
    public function visualizar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $escola = $this->service()->buscar($id);

        renderView('escola/perfil', ['escola' => $escola]);
    }
}
