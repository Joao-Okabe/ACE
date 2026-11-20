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
        $papeis = $this->papeisVinculaveis();
        $escolaDiretor = $this->escolaDoDiretor();

        renderView('usuario/cadastrar', [
            'escolas' => $escolas,
            'papeis' => $papeis,
            'escolaDiretor' => $escolaDiretor,
            'podeVincular' => !empty($papeis),
        ]);
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

            renderView('usuario/cadastrar', [
                'erro' => $erro,
                'dados' => $dados,
                'escolas' => $escolas,
                'papeis' => $this->papeisVinculaveis(),
                'escolaDiretor' => $this->escolaDoDiretor(),
                'podeVincular' => !empty($this->papeisVinculaveis()),
            ]);
        }
    }

    private function papeisVinculaveis(): array
    {
        $papeisUsuario = $_SESSION['usuario']['papeis'] ?? [];
        if (!is_array($papeisUsuario) || (!in_array('ADM', $papeisUsuario, true) && !in_array('DIR', $papeisUsuario, true))) {
            return [];
        }

        return [
            ['codigo' => 'ALU', 'nome' => 'Aluno'],
            ['codigo' => 'PRF', 'nome' => 'Professor'],
            ['codigo' => 'AGR', 'nome' => 'Gremista'],
        ];
    }

    private function escolaDoDiretor(): ?int
    {
        $papeisUsuario = $_SESSION['usuario']['papeis'] ?? [];
        if (!in_array('DIR', $papeisUsuario, true) || in_array('ADM', $papeisUsuario, true)) {
            return null;
        }

        return (new VinculoUsuarioEscola())->escolaAtualPorPapeis(
            (int) ($_SESSION['usuario']['id'] ?? 0),
            ['DIR']
        );
    }

    public function edit(): void
    {
        $id = (int) ($_SESSION['usuario']['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /login');
            exit;
        }

        $usuario = $this->service()->buscar($id);
        if ($usuario === null) {
            http_response_code(404);
            echo 'Usuário não encontrado';
            return;
        }

        $vinculos = $this->service()->listarVinculos($id);

        renderView('usuario/editar', [
            'dados' => $usuario,
            'vinculos' => $vinculos
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_SESSION['usuario']['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /login');
            exit;
        }

        try {
            $this->service()->atualizar($id, [
                'nm_usuario' => $_POST['nm_usuario'] ?? '',
                'email' => $_POST['email'] ?? '',
                'senha' => $_POST['senha'] ?? '',
                'foto_perfil' => $_FILES['foto_perfil'] ?? null
            ]);

            $usuario = $this->service()->buscar($id);
            $_SESSION['usuario']['nome'] = $usuario['nm_usuario'];
            $_SESSION['usuario']['nm_usuario'] = $usuario['nm_usuario'];
            $_SESSION['usuario']['email'] = $usuario['email'];
            $_SESSION['usuario']['foto_perfil'] = $usuario['path_ft_usuario'];

            header('Location: /usuarios/editar?sucesso=1');
            exit;
        } catch (Exception $e) {
            renderView('usuario/editar', [
                'erro' => $e->getMessage(),
                'dados' => array_merge($_POST, ['path_ft_usuario' => $_SESSION['usuario']['foto_perfil'] ?? null])
            ]);
        }
    }
}
