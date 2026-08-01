<?php

class authController
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

        require __DIR__ . '/../Views/auth/login.php';
    }

    public function autenticar(): void
    {
        try {
            $usuario = $this->service()->autenticar($_POST);

            session_regenerate_id(true);

            $_SESSION['usuario'] = $usuario;
            $_SESSION['usuario_id'] = $usuario['id'] ?? null;
            $_SESSION['usuario_email'] = $usuario['email'] ?? null;

            header("Location: /dashboard");
            exit;
        } catch (Exception $e) {
            $erro = $e->getMessage();
            $dados = $_POST;

            require __DIR__ . '/../Views/auth/login.php';
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

        header("Location: /login");
        exit;
    }
}
