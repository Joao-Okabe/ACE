<?php

class AuthController
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
        if (!empty($_SESSION['usuario'])) {
            header("Location: /dashboard");
            exit;
        }

        renderView('auth/login');
    }

    public function autenticar(): void
    {
        try {
            $usuario = $this->service()->autenticar($_POST);

            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nome' => $usuario['nm_usuario'],
                'nm_usuario' => $usuario['nm_usuario'],
                'email' => $usuario['email'],
                'foto_perfil' => $usuario['foto_perfil'] ?? null,
                'papeis' => $usuario['papeis']
            ];

            header("Location: /dashboard");
            exit;
        } catch (InvalidArgumentException $e) {
            // Erro de validação (e-mail/senha ausentes ou inválidos).
            $erro = $e->getMessage();
            $dados = $_POST;

            renderView('auth/login', ['erro' => $erro, 'dados' => $dados]);
        } catch (DomainException $e) {
            // Credenciais inválidas ou usuário inativo.
            $erro = $e->getMessage();
            $dados = $_POST;

            renderView('auth/login', ['erro' => $erro, 'dados' => $dados]);
        } catch (Throwable $e) {
            // Erro interno inesperado: registra e exibe mensagem genérica.
            error_log(__METHOD__ . ': ' . $e->getMessage());

            renderView('auth/login', [
                'erro' => 'Erro interno ao autenticar. Tente novamente mais tarde.',
                'dados' => $_POST
            ]);
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

        header("Location: /login");
        exit;
    }
}