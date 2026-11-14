<?php

class EscolaApiController
{
    private ?EscolaService $service = null;

    private function service(): EscolaService
    {
        if ($this->service === null) {
            $this->service = new EscolaService();
        }

        return $this->service;
    }

    public function index(): void
    {
        // O Router exige autenticação; qualquer usuário autenticado pode listar escolas.
        try {
            $escolas = $this->service()->listar();

            Response::json([
                'sucesso' => true,
                'dados' => $escolas
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível listar as escolas.'
            ], 500);
        }
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID da escola inválido.'
            ], 400);
        }

        try {
            // O Router exige autenticação; qualquer usuário autenticado pode consultar escolas.
            $escola = $this->service()->buscar($id);

            Response::json([
                'sucesso' => true,
                'dados' => $escola
            ]);
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Escola não encontrada.') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Escola não encontrada.'
                ], 404);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível consultar a escola.'
            ], 500);
        }
    }

    public function store(): void
    {
        // Somente o administrador cadastra escolas.
        if (!Permissoes::temPapel('ADM')) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para cadastrar escolas.'
            ], 403);
        }

        $contentType = strtolower(
            $_SERVER['CONTENT_TYPE'] ?? ''
        );

        if (str_contains($contentType, 'application/json')) {
            $corpo = file_get_contents('php://input');
            $dados = json_decode($corpo, true);

            if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'O corpo da requisição contém JSON inválido.'
                ], 400);
            }

            // O upload do logo usa multipart/form-data.
            if (isset($_FILES['img_logo_upload'])) {
                $dados['img_logo_upload'] = $_FILES['img_logo_upload'];
            }
        } else {
            // Aceita multipart/form-data e application/x-www-form-urlencoded.
            $dados = $_POST;

            if (isset($_FILES['img_logo_upload'])) {
                $dados['img_logo_upload'] = $_FILES['img_logo_upload'];
            }
        }

        try {
            $this->service()->cadastrar($dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Escola cadastrada com sucesso.'
            ], 201);
        } catch (InvalidArgumentException $e) {
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 422);
        } catch (PDOException $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível cadastrar a escola.'
            ], 500);
        } catch (Exception $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
                || str_contains(mb_strtolower($mensagem), 'somente')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            // Regras de negócio e validações do serviço.
            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Ocorreu um erro interno ao cadastrar a escola.'
            ], 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID da escola inválido.'
            ], 400);
        }

        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para editar escolas.'
            ], 403);
        }

        $contentType = strtolower(
            $_SERVER['CONTENT_TYPE'] ?? ''
        );

        if (str_contains($contentType, 'application/json')) {
            $corpo = file_get_contents('php://input');
            $dados = json_decode($corpo, true);

            if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'O corpo da requisição contém JSON inválido.'
                ], 400);
            }

            if (isset($_FILES['img_logo_upload'])) {
                $dados['img_logo_upload'] = $_FILES['img_logo_upload'];
            }
        } else {
            $dados = $_POST;

            if (isset($_FILES['img_logo_upload'])) {
                $dados['img_logo_upload'] = $_FILES['img_logo_upload'];
            }
        }

        try {
            if (
                !Permissoes::temPapel('ADM')
                && !Permissoes::temPapel('DIR')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Você não tem permissão para editar escolas.'
                ], 403);
            }

            // Diretor só pode editar a própria escola.
            if (!Permissoes::temPapel('ADM') && !Permissoes::podeGerenciarEscola($id)) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Você não tem permissão para editar esta escola.'
                ], 403);
            }

            $this->service()->atualizar($id, $dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Escola atualizada com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Escola não encontrada.') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 404);
            }

            if (
                str_contains(
                    mb_strtolower($mensagem),
                    'permissão'
                )
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            if ($e instanceof InvalidArgumentException) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 422);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível atualizar a escola.'
            ], 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID da escola inválido.'
            ], 400);
        }

        // Somente administradores e diretores podem excluir escolas.
        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para excluir escolas.'
            ], 403);
        }

        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::podeGerenciarEscola($id)
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para excluir esta escola.'
            ], 403);
        }

        try {
            // Garante o 404 quando a escola não existe.
            $this->service()->buscar($id);

            $this->service()->remover($id);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Escola excluída com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Escola não encontrada.') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 404);
            }

            if (
                str_contains(
                    mb_strtolower($mensagem),
                    'permissão'
                )
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível excluir a escola.'
            ], 500);
        }
    }
}
