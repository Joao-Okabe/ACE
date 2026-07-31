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
        $escolas = (new EscolaService())->listar();
        require __DIR__ . '/../Views/aluno/cadastrar.php';
    }

    //Salva uma nova escola
    public function store(): void
    {
        try {

            $this->service()->cadastrar($_POST);

            header("Location: /alunos/cadastrar?sucesso=1");
            exit;

        } catch (Exception $e) {

            $erro = $e->getMessage();
            $dados = $_POST;

            $escolas = (new EscolaService())->listar();

            require __DIR__ . '/../Views/aluno/cadastrar.php';

        }
    }

    public function list(): void
    {
        $alunos = $this->service()->listar();
        require __DIR__ . '/../Views/aluno/listar.php';
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $aluno = $this->service()->buscar($id);
        $escolas = (new EscolaService())->listar();
        require __DIR__ . '/../Views/aluno/editar.php';
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        try {
            // use service to update if method exists
            if (method_exists($this->service(), 'atualizar')) {
                $this->service()->atualizar($id, $_POST);
            } else {
                $this->service()->alunoModel->atualizar($id, $_POST);
            }

            header("Location: /alunos/listar");
            exit;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

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
            header("Location: /alunos/listar");
            exit;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}