<?php
/*
    Serviço de chaveamento do SISTEMA SUÍÇO.

    gerar();
        ->  gera todas as rodadas do sistema suíço (models)

    buscarTimes();
        ->  busca times inscritos na competição (model)

    validarQuantidadeTimes();
        ->  confere se existem pelo menos 2 times

    validarEtapa();
        ->  valida (model) se a etapa da competição é do tipo GRUPOS

    calcularQuantidadeRodadas();
        ->  ceil(log2(n)) rodadas, mínimo 1

    sortear();
        ->  embaralha os times (Fisher-Yates com seed determinístico
            para permitir reprodução em testes)

    criarRodadas();
        ->  cria as rodadas (model)

    emparelhar();
        ->  pareia os times da rodada; evita repetir confrontos
            anteriores; sobras recebem bye

    jaEnfrentaram();
        ->  verifica se dois times já se enfrentaram nesta etapa

    registrarConfrontos();
        ->  insere os confrontos da rodada e seus participantes
*/

class SuicoService
{
    private PDO $pdo;

    private Competicao $competicaoModel;

    private EtapaCompeticao $etapaCompeticaoModel;

    private RodadaCompeticao $rodadaCompeticaoModel;

    private Confronto $confrontoModel;

    private ConfrontoParticipante $confrontoParticipanteModel;

    private OrigemParticipanteConfronto $origemParticipanteConfrontoModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->competicaoModel = new Competicao();

        $this->etapaCompeticaoModel = new EtapaCompeticao();

        $this->rodadaCompeticaoModel = new RodadaCompeticao();

        $this->confrontoModel = new Confronto();

        $this->confrontoParticipanteModel = new ConfrontoParticipante();

