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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Método não permitido.';
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        // Verifica sessão
        if (empty($_SESSION['usuario']['id'])) {
            http_response_code(401);
            echo 'Acesso negado.';
            return;
        }

        // CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $token)) {
            http_response_code(403);
            echo 'Token inválido.';
            return;
        }

        try {
            // Obter a escola do aluno
            $idEscola = $this->service()->obterEscolaDoAluno($id);

            if ($idEscola === null) {
                $_SESSION['flash'] = ['error' => 'Aluno sem vínculo com escola.'];
                header("Location: /alunos/listar");
                exit;
            }

            $idUsuario = (int) $_SESSION['usuario']['id'];

            $vinculoModel = new VinculoUsuarioEscola();

            // permitir apenas COORD ou DIRETOR
            $permitido = $vinculoModel->isUsuarioComPapeis($idUsuario, $idEscola, ['COORD', 'DIRETOR']);

            if (!$permitido) {
                http_response_code(403);
                $_SESSION['flash'] = ['error' => 'Acesso negado.'];
                header("Location: /alunos/listar");
                exit;
            }

            $this->service()->remover($id);
            $_SESSION['flash'] = ['success' => 'Aluno removido com sucesso.'];
            header("Location: /alunos/listar");
            exit;
        } catch (Exception $e) {
            $_SESSION['flash'] = ['error' => $e->getMessage()];
            header("Location: /alunos/listar");
            exit;
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