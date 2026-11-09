<?php

class AuthApiController
{
    private ?AuthService $service = null;

    private function service(): AuthService
    {
        if ($this->service === null) {
            $this->service = new AuthService();
        }

        return $this->service;
    }

    public function login(): void
    {
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
            // Aceita application/x-www-form-urlencoded e multipart.
            $dados = $_POST;
        }

        try {
            $usuario = $this->service()->autenticar($dados);

            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nome' => $usuario['nm_usuario'],
                'nm_usuario' => $usuario['nm_usuario'],
                'email' => $usuario['email'],
                'foto_perfil' => $usuario['foto_perfil'] ?? null,
                'papeis' => $usuario['papeis']
            ];

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Autenticado com sucesso.',
                'dados' => [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nm_usuario'],
                    'email' => $usuario['email'],
                    'papeis' => $usuario['papeis']
                ]
            ]);
        } catch (InvalidArgumentException $e) {
            // Erro de validação (e-mail/senha ausentes ou inválidos).
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 422);
        } catch (DomainException $e) {
            // Credenciais inválidas ou usuário inativo.
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 401);
        } catch (Throwable $e) {
            // Erro interno inesperado: nunca expõe detalhes ao cliente.
            error_log(__METHOD__ . ': ' . $e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Erro interno ao autenticar. Tente novamente mais tarde.'
            ], 500);
        }
    }

    public function me(): void
    {
        if (empty($_SESSION['usuario']['id'])) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não autenticado.'
            ], 401);
        }

        Response::json([
            'sucesso' => true,
            'dados' => $_SESSION['usuario']
        ]);
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => 'Lax'
                ]
            );
        }

        session_destroy();

        Response::json([
            'sucesso' => true,
            'mensagem' => 'Sessão encerrada com sucesso.'
        ]);
    }
}
