<?php

class UsuarioApiController
{
    private ?UsuarioService $service = null;

    private function service(): UsuarioService
    {
        if ($this->service === null) {
            $this->service = new UsuarioService();
        }

        return $this->service;
    }

    public function index(): void
    {
        // O Router já exige autenticação.
        // Aqui verificamos a autorização para listar usuários.
        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para listar usuários.'
            ], 403);
        }

        try {
            $filtros = [];

            // Administradores podem consultar todos os usuários.
            if (Permissoes::temPapel('ADM')) {
                $usuarios = $this->service()->listar($filtros);
            } else {
                // Diretores só podem consultar usuários da própria escola.
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

                $usuarios = $this->service()->listar($filtros);
            }

            Response::json([
                'sucesso' => true,
                'dados' => $usuarios
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível listar os usuários.'
            ], 500);
        }
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do usuário inválido.'
            ], 400);
        }

        try {
            $service = $this->service();

            // O usuário autenticado pode consultar o próprio perfil.
            $idUsuarioSessao = (int) ($_SESSION['usuario']['id'] ?? 0);

            // O administrador pode consultar qualquer usuário.
            if ($id !== $idUsuarioSessao && !Permissoes::temPapel('ADM')) {
                // Apenas diretores podem consultar outros usuários
                // e somente quando pertencem à sua escola.
                if (!Permissoes::temPapel('DIR')) {
                    Response::json([
                        'sucesso' => false,
                        'mensagem' => 'Você não tem permissão para consultar este usuário.'
                    ], 403);
                }

                $idEscola = $service->obterEscolaDoUsuario($id);

                if (
                    $idEscola === null
                    || !Permissoes::podeGerenciarEscola($idEscola)
                ) {
                    Response::json([
                        'sucesso' => false,
                        'mensagem' => 'Você não tem permissão para consultar este usuário.'
                    ], 403);
                }
            }

            $usuario = $service->buscar($id);

            if ($usuario === null) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Usuário não encontrado.'
                ], 404);
            }

            Response::json([
                'sucesso' => true,
                'dados' => $usuario
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível consultar o usuário.'
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
                'mensagem' => 'Você não tem permissão para cadastrar usuários.'
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
                'mensagem' => 'Usuário cadastrado com sucesso.'
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
                'mensagem' => 'Não foi possível cadastrar o usuário.'
            ], 500);
        } catch (Exception $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
                || str_contains(mb_strtolower($mensagem), 'só pode vincular')
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
                'mensagem' => 'Ocorreu um erro interno ao cadastrar o usuário.'
            ], 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do usuário inválido.'
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
            // O usuário autenticado pode editar o próprio perfil.
            $idUsuarioSessao = (int) ($_SESSION['usuario']['id'] ?? 0);

            if (
                $id !== $idUsuarioSessao
                && !Permissoes::temPapel('ADM')
                && !Permissoes::temPapel('DIR')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Você não tem permissão para editar este usuário.'
                ], 403);
            }

            // Diretor só pode editar usuários da própria escola.
            if (
                $id !== $idUsuarioSessao
                && !Permissoes::temPapel('ADM')
                && Permissoes::temPapel('DIR')
            ) {
                $idEscola = $this->service()->obterEscolaDoUsuario($id);

                if (
                    $idEscola === null
                    || !Permissoes::podeGerenciarEscola($idEscola)
                ) {
                    Response::json([
                        'sucesso' => false,
                        'mensagem' => 'Você não tem permissão para editar este usuário.'
                    ], 403);
                }
            }

            $this->service()->atualizar($id, $dados);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Usuário atualizado com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Usuário não encontrado.') {
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
                'mensagem' => 'Não foi possível atualizar o usuário.'
            ], 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do usuário inválido.'
            ], 400);
        }

        if (
            !Permissoes::temPapel('ADM')
            && !Permissoes::temPapel('DIR')
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Você não tem permissão para excluir usuários.'
            ], 403);
        }

        try {
            // Diretor só pode excluir usuários da própria escola.
            if (!Permissoes::temPapel('ADM')) {
                $idEscola = $this->service()->obterEscolaDoUsuario($id);

                if (
                    $idEscola === null
                    || !Permissoes::podeGerenciarEscola($idEscola)
                ) {
                    Response::json([
                        'sucesso' => false,
                        'mensagem' => 'Você não tem permissão para excluir este usuário.'
                    ], 403);
                }
            }

            $this->service()->remover($id);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Usuário excluído com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if ($mensagem === 'Usuário não encontrado.') {
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
                'mensagem' => 'Não foi possível excluir o usuário.'
            ], 500);
        }
    }
}
