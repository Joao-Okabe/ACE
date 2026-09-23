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

        $idsObrigatorios = [
            'cd_formato' => 'formato',
            'cd_esporte' => 'esporte',
            'cd_modalidade' => 'modalidade',
        ];

        foreach ($idsObrigatorios as $campo => $descricao) {
            if ((int) ($dados[$campo] ?? 0) <= 0) {
                throw new Exception("Selecione o {$descricao} da competição.");
            }
        }

        $idCriador = (int) ($_SESSION['usuario']['id'] ?? 0);
        if ($idCriador <= 0) {
            throw new Exception('Usuário não autenticado.');
        }

        try {
            $this->pdo->beginTransaction();

            $this->competicaoModel->criar([
                'nm_competicao' => $nome,
                'cd_formato' => $dados['cd_formato'] ?? null,
                'cd_esporte' => $dados['cd_esporte'] ?? null,
                'cd_modalidade' => $dados['cd_modalidade'] ?? null,
                'inicio_em' => $dados['dt_inicio'] ?? null,
                'fim_em' => $dados['dt_encerramento'] ?? null,
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

    public function buscar(int $id): array
    {
        $competicao = $this->competicaoModel->buscar($id);

        if ($competicao === null) {
            throw new Exception('Competição não encontrada.');
        }

        return $competicao;
    }

    public function atualizar(int $id, array $dados): void
    {
        $nome = trim((string) ($dados['nm_competicao'] ?? ''));
        if ($nome === '') {
            throw new Exception('Informe o nome da competição.');
        }

        $this->buscar($id);
        $this->competicaoModel->atualizar($id, [
            'nm_competicao' => $nome,
            'inicio_em' => $dados['dt_inicio'] ?? null,
            'fim_em' => $dados['dt_encerramento'] ?? null,
        ]);
    }

    public function remover(int $id): void
    {
        $this->competicaoModel->remover($id);
    }
}