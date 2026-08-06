<?php

class DashboardController
{
    public function index(): void
    {
        $usuario = $_SESSION['usuario'] ?? null;

        if ($usuario === null) {
            header('Location: /login');
            exit;
        }

        renderView('auth/dashboard');
    }
}