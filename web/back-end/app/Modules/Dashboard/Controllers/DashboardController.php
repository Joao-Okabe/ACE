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

    public function apiV1(): void
    {
        $idUsuario = AuthContext::id();
        if ($idUsuario === null) {
            Response::error('UNAUTHENTICATED', 'Autenticação necessária.', 401);
        }

        try {
            $dashboard = $this->dashboard();
            $partidas = new PartidaService();
            $competicoes = (new CompeticaoService())->listar();
            $emAndamento = $partidas->listar(
                ['status' => 'EM_ANDAMENTO', 'limite' => 4],
                $idUsuario
            );
            $proximas = $partidas->listar(
                ['futuros' => true, 'limite' => 5],
                $idUsuario
            );
            $hoje = $partidas->listar(['hoje' => true, 'limite' => 100], $idUsuario);

            Response::success([
                'resumo' => [
                    'jogos_hoje' => count($hoje),
                    'competicoes' => count($competicoes),
                    'times' => $dashboard->qtTime($idUsuario),
                    'atletas' => $dashboard->qtAluno($idUsuario),
                ],
                'partidasAoVivo' => $emAndamento,
                'proximosJogos' => $proximas,
                'rankingEscolas' => [],
                'atualizadoEm' => gmdate(DATE_ATOM),
            ], 'Painel consultado.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar o painel.', 500);
        }
    }
}
