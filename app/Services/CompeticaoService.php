<?php

class CompeticaoService
{
    private PDO $pdo;

    private Competicao $competicaoModel;

    private VinculoUsuarioEscola $vinculoEscolaModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->competicaoModel = new Competicao();
        $this->vinculoEscolaModel = new VinculoUsuarioEscola();
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

        $idEscola = $this->resolverEscolaCompeticao($dados, $idCriador);

        try {
            $this->pdo->beginTransaction();

            $this->competicaoModel->criar([
                'nm_competicao' => $nome,
                'cd_formato' => $dados['cd_formato'] ?? null,
                'cd_esporte' => $dados['cd_esporte'] ?? null,
                'cd_modalidade' => $dados['cd_modalidade'] ?? null,
                'cd_escola' => $idEscola,
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

        $competicao = $this->buscar($id);
        $this->exigirPermissao($competicao);
        $this->competicaoModel->atualizar($id, [
            'nm_competicao' => $nome,
            'inicio_em' => $dados['dt_inicio'] ?? null,
            'fim_em' => $dados['dt_encerramento'] ?? null,
        ]);
    }

    public function remover(int $id): void
    {
        $this->exigirPermissao($this->buscar($id));
        $this->competicaoModel->remover($id);
    }

    public function exigirPermissao(array $competicao): void
    {
        $idEscola = (int) ($competicao['cd_escola'] ?? 0);
        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);

        if ($idEscola <= 0 || !$this->vinculoEscolaModel->usuarioPodeGerenciarEscola($idUsuario, $idEscola)) {
            throw new Exception('Você não tem permissão para gerenciar esta competição.');
        }
    }

    private function resolverEscolaCompeticao(array $dados, int $idUsuario): int
    {
        $papeis = $_SESSION['usuario']['papeis'] ?? [];
        if (in_array('ADM', $papeis, true)) {
            $idEscola = (int) ($dados['cd_escola'] ?? 0);
        } else {
            $idEscola = (int) ($this->vinculoEscolaModel->escolaGerenciavelPorUsuario($idUsuario) ?? 0);
        }

        if ($idEscola <= 0 || !$this->vinculoEscolaModel->usuarioPodeGerenciarEscola($idUsuario, $idEscola)) {
            throw new Exception('Você não tem permissão para criar competição nesta escola.');
        }

        return $idEscola;
    }
}