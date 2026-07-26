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

    /**
     * Lista todas as escolas
     */
    public function index(): void
    {
        $escolas = $this->service()->listar();

        require __DIR__ . '/../Views/escola/listar.php';
    }

    /**
     * Exibe o formulário de cadastro
     */
    public function create(): void
    {
        require __DIR__ . '/../Views/escola/cadastrar.php';
    }

    /**
     * Salva uma nova escola
     */
    public function store(): void
    {
        try {

            $this->service()->cadastrar($_POST);

            header("Location: /escolas");
            exit;

        } catch (Exception $e) {

            echo $e->getMessage();

        }
    }

    /**
     * Exibe o formulário de edição
     */
    public function edit(int $id): void
    {
        $escola = $this->service()->buscar($id);

        require __DIR__ . '/../Views/escola/editar.php';
    }

    /**
     * Atualiza a escola
     */
    public function update(int $id): void
    {
        try {

            $this->service()->atualizar($id, $_POST);

            header("Location: /escolas");
            exit;

        } catch (Exception $e) {

            echo $e->getMessage();

        }
    }

    /**
     * Remove uma escola
     */
    public function destroy(int $id): void
    {
        try {

            $this->service()->remover($id);

            header("Location: /escolas");
            exit;

        } catch (Exception $e) {

            echo $e->getMessage();

        }
    }
}
