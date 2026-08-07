<?php

class AlunoController 
{

    private ?AlunoService $service = null;

    private function service(): AlunoService
    {
        if ($this->service === null) {
            $this->service = new AlunoService();
        }

        return $this->service;
    }

    //Passa sessão do usuário e exibe tela de cadastro de aluno
    public function create(): void
    {
        $escolas = (new EscolaService())->listar();
        renderView('aluno/cadastrar', ['escolas' => $escolas]);
    }

    //Salva um novo aluno
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

            renderView('aluno/cadastrar', ['erro' => $erro, 'dados' => $dados, 'escolas' => $escolas]);

        }
    }

    //Lista todos os alunos
    public function list(): void
    {
        $filtros = [
            'nome' => trim($_GET['nome'] ?? ''),
            'escola' => (int) ($_GET['escola'] ?? 0),
            'ordem' => strtolower($_GET['ordem'] ?? 'asc'),
        ];

        if (!in_array($filtros['ordem'], ['asc', 'desc'], true)) {
            $filtros['ordem'] = 'asc';
        }

        $alunos = $this->service()->listar($filtros);
        $escolas = (new EscolaService())->listar();

        renderView('aluno/listar', [
            'alunos' => $alunos,
            'escolas' => $escolas,
            'filtros' => $filtros,
        ]);
    }

    //Exibe formulário de edição do Aluno
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
        renderView('aluno/editar', ['aluno' => $aluno, 'escolas' => $escolas]);
    }

    //Atualiza Aluno
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
            header("Location: /alunos/listar");
            exit;
        } catch (Exception $e) {
            $erro = $e->getMessage();
            $aluno = $this->service()->buscar($id);
            $escolas = (new EscolaService())->listar();
            $dados = $_POST;

            renderView('aluno/editar', [
                'erro' => $erro,
                'aluno' => $aluno,
                'escolas' => $escolas,
                'dados' => $dados
            ]);
        }
    }

    //Remove Aluno
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

    //Visualiza perfil do aluno
    public function visualizar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $aluno = $this->service()->buscar($id);

        $escolas = (new EscolaService())->listar();

        renderView('aluno/perfil', ['aluno' => $aluno, 'escolas' => $escolas]);
    }

}