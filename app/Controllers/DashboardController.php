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

        $quantidadeAlunos = (new Aluno())->contarPorDiretor((int) $usuario['id']);

        renderView('auth/dashboard', [
            'quantidadeAlunos' => $quantidadeAlunos,
        ]);
    }
}
