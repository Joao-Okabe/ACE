<?php

class TimeApiController
{
    private ?TimeService $service = null;

    private function service(): TimeService
    {
        if ($this->service === null) {
            $this->service = new TimeService();
        }

        return $this->service;
    }

    public function index(): void
    {
        try {
            $filtros = [
                'nome' => trim($_GET['nome'] ?? ''),
                'categoria' => trim($_GET['categoria'] ?? ''),
                'ordem' => strtolower($_GET['ordem'] ?? 'asc'),
            ];

            if (!in_array($filtros['ordem'], ['asc', 'desc'], true)) {
                $filtros['ordem'] = 'asc';
            }

            $times = $this->service()->listar($filtros);

            Response::json([
                'sucesso' => true,
                'dados' => $times
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível listar os times.'
            ], 500);
        }
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        try {
            $time = $this->service()->buscar($id);

            Response::json([
                'sucesso' => true,
                'dados' => $time
            ]);
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Time não encontrado.') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Time não encontrado.'
                ], 404);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível consultar o time.'
            ], 500);
        }
    }

    public function store(): void
    {
        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
            && !Permissoes::temPapel('CRD')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para cadastrar times.'
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

            $dados['path_escudo'] = null;
        } else {
            // Aceita multipart/form-data (para envio do escudo)
            // e application/x-www-form-urlencoded.
            $dados = $_POST;
            $dados['path_escudo'] = $_FILES['path_escudo'] ?? null;
        }

        try {
            $this->service()->cadastrar($dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Time cadastrado com sucesso.'
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
                'mensagem' => 'Não foi possível cadastrar o time.'
            ], 500);
        } catch (Exception $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
                || str_contains(mb_strtolower($mensagem), 'somente diretor')
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
                'mensagem' => 'Ocorreu um erro interno ao cadastrar o time.'
            ], 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
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

            $dados['path_escudo'] = null;
        } else {
            $dados = $_POST;
            $dados['path_escudo'] = $_FILES['path_escudo'] ?? null;
        }

        try {
            if (!Permissoes::temPapel('ADM')) {
                (new VinculoTimeService())->exigirPermissaoGerenciarTime($id);
            }

            $this->service()->atualizar($id, $dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Time atualizado com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Time não encontrado.') {
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
                'mensagem' => 'Não foi possível atualizar o time.'
            ], 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        try {
            if (!Permissoes::temPapel('ADM')) {
                (new VinculoTimeService())->exigirPermissaoGerenciarTime($id);
            }

            $this->service()->remover($id);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Time excluído com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Time não encontrado.') {
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
                'mensagem' => 'Não foi possível excluir o time.'
            ], 500);
        }
    }
}
