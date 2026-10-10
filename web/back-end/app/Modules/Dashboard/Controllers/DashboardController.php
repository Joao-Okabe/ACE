<?php

class DashboardController
{
    private ?Dashboard $dashboardModel = null;

    private function dashboard(): Dashboard
    {
        if ($this->dashboardModel === null) {
            $this->dashboardModel = new Dashboard();
        }

        return $this->dashboardModel;
    }

    public function index(): void
    {
        $usuario = $_SESSION['usuario'] ?? null;

        if ($usuario === null) {
            header('Location: /login');
            exit;
        }

        $qtAluno = $this->dashboard()->qtAluno((int) $usuario['id']);
        $qtTime = $this->dashboard()->qtTime((int) $usuario['id']);
        $qtCompeticao = $this->dashboard()->qtCompeticao((int) $usuario['id']);

        renderView('auth/dashboard', [
            'qtAluno' => $qtAluno,
            'qtTime' => $qtTime,
            'qtCompeticao' => $qtCompeticao
        ]);
    }
}
