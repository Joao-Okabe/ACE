<?php

class CompeticaoService
{
    private PDO $pdo;

    private Competicao $competicaoModel;

    private VinculoUsuarioEscola $vinculoEscolaModel;

    private Confronto $confrontoModel;

    private EtapaCompeticao $etapaCompeticaoModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->competicaoModel = new Competicao();

        $this->vinculoEscolaModel = new VinculoUsuarioEscola();

        $this->confrontoModel = new Confronto();

        $this->etapaCompeticaoModel = new EtapaCompeticao();
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
        $idCriador = (int) ($competicao['cd_criador'] ?? 0);

        if ($idCriador === $idUsuario || $this->usuarioEhAdm($idUsuario)) {
            return;
        }

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

    public function criarInscricao(int $idCompeticao, array $dados): void
    {
        $competicao = $this->buscar($idCompeticao);
        $this->exigirPermissao($competicao);

        $inicio = trim((string) ($dados['dt_inicio_inscricao'] ?? $dados['dt_inicio'] ?? ''));
        $encerramento = trim((string) ($dados['dt_encerramento_inscricao'] ?? $dados['dt_encerramento'] ?? ''));

        if ($inicio === '') {
            throw new Exception('Informe a data de início das inscrições.');
        }

        if ($encerramento !== '' && $encerramento < $inicio) {
            throw new Exception('A data de encerramento deve ser maior ou igual à data de início.');
        }

        $this->competicaoModel->criarInscricao($idCompeticao, [
            'dt_inicio_inscricao' => $inicio,
            'dt_encerramento_inscricao' => $encerramento !== '' ? $encerramento : null,
        ]);
    }

    public function buscarPeriodoInscricao(int $idCompeticao): ?array
    {
        return $this->competicaoModel->buscarPeriodoInscricao($idCompeticao);
    }

    public function listarTimesInscritos(int $idCompeticao): array
    {
        return $this->competicaoModel->listarTimesInscritos($idCompeticao);
    }

    public function listarTimesDisponiveisInscricao(int $idCompeticao): array
    {
        return $this->competicaoModel->listarTimesDisponiveisInscricao($idCompeticao);
    }

    public function inscreverTime(int $idCompeticao, int $idTime): void
    {
        if ($idTime <= 0) {
            throw new Exception('Selecione um time para inscrever.');
        }

        $competicao = $this->buscar($idCompeticao);
        $this->exigirPermissao($competicao);

        $periodo = $this->competicaoModel->buscarPeriodoInscricao($idCompeticao);
        if ($periodo === null) {
            throw new Exception('Cadastre o período de inscrição antes de inscrever times.');
        }

        $this->competicaoModel->inscreverTime((int) $periodo['cd_inscricao_competicao'], $idTime);
    }

    public function removerTimeInscrito(int $idCompeticao, int $idTime): void
    {
        if ($idTime <= 0) {
            throw new Exception('Time inválido.');
        }

        $competicao = $this->buscar($idCompeticao);
        $this->exigirPermissao($competicao);

        $this->competicaoModel->removerTimeInscrito($idCompeticao, $idTime);
    }

    private function usuarioEhAdm(int $idUsuario): bool
    {
        $papeis = $_SESSION['usuario']['papeis'] ?? [];

        return in_array('ADM', $papeis, true);
    }

    public function listarChaveamento(int $idCompeticao): array
    {
        return $this->confrontoModel->listarChaveamento($idCompeticao);
    }

    /**
     * Indica se o usuário logado é o criador da competição.
     */
    public function usuarioEhCriador(array $competicao): bool
    {
        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);
        $idCriador = (int) ($competicao['cd_criador'] ?? 0);

        return $idUsuario > 0 && $idUsuario === $idCriador;
    }

    public function gerarChaveamento(int $idCompeticao): void
    {
        $competicao = $this->buscar($idCompeticao);

        if (!$this->usuarioEhCriador($competicao)) {
            throw new Exception('Apenas o criador da competição pode gerar o chaveamento.');
        }

        if ($this->confrontoModel->listarChaveamento($idCompeticao) !== []) {
            throw new Exception('Esta competição já possui um chaveamento.');
        }

        $etapa = $this->etapaCompeticaoModel->buscarEliminatoriaPorCompeticao($idCompeticao);

        if ($etapa === null) {
            $cdTipoEtapa = $this->etapaCompeticaoModel->buscarTipoPorNome('ELIMINATORIA');

            if ($cdTipoEtapa === null) {
                throw new Exception('Tipo de etapa eliminatória não cadastrado.');
            }

            $cdEtapa = $this->etapaCompeticaoModel->criar([
                'cd_competicao' => $idCompeticao,
                'cd_tipo_etapa' => $cdTipoEtapa,
                'nm_etapa' => 'Eliminatória',
                'ordem' => 1,
                'descricao' => 'Etapa de eliminatória simples da competição.',
            ]);
        } else {
            $cdEtapa = (int) $etapa['cd_etapa_competicao'];
        }

        (new EliminatoriaSimplesService())->gerar($idCompeticao, $cdEtapa);
    }
}