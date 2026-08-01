<?php

class dashboardController
{
    public function index(): void
    {
        $usuario = $_SESSION['usuario'] ?? null;

        if ($usuario === null) {
            header('Location: /login');
            exit;
        }

        require __DIR__ . '/../Views/auth/dashboard.php';
    }
}