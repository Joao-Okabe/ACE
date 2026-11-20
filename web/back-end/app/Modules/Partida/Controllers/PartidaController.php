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
        $id = AuthContext::id() ?? 0;

        if ($id <= 0) {
            Response::error('UNAUTHENTICATED', 'Autenticação necessária.', 401);
        }

        return $id;
    }

    public function store(): void
    {
        $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
        if (str_contains($contentType, 'application/json')) {
            $dados = json_decode(file_get_contents('php://input'), true);
            if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
                Response::json(['sucesso' => false, 'mensagem' => 'O corpo da requisição contém JSON inválido.'], 400);
            }
        } else {
            $dados = $_POST;
        }

        try {
            $idPartida = $this->service()->criar($dados);
            Response::json([
                'sucesso' => true,
                'mensagem' => 'Partida criada com sucesso.',
                'dados' => ['cd_partida' => $idPartida],
            ], 201);
        } catch (InvalidArgumentException $e) {
            Response::json(['sucesso' => false, 'mensagem' => $e->getMessage()], 422);
        } catch (DomainException $e) {
            $mensagem = $e->getMessage();
            $status = str_contains(mb_strtolower($mensagem), 'permissão') ? 403
                : (str_contains(mb_strtolower($mensagem), 'não encontrado') ? 404 : 409);
            Response::json(['sucesso' => false, 'mensagem' => $mensagem], $status);
        } catch (Exception $e) {
            $mensagem = mb_strtolower($e->getMessage());
            if (str_contains($mensagem, 'não autenticado')) {
                Response::json(['sucesso' => false, 'mensagem' => 'Autenticação necessária.'], 401);
            }
            if (str_contains($mensagem, 'permissão')) {
                Response::json(['sucesso' => false, 'mensagem' => $e->getMessage()], 403);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::json(['sucesso' => false, 'mensagem' => 'Não foi possível criar a partida.'], 500);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::json(['sucesso' => false, 'mensagem' => 'Não foi possível criar a partida.'], 500);
        }
    }

    public function index(): void
    {
        $filtros = [];
        foreach (['cd_competicao', 'status', 'visibilidade', 'de', 'ate', 'local'] as $campo) {
            if (!isset($_GET[$campo])) {
                continue;
            }
            if (!is_string($_GET[$campo])) {
                Response::json(['sucesso' => false, 'mensagem' => "Parâmetro {$campo} inválido."], 400);
            }
            $filtros[$campo] = trim($_GET[$campo]);
        }

        if (isset($filtros['cd_competicao'])) {
            $idCompeticao = filter_var($filtros['cd_competicao'], FILTER_VALIDATE_INT);
            if ($idCompeticao === false || $idCompeticao <= 0) {
                Response::json(['sucesso' => false, 'mensagem' => 'Parâmetro cd_competicao inválido.'], 400);
            }
            $filtros['cd_competicao'] = $idCompeticao;
        }

        try {
            $partidas = $this->service()->listar($filtros, $this->usuarioAtual());
            Response::json(['sucesso' => true, 'dados' => $partidas]);
        } catch (Throwable $e) {
            if (str_contains(mb_strtolower($e->getMessage()), 'permissão')) {
                Response::json(['sucesso' => false, 'mensagem' => $e->getMessage()], 403);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::json(['sucesso' => false, 'mensagem' => 'Não foi possível listar as partidas.'], 500);
        }
    }

    public function show(int|string $id): void
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            Response::json(['sucesso' => false, 'mensagem' => 'ID da partida inválido.'], 400);
        }

        try {
            $partida = $this->service()->buscar($id, $this->usuarioAtual());
            Response::json(['sucesso' => true, 'dados' => $partida]);
        } catch (Throwable $e) {
            if (str_contains(mb_strtolower($e->getMessage()), 'permissão') || str_contains(mb_strtolower($e->getMessage()), 'não encontrada')) {
                Response::json(['sucesso' => false, 'mensagem' => 'Partida não encontrada.'], 404);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::json(['sucesso' => false, 'mensagem' => 'Não foi possível consultar a partida.'], 500);
        }
    }
}
