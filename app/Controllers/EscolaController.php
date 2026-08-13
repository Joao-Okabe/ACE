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

    // Tranca/Destranca escola (apenas Diretores vinculados podem executar)
    public function trancar(): void
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

        $idUsuario = (int) $_SESSION['usuario']['id'];

        $vinculoModel = new VinculoUsuarioEscola();

        if (!$vinculoModel->isUsuarioDiretor($idUsuario, $id)) {
            http_response_code(403);
            echo 'Acesso negado.';
            return;
        }

        try {
            $escola = $this->service()->buscar($id);
            $novaAtiva = !((bool) ($escola['ativa'] ?? false));

            $this->service()->definirAtiva($id, $novaAtiva);
            // flash
            $_SESSION['flash'] = ['success' => $novaAtiva ? 'Escola ativada.' : 'Escola trancada.'];

            header("Location: /escolas/visualizar?id={$id}");
            exit;
        } catch (Exception $e) {
            $_SESSION['flash'] = ['error' => $e->getMessage()];
            header("Location: /escolas/visualizar?id={$id}");
            exit;
        }
    }
}
