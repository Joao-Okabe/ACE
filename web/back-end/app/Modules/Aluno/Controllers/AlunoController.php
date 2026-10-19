<?php

class AlunoController 
{

    private AlunoService $service;

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
        $ehDiretor = $this->ehDiretor();
        renderView('aluno/cadastrar', [
            'escolas' => $escolas,
            'usuariosExistentes' => $ehDiretor ? $this->service()->listarUsuariosDisponiveis() : [],
            'ehDiretor' => $ehDiretor,
            'ehAdministrador' => $this->ehAdministrador(),
            'escolaVinculada' => $this->escolaVinculada(),
        ]);
    }

    //Salva um novo aluno
    public function store(): void
    {
        try {
            // Apenas ADM e DIR podem cadastrar alunos.
            if (!Permissoes::podeGerenciarUsuarios()) {
                http_response_code(403);
                echo 'Você não tem permissão para cadastrar alunos.';
                return;
            }

            $this->service()->cadastrar($_POST);

            header("Location: /alunos/cadastrar?sucesso=1");
            exit;

        } catch (Exception $e) {

            $erro = $e->getMessage();
            $dados = $_POST;

            $escolas = (new EscolaService())->listar();
            $ehDiretor = $this->ehDiretor();

            renderView('aluno/cadastrar', [
                'erro' => $erro,
                'dados' => $dados,
                'escolas' => $escolas,
                'usuariosExistentes' => $ehDiretor ? $this->service()->listarUsuariosDisponiveis() : [],
                'ehDiretor' => $ehDiretor,
                'ehAdministrador' => $this->ehAdministrador(),
                'escolaVinculada' => $this->escolaVinculada(),
            ]);

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

        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);
        $vinculoModel = new VinculoUsuarioEscola();
        $podeGerenciar = [];

        foreach ($alunos as $aluno) {
            $idAluno = (int) ($aluno['cd_aluno'] ?? 0);
            $idEscola = (int) ($aluno['cd_escola'] ?? 0);

            $podeGerenciar[$idAluno] = (
                $idEscola > 0
                && $vinculoModel->usuarioPodeGerenciarEscola(
                    $idUsuario,
                    $idEscola
                )
            );
        }

        renderView('aluno/listar', [
            'alunos' => $alunos,
            'escolas' => $escolas,
            'filtros' => $filtros,
            'podeGerenciar' => $podeGerenciar,
        ]);
    }

    private function ehAdministrador(): bool
    {
        return in_array('ADM', $_SESSION['usuario']['papeis'] ?? [], true);
    }

    private function ehDiretor(): bool
    {
        return in_array('DIR', $_SESSION['usuario']['papeis'] ?? [], true);
    }

    private function escolaVinculada(): ?int
    {
        return (new VinculoUsuarioEscola())->escolaAtualPorPapeis(
            (int) ($_SESSION['usuario']['id'] ?? 0),
            ['DIR', 'CRD']
        );
    }

    //Exibe formulário de edição do Aluno
    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            return;
        }

        $aluno = $this->service()->buscar($id);
        (new Permissoes())->exigirPermissaoGerenciarAluno($id);
        $escolas = (new EscolaService())->listar();
        renderView('aluno/editar', ['aluno' => $aluno, 'escolas' => $escolas]);
    }

    //Atualiza Aluno
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Método não permitido.';
            return;
        }

        $id = (int) ($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        try {
            (new Permissoes())->exigirPermissaoGerenciarAluno($id);
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

        // CSRF: verificado centralmente pelo Router (Csrf::exigirToken).

        try {
            (new Permissoes())->exigirPermissaoGerenciarAluno($id);
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

        try {
            $aluno = $this->service()->buscar($id);
            // Somente ADM ou diretor da escola do aluno pode visualizar.
            (new Permissoes())->exigirPermissaoGerenciarAluno($id);
        } catch (Exception $e) {
            http_response_code(403);
            echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            return;
        }

        $escolas = (new EscolaService())->listar();

        renderView('aluno/perfil', ['aluno' => $aluno, 'escolas' => $escolas]);
    }

}