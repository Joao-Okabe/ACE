<?php

class EliminatoriaSimplesService
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

        $this->confrontoParticipanteModel =
            new ConfrontoParticipante();

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

            $tamanhoChave = $this->calcularTamanhoChave(
                count($times)
            );

            $seeds = $this->gerarSeeds($tamanhoChave);

            $posicoes = $this->distribuirTimes(
                $times,
                $seeds,
                $tamanhoChave
            );

            $rodadas = $this->criarRodadas(
                $cdEtapaCompeticao,
                $tamanhoChave
            );

            $confrontos = $this->criarPrimeiraRodada(
                $rodadas[0],
                $posicoes
            );

            $this->criarProximasRodadas(
                $rodadas,
                $confrontos
            );

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    private function buscarTimes(
        int $cdCompeticao
    ): array {
        return $this->competicaoModel
            ->listarTimesInscritos($cdCompeticao);
    }

    private function validarQuantidadeTimes(
        array $times
    ): void {
        if (count($times) < 2) {
            throw new RuntimeException(
                'A eliminatória simples precisa de pelo menos 2 times.'
            );
        }
    }

    private function validarEtapa(
        int $cdCompeticao,
        int $cdEtapaCompeticao
    ): void {
        $etapa = $this->etapaCompeticaoModel
            ->buscarPorCompeticao(
                $cdEtapaCompeticao,
                $cdCompeticao
            );

        if (!$etapa) {
            throw new RuntimeException(
                'A etapa não pertence à competição.'
            );
        }

        if ($etapa['nm_tipo_etapa'] !== 'ELIMINATORIA') {
            throw new RuntimeException(
                'A etapa informada não é uma etapa eliminatória.'
            );
        }
    }

    private function calcularTamanhoChave(
        int $quantidadeTimes
    ): int 
    {
        $tamanho = 1;

        while ($tamanho < $quantidadeTimes) {
            $tamanho *= 2;
        }

        return $tamanho;
    }

    private function gerarSeeds(
        int $tamanho
    ): array 
    {
        $seeds = [1, 2];

        while (count($seeds) < $tamanho) {
            $limite = count($seeds) * 2 + 1;

            $novosSeeds = [];

            foreach ($seeds as $seed) {
                $novosSeeds[] = $seed;
                $novosSeeds[] = $limite - $seed;
            }

            $seeds = $novosSeeds;
        }

        return $seeds;
    }

    private function distribuirTimes(
        array $times,
        array $seeds,
        int $tamanhoChave
    ): array {
        $timesPorSeed = [];

        foreach ($times as $indice => $time) {
            $seed = $indice + 1;

            $timesPorSeed[$seed] = [
                'seed' => $seed,
                'cd_time' => (int) $time['cd_time'],
                'nm_time' => $time['nm_time']
            ];
        }

        $posicoes = [];

        foreach ($seeds as $seed) {
            $posicoes[] = $timesPorSeed[$seed] ?? null;
        }

        return $posicoes;
    }

    private function criarRodadas(
        int $cdEtapaCompeticao,
        int $tamanhoChave
    ): array {
        $quantidadeRodadas = (int) log(
            $tamanhoChave,
            2
        );

        $rodadas = [];

        for (
            $numero = 1;
            $numero <= $quantidadeRodadas;
            $numero++
        ) {
            $rodadas[] = $this->rodadaCompeticaoModel
                ->criar([
                    'cd_etapa_competicao' =>
                        $cdEtapaCompeticao,
                    'nr_rodada' => $numero,
                    'nm_rodada' => $this->nomeRodada(
                        $quantidadeRodadas,
                        $numero
                    )
                ]);
        }

        return $rodadas;
    }

    private function nomeRodada(
        int $totalRodadas,
        int $rodada
    ): string {
        $distancia = $totalRodadas - $rodada;

        return match ($distancia) {
            0 => 'Final',
            1 => 'Semifinal',
            2 => 'Quartas de final',
            3 => 'Oitavas de final',
            default => "Rodada {$rodada}"
        };
    }

    private function criarPrimeiraRodada(
        int $cdRodada,
        array $posicoes
    ): array {
        $confrontos = [];

        for (
            $indice = 0;
            $indice < count($posicoes);
            $indice += 2
        ) {
            $numeroConfronto = ($indice / 2) + 1;

            $cdConfronto = $this->confrontoModel
                ->criar([
                    'cd_rodada' => $cdRodada,
                    'nr_confronto' => $numeroConfronto,
                    'status' => 'PENDENTE'
                ]);

            $confrontos[] = $cdConfronto;

            $this->adicionarParticipante(
                $cdConfronto,
                1,
                $posicoes[$indice]
            );

            $this->adicionarParticipante(
                $cdConfronto,
                2,
                $posicoes[$indice + 1]
            );
        }

        return $confrontos;
    }

    private function adicionarParticipante(
        int $cdConfronto,
        int $posicao,
        ?array $time
    ): void {
        if ($time === null) {
            $cdOrigem = $this
                ->origemParticipanteConfrontoModel
                ->criarBye();

            $status = 'BYE';
        } else {
            $cdOrigem = $this
                ->origemParticipanteConfrontoModel
                ->criarTime(
                    $time['cd_time']
                );

            $status = 'DEFINIDO';
        }

        $this->confrontoParticipanteModel
            ->criar([
                'cd_confronto' => $cdConfronto,
                'posicao' => $posicao,
                'cd_origem_participante' => $cdOrigem,
                'status' => $status
            ]);
    }

    private function criarProximasRodadas(
        array $rodadas,
        array $confrontosAnteriores
    ): void {
        for (
            $indiceRodada = 1;
            $indiceRodada < count($rodadas);
            $indiceRodada++
        ) {
            $novosConfrontos = [];

            for (
                $indice = 0;
                $indice < count($confrontosAnteriores);
                $indice += 2
            ) {
                $confrontoA =
                    $confrontosAnteriores[$indice];

                $confrontoB =
                    $confrontosAnteriores[$indice + 1];

                $numeroConfronto =
                    ($indice / 2) + 1;

                $cdConfronto =
                    $this->confrontoModel->criar([
                        'cd_rodada' =>
                            $rodadas[$indiceRodada],
                        'nr_confronto' =>
                            $numeroConfronto,
                        'status' => 'AGUARDANDO',
                        'cd_confronto_anterior_a' =>
                            $confrontoA,
                        'cd_confronto_anterior_b' =>
                            $confrontoB
                    ]);

                $novosConfrontos[] = $cdConfronto;

                $this->adicionarOrigemConfronto(
                    $cdConfronto,
                    1,
                    $confrontoA
                );

                $this->adicionarOrigemConfronto(
                    $cdConfronto,
                    2,
                    $confrontoB
                );
            }

            $confrontosAnteriores = $novosConfrontos;
        }
    }

    private function adicionarOrigemConfronto(
        int $cdConfronto,
        int $posicao,
        int $cdConfrontoAnterior
    ): void {
        $cdOrigem = $this
            ->origemParticipanteConfrontoModel
            ->criarVencedorConfronto(
                $cdConfrontoAnterior
            );

        $this->confrontoParticipanteModel
            ->criar([
                'cd_confronto' => $cdConfronto,
                'posicao' => $posicao,
                'cd_origem_participante' => $cdOrigem,
                'status' => 'PENDENTE'
            ]);
    }
}