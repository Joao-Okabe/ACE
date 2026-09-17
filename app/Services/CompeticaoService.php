<?php

class CompeticaoService
{
    private PDO $pdo;

    private Competicao $competicaoModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->competicaoModel = new Competicao();
    }

    public function criar(array $dados): void
    {
        $nome = trim((string) ($dados['nm_competicao'] ?? ''));
        if ($nome === '') {
            throw new Exception('Informe o nome da competição.');
        }

        $idCriador = (int) ($_SESSION['usuario']['id'] ?? 0);
        if ($idCriador <= 0) {
            throw new Exception('Usuário não autenticado.');
        }

        try {
            $this->pdo->beginTransaction();

            $this->competicaoModel->criar([
                'nm_competicao' => $nome,
                'dt_inicio' => $dados['dt_inicio'] ?? null,
                'dt_encerramento' => $dados['dt_encerramento'] ?? null,
            ], 
                $idCriador
            );

            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function listar(array $filtros = []): array
    {
        return $this->competicaoModel->listar($filtros);
    }

    public function buscar(int $id)
    {
        return $this->competicaoModel->buscar($id);
    }

    public function remover(int $id)
    {
        return $this->competicaoModel->remover($id);
    }
}