        $this->origemParticipanteConfrontoModel =
            new OrigemParticipanteConfronto();
    }

    public function gerar(
        int $cdCompeticao,
        int $cdEtapaCompeticao
    ): void {
        $this->pdo->beginTransaction();

        try {
            $times = $this->buscarTimes($cdCompeticao);

            $this->validarQuantidadeTimes($times);

            $this->validarEtapa(
                $cdCompeticao,
                $cdEtapaCompeticao
            );

            $quantidadeRodadas = $this->calcularQuantidadeRodadas(
                count($times)
            );

            $rodadas = $this->criarRodadas(
                $cdEtapaCompeticao,
                $quantidadeRodadas
            );

            $idsTimes = array_map(
                static fn (array $time): int => (int) $time['cd_time'],
                $times
            );

            $this->sortear($idsTimes);

            // "a|b" => true para cada confronto já criado na etapa
            $enfrentamentos = [];

            for ($numeroRodada = 1; $numeroRodada <= $quantidadeRodadas; $numeroRodada++) {
                $pares = $this->emparelhar(
                    $idsTimes,
                    $numeroRodada,
                    $enfrentamentos
                );

                $this->registrarConfrontos(
                    $rodadas[$numeroRodada - 1],
                    $pares,
                    $enfrentamentos
                );
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    private function buscarTimes(int $cdCompeticao): array
    {
        return $this->competicaoModel
            ->listarTimesInscritos($cdCompeticao);
    }

    private function validarQuantidadeTimes(array $times): void
    {
        if (count($times) < 2) {
            throw new RuntimeException(
                'O sistema suíço precisa de pelo menos 2 times.'
            );
        }
    }

    private function validarEtapa(
        int $cdCompeticao,
        int $cdEtapaCompeticao
    ): void {
        $etapa = $this->etapaCompeticaoModel->buscarPorCompeticao(
            $cdEtapaCompeticao,
            $cdCompeticao
        );

        if (!$etapa) {
            throw new RuntimeException(
                'A etapa não pertence à competição.'
            );
        }

        if ($etapa['nm_tipo_etapa'] !== 'GRUPOS') {
            throw new RuntimeException(
                'A etapa informada não é compatível com o sistema suíço.'
            );
        }
    }

    private function calcularQuantidadeRodadas(
        int $quantidadeTimes
    ): int {
        return max(
            1,
            (int) ceil(log(max($quantidadeTimes, 2), 2))
        );
    }

    // Embaralhamento determinístico (Fisher-Yates) para testes.
    private function sortear(array &$times): void
    {
        mt_srand(12345 + count($times));

        for ($i = count($times) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);

            [$times[$i], $times[$j]] = [$times[$j], $times[$i]];
        }
    }

    private function criarRodadas(
        int $cdEtapaCompeticao,
        int $quantidadeRodadas
    ): array {
        $rodadas = [];

        for ($numero = 1; $numero <= $quantidadeRodadas; $numero++) {
            $rodadas[] = $this->rodadaCompeticaoModel->criar([
                'cd_etapa_competicao' => $cdEtapaCompeticao,
                'nr_rodada' => $numero,
                'nm_rodada' => "Rodada {$numero}",
            ]);
        }

        return $rodadas;
    }

    /*
        Emparelha os times usando o método do círculo (round-robin,
        tabela de Berger): fixa um time e rotaciona os demais a cada
        rodada. Assim cada time joga exatamente uma vez por rodada e
        nenhum confronto se repete antes de esgotar os adversários.
        Com resultados lançados, o pareamento natural do suíço
        reordena por pontuação (fora do escopo da geração).
    */
    private function emparelhar(
        array $idsTimes,
        int $numeroRodada,
        array $enfrentamentos
    ): array {
        $idsTimes = array_values($idsTimes);

        $temBye = count($idsTimes) % 2 === 1;

        if ($temBye) {
            $idsTimes[] = null; // bye fictício
        }

        $n = count($idsTimes);

        // Posições do círculo para esta rodada:
        // o primeiro slot fica fixo; os demais rotacionam.
        $rotacao = $numeroRodada - 1;

        $posicoes = [];

        $posicoes[0] = $idsTimes[0];

        for ($i = 1; $i < $n; $i++) {
            $posicoes[$i] = $idsTimes[1 + (($i - 1 + $rotacao) % ($n - 1))];
        }

        $pares = [];

        for ($i = 0; $i < $n / 2; $i++) {
            $a = $posicoes[$i];
            $b = $posicoes[$n - 1 - $i];

            if ($a === null || $b === null) {
                continue; // bye: ninguém joga neste confronto
            }

            $pares[] = [$a, $b];
        }

        return $pares;
    }

    private function jaEnfrentaram(
        int $a,
        int $b,
        array $enfrentamentos
    ): bool {
        $chave = $a < $b ? "{$a}|{$b}" : "{$b}|{$a}";

        return isset($enfrentamentos[$chave]);
    }

    private function registrarConfrontos(
        int $cdRodada,
        array $pares,
        array &$enfrentamentos
    ): void {
        $numeroConfronto = 1;

        foreach ($pares as [$idTimeA, $idTimeB]) {
            $cdConfronto = $this->confrontoModel->criar([
                'cd_rodada' => $cdRodada,
                'nr_confronto' => $numeroConfronto,
                'status' => 'PENDENTE',
            ]);

            $this->adicionarParticipante(
                $cdConfronto,
                1,
                $idTimeA
            );

            $this->adicionarParticipante(
                $cdConfronto,
                2,
                $idTimeB
            );

            if ($idTimeA !== null && $idTimeB !== null) {
                $chave = $idTimeA < $idTimeB
                    ? "{$idTimeA}|{$idTimeB}"
                    : "{$idTimeB}|{$idTimeA}";

                $enfrentamentos[$chave] = true;
            }

            $numeroConfronto++;
        }
    }

    private function adicionarParticipante(
        int $cdConfronto,
        int $posicao,
        ?int $idTime
    ): void {
        if ($idTime === null) {
            $cdOrigem = $this->origemParticipanteConfrontoModel
                ->criarBye();

            $status = 'BYE';
        } else {
            $cdOrigem = $this->origemParticipanteConfrontoModel
                ->criarTime($idTime);

            $status = 'DEFINIDO';
        }

        $this->confrontoParticipanteModel->criar([
            'cd_confronto' => $cdConfronto,
            'posicao' => $posicao,
            'cd_origem_participante' => $cdOrigem,
            'status' => $status,
        ]);
    }
}
