<?php

class CompeticaoController 
{

    private ?CompeticaoService $service = null;

    private function service(): CompeticaoService
    {
        if ($this->service === null) {
            $this->service = new CompeticaoService();
        }

        return $this->service;
    }

    //Passa sessão do usuário e exibe tela de criação de competição
    public function criar(): void
    {
        renderView('competicao/criar');
    }

    //Salva um nova competição
    public function store(): void
    {
        try {

            $this->service()->criar($_POST);

            header("Location: /competicoes/criar?sucesso=1");
            exit;

        } catch (Exception $e) {

            $erro = $e->getMessage();
            $dados = $_POST;

            $competicoes = (new CompeticaoService())->listar();
            renderView('competicao/criar', ['erro' => $erro, 'dados' => $dados]);
        }
    }

    //Lista todos as competições
    public function listar(): void
    {
        $filtros = [
            'nome' => trim($_GET['nome'] ?? ''),
            'escola' => (int) ($_GET['escola'] ?? 0),
            'ordem' => strtolower($_GET['ordem'] ?? 'asc'),
        ];

        if (!in_array($filtros['ordem'], ['asc', 'desc'], true)) {
            $filtros['ordem'] = 'asc';
        }

        $competicao = $this->service()->listar($filtros);
        $escolas = (new EscolaService())->listar();

        renderView('competicao/listar', [
            'competicoes' => $competicao,
            'escolas' => $escolas,
            'filtros' => $filtros,
        ]);
    }

    //Exibe formulário de edição da Competição
    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $competicao = $this->service()->buscar($id);

        renderView('competicao/editar', ['competicao' => $competicao]);
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
            $this->service()->atualizar($id, $_POST);
            header('Location: /competicoes/visualizar?id=' . $id);
            exit;
        } catch (Exception $e) {
            renderView('competicao/editar', [
                'erro' => $e->getMessage(),
                'competicao' => array_merge($this->service()->buscar($id) ?? [], $_POST),
            ]);
        }
    }

    //Visualiza competicao
    public function visualizar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $competicao = $this->service()->buscar($id);

        $escolas = (new EscolaService())->listar();

        renderView('competicao/vizualizar', ['competicao' => $competicao, 'escolas' => $escolas]);
    }

    public function remover(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $this->service()->remover($id);

        header('Location: /competicoes/listar');
        exit;
    }

}