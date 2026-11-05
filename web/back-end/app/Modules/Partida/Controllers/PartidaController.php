<?php

class PartidaController
{
    private ?PartidaService $service = null;

    private function service(): PartidaService
    {
        if ($this->service === null) {
            $this->service = new PartidaService();
        }

        return $this->service;
    }

    private function usuarioAtual(): int
    {
        $id = (int) ($_SESSION['id'] ?? $_SESSION['cd_usuario'] ?? 0);

        if ($id <= 0) {
            throw new RuntimeException('Sessão inválida. Faça login novamente.');
        }

        return $id;
    }

    public function create(): void
    {
        try {
            $idCompeticao = isset($_GET['id_competicao']) ? (int) $_GET['id_competicao'] : null;

            if ($idCompeticao) {
                $this->service()->exigirAcessoCompeticao($this->usuarioAtual(), $idCompeticao);
            }

            $esporteService = new EsporteService();
            $modalidadeService = new ModalidadeService();
            $timeService = new TimeService();

            renderView('partida/criar', [
                'idCompeticao' => $idCompeticao,
                'esportes' => $esporteService->listar(),
                'modalidades' => $modalidadeService->listar(),
                'times' => $timeService->listar(),
            ]);
        } catch (Throwable $e) {
            http_response_code(403);
            renderView('erro', ['erro' => $e->getMessage()]);
        }
    }

    public function store(): void
    {
        try {
            $idUsuario = $this->usuarioAtual();
            $dados = $_POST;

            // Campos opcionais: sem competição = partida avulsa;
            // sem confronto = partida independente dentro da competição.
            $idPartida = $this->service()->criar($dados, $idUsuario);

            $idCompeticao = (int) ($dados['cd_competicao'] ?? 0);

            if ($idCompeticao > 0) {
                header("Location: /competicoes/visualizar?id={$idCompeticao}&partida_criada=1");
            } else {
                header("Location: /partidas/visualizar?id={$idPartida}&partida_criada=1");
            }
            exit;
        } catch (Throwable $e) {
            http_response_code(422);
            renderView('partida/criar', [
                'erro' => $e->getMessage(),
                'dados' => $_POST,
            ]);
        }
    }

    public function show(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $partida = $this->service()->buscar($id, $this->usuarioAtual());

            renderView('partida/visualizar', ['partida' => $partida]);
        } catch (Throwable $e) {
            http_response_code(404);
            renderView('erro', ['erro' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filtros = [
                'cd_competicao' => isset($_GET['cd_competicao']) ? (int) $_GET['cd_competicao'] : null,
                'status' => trim($_GET['status'] ?? ''),
                'visibilidade' => trim($_GET['visibilidade'] ?? ''),
            ];

            $partidas = $this->service()->listar($filtros, $this->usuarioAtual());

            renderView('partida/listar', [
                'partidas' => $partidas,
                'filtros' => $filtros,
            ]);
        } catch (Throwable $e) {
            http_response_code(403);
            renderView('erro', ['erro' => $e->getMessage()]);
        }
    }
}
