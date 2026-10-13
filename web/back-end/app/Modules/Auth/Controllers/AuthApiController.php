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
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'E-mail ou senha inválidos.'
            ], 401);
        }
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        Response::json([
            'sucesso' => true,
            'mensagem' => 'Sessão encerrada com sucesso.'
        ]);
    }
}
