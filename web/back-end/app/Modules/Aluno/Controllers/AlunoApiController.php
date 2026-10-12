<?php

class AlunoApiController
{
    private ?AlunoService $service = null;

    private function service(): AlunoService
    {
        if ($this->service === null) {
            $this->service = new AlunoService();
        }

        return $this->service;
    }

    public function index(): void
    {
        // O Router já exige autenticação.
        // Aqui verificamos a autorização para listar alunos.
        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para listar alunos.'
            ], 403);
        }

        try {
            $filtros = [];

            // Administradores podem consultar todos os alunos.
            if (Permissoes::temPapel('ADM')) {
                $alunos = $this->service()->listar($filtros);
            } else {
                // Diretores só podem consultar alunos da própria escola.
                $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);

                $vinculoModel = new VinculoUsuarioEscola();

                $idEscola = $vinculoModel->escolaAtualPorPapeis(
                    $idUsuario,
                    ['DIR']
                );

                if ($idEscola === null || $idEscola <= 0) {
                    Response::json([
                        'sucesso' => false,
                        'mensagem' => 'Nenhuma escola autorizada foi encontrada.'
                    ], 403);
                }

                $filtros['escola'] = $idEscola;

                $alunos = $this->service()->listar($filtros);
            }

            Response::json([
                'sucesso' => true,
                'dados' => $alunos
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível listar os alunos.'
            ], 500);
        }
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do aluno inválido.'
            ], 400);
        }

        try {
            $service = $this->service();

            // O administrador pode consultar qualquer aluno.
            if (!Permissoes::temPapel('ADM')) {
                // Apenas diretores podem consultar alunos
                // e somente quando pertencem à sua escola.
                if (!Permissoes::temPapel('DIR')) {
                    Response::json([
                        'sucesso' => false,
                        'mensagem' => 'Você não tem permissão para consultar este aluno.'
                    ], 403);
                }

                $idEscola = $service->obterEscolaDoAluno($id);

                if (
                    $idEscola === null
                    || !Permissoes::podeGerenciarEscola($idEscola)
                ) {
                    Response::json([
                        'sucesso' => false,
                        'mensagem' => 'Você não tem permissão para consultar este aluno.'
                    ], 403);
                }
            }

            $aluno = $service->buscar($id);

            Response::json([
                'sucesso' => true,
                'dados' => $aluno
            ]);
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Aluno(a) não encontrado(a).') {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Aluno não encontrado.'
                ], 404);
            }

            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível consultar o aluno.'
            ], 500);
        }
    }

    public function store(): void
    {
        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para cadastrar alunos.'
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
        } else {
            // Aceita multipart/form-data e application/x-www-form-urlencoded.
            $dados = $_POST;
        }

        try {
            $this->service()->cadastrar($dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Aluno cadastrado com sucesso.'
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
                'mensagem' => 'Não foi possível cadastrar o aluno.'
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
                'mensagem' => 'Ocorreu um erro interno ao cadastrar o aluno.'
            ], 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do aluno inválido.'
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
        } else {
            $dados = $_POST;
        }

        try {
            if (
                !Permissoes::temPapel('ADM')
                && !Permissoes::temPapel('DIR')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Você não tem permissão para editar alunos.'
                ], 403);
            }

            $this->service()->atualizar($id, $dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Aluno atualizado com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Aluno não encontrado.') {
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
                'mensagem' => 'Não foi possível atualizar o aluno.'
            ], 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do aluno inválido.'
            ], 400);
        }

        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para excluir alunos.'
            ], 403);
        }

        try {
            $this->service()->remover($id);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Aluno excluído com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Aluno não encontrado.') {
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
                'mensagem' => 'Não foi possível excluir o aluno.'
            ], 500);
        }
    }

}
