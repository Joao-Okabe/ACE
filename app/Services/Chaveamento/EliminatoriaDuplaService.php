<?php
/*
    Serviço de chaveamento de ELIMINATÓRIA DUPLA (dupla eliminação).
    Baseado no EliminatoriaSimplesService, mas com duas chaves:

    Chave de VENCEDORES (upper bracket):
        - Quem perde não é eliminado, desce para a chave de perdedores.

    Chave de PERDEDORES (repescagem / lower bracket):
        - Quem perde aqui é eliminado de verdade.
        - Estrutura para 8 times (log2(8) = 3 rodadas de vencedores):
            Repescagem 1 → perdedores da rodada 1 pareados entre si (2 confrontos)
            Repescagem 2 → vencedores da repescagem 1 × perdedores da rodada 2
            Repescagem 3 → confronto entre vencedores da repescagem 2
            Repescagem 4 → vencedor da repescagem 3 × perdedor da final

    Gran Final:
        - Vencedor da chave de vencedores × vencedor da chave de perdedores.
        - Com 2 times (k = 1) não há repescagem: o perdedor da final
          de vencedores vai direto para a Gran Final.

    gerar();
        ->  Orquestra tudo dentro de uma transação.

    buscarTimes();
        ->  Busca times inscritos na competição (model).

    validarQuantidadeTimes();
        ->  Confere se existem pelo menos 2 times.

    validarEtapa();
        ->  Valida (model) se a etapa da competição é eliminatória.

    calcularTamanhoChave();
        ->  Igual à eliminatória simples: dobra até virar potência de 2.

    gerarSeeds();
    distribuirTimes();
    criarPrimeiraRodada();
    adicionarParticipante();
        ->  Iguais à eliminatória simples.

    criarRodadasVencedores();
        ->  Cria as rodadas da chave de vencedores (log2(tamanho) rodadas)
            com os mesmos nomes da eliminatória simples.

    criarRodadasPerdedores();
        ->  Cria as rodadas da repescagem (2*log2(tamanho) - 2 rodadas),
            numeradas após as rodadas de vencedores.

    criarRodadaGranFinal();
        ->  Cria a rodada da Gran Final.

    criarProximasRodadasVencedores();
        ->  Igual à criarProximasRodadas() da eliminatória simples, mas
            devolve os confrontos de cada rodada de vencedores, pois os
            PERDEDores desses confrontos alimentam a repescagem.

    criarRodadasRepescagem();
        ->  Monta a chave de perdedores:
            - Rodada 1: pareia os perdedores da rodada 1 de vencedores.
            - Rodadas pares: vencedor da repescagem anterior
              × perdedor que desce da chave de vencedores.
            - Rodadas ímpares: pareiam vencedores da repescagem anterior.

    criarGranFinal();
        ->  Vencedor da final de vencedores × vencedor da repescagem.

    adicionarOrigemConfronto();
        ->  Participante vem do VENCEDOR de um confronto anterior.

    adicionarOrigemPerdedor();
        ->  Participante vem do PERDEDOR de um confronto anterior
            (só existe na eliminatória dupla).
*/


class EliminatoriaDuplaService
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
                $seeds
            );

            $rodadasVencedores = $this->criarRodadasVencedores(
                $cdEtapaCompeticao,
                $tamanhoChave
            );

            $rodadasPerdedores = $this->criarRodadasPerdedores(
                $cdEtapaCompeticao,
                $tamanhoChave
            );

            $rodadaGranFinal = $this->criarRodadaGranFinal(
                $cdEtapaCompeticao,
                count($rodadasVencedores) +
                    count($rodadasPerdedores)
            );

            $confrontos = $this->criarPrimeiraRodada(
                $rodadasVencedores[0],
                $posicoes
            );

            $perdedoresPorRodada =
                $this->criarProximasRodadasVencedores(
                    $rodadasVencedores,
                    $confrontos
                );

            $cdVencedorRepescagem =
                $this->criarRodadasRepescagem(
                    $rodadasPerdedores,
                    $perdedoresPorRodada
                );

            $this->criarGranFinal(
                $rodadaGranFinal,
                end($confrontos),
                $cdVencedorRepescagem
            );

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
        return $this->competicaoModel->listarTimesInscritos($cdCompeticao);
    }

    private function validarQuantidadeTimes(array $times): void
    {
        if (count($times) < 2) {
            throw new RuntimeException(
                'A eliminatória dupla precisa de pelo menos 2 times.'
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

        if ($etapa['nm_tipo_etapa'] !== 'ELIMINATORIA') {
            throw new RuntimeException(
                'A etapa informada não é uma etapa eliminatória.'
            );
        }
    }

    private function calcularTamanhoChave(
        int $quantidadeTimes
    ): int {
        $tamanho = 1;

        while ($tamanho < $quantidadeTimes) {
            $tamanho *= 2;
        }

        return $tamanho;
    }

    private function gerarSeeds(
        int $tamanho
    ): array {
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
        array $seeds
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

    private function criarRodadasVencedores(
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
            $rodadas[] = $this->rodadaCompeticaoModel->criar(
                [
                    'cd_etapa_competicao' => $cdEtapaCompeticao,
                    'nr_rodada' => $numero,
                    'nm_rodada' => $this->nomeRodadaVencedores(
                        $quantidadeRodadas,
                        $numero
                    )
                ]
            );
        }

        return $rodadas;
    }

    private function nomeRodadaVencedores(
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

    /*
        Com 8 times (3 rodadas de vencedores) a repescagem tem
        2*3 - 2 = 4 rodadas.

        Com 2 times (1 rodada de vencedores) não existem rodadas
        de repescagem: o perdedor da final vai direto para a
        Gran Final.
    */
    private function criarRodadasPerdedores(
        int $cdEtapaCompeticao,
        int $tamanhoChave
    ): array {
        $quantidadeVencedores = (int) log(
            $tamanhoChave,
            2
        );

        $quantidadeRodadas = 2 * $quantidadeVencedores - 2;

        $rodadas = [];

        for (
            $numero = 1;
            $numero <= $quantidadeRodadas;
            $numero++
        ) {
            $rodadas[] = $this->rodadaCompeticaoModel->criar(
                [
                    'cd_etapa_competicao' => $cdEtapaCompeticao,
                    'nr_rodada' => $quantidadeVencedores + $numero,
                    'nm_rodada' => "Repescagem {$numero}"
                ]
            );
        }

        return $rodadas;
    }

    private function criarRodadaGranFinal(
        int $cdEtapaCompeticao,
        int $numeroRodada
    ): int {
        return $this->rodadaCompeticaoModel->criar(
            [
                'cd_etapa_competicao' => $cdEtapaCompeticao,
                'nr_rodada' => $numeroRodada + 1,
                'nm_rodada' => 'Gran Final'
            ]
        );
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

            $cdConfronto = $this->confrontoModel->criar(
                [
                    'cd_rodada' => $cdRodada,
                    'nr_confronto' => $numeroConfronto,
                    'status' => 'PENDENTE'
                ]
            );

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
            $cdOrigem = $this->origemParticipanteConfrontoModel->criarBye();

            $status = 'BYE';
        } else {
            $cdOrigem = $this->origemParticipanteConfrontoModel->criarTime(
                    $time['cd_time']
            );
            $status = 'DEFINIDO';
        }

        $this->confrontoParticipanteModel->criar(
            [
                'cd_confronto' => $cdConfronto,
                'posicao' => $posicao,
                'cd_origem_participante' => $cdOrigem,
                'status' => $status
            ]
        );
    }

    /*
        Cria as rodadas seguintes da chave de vencedores e devolve
        um array indexado por rodada (0 = primeira rodada) com os
        cd_confronto de cada uma.

        Exemplo com 8 times:
            $perdedoresPorRodada = [
                0 => [1, 2, 3, 4],   // perdedores descem p/ Repescagem 1
                1 => [5, 6],         // perdedores descem p/ Repescagem 2
                2 => [7]             // perdedor desce p/ Repescagem 4
            ];
    */
    private function criarProximasRodadasVencedores(
        array $rodadas,
        array $confrontosAnteriores
    ): array {
        $perdedoresPorRodada = [$confrontosAnteriores];

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
                $confrontoA = $confrontosAnteriores[$indice];

                $confrontoB = $confrontosAnteriores[$indice + 1];

                $numeroConfronto = ($indice / 2) + 1;

                $cdConfronto = $this->confrontoModel->criar(
                    [
                        'cd_rodada' => $rodadas[$indiceRodada],
                        'nr_confronto' => $numeroConfronto,
                        'status' => 'AGUARDANDO',
                        'cd_confronto_anterior_a' => $confrontoA,
                        'cd_confronto_anterior_b' => $confrontoB
                    ]
                );

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

            $perdedoresPorRodada[$indiceRodada] = $novosConfrontos;

            $confrontosAnteriores = $novosConfrontos;
        }

        return $perdedoresPorRodada;
    }

    /*
        Monta a chave de perdedores (repescagem) e devolve o cd_confronto
        do confronto final da repescagem, que vai para a Gran Final.

        Exemplo com 8 times (perdedoresPorRodada = [4, 2, 1] confrontos):

            Repescagem 1 → perdedores da rodada 1 pareados (2 confrontos)
            Repescagem 2 → vencedor Rep.1 × perdedor da rodada 2 (2 confrontos)
            Repescagem 3 → vencedores da Rep.2 pareados (1 confronto)
            Repescagem 4 → vencedor Rep.3 × perdedor da final (1 confronto)

        Regras:
            - Rodada 1: pareia os perdedores da rodada 1 de vencedores.
            - Rodadas pares (2, 4, ...): posição 1 = vencedor da repescagem
              anterior, posição 2 = perdedor que desce da chave de vencedores
              (rodada indiceRodada/2 + 1).
            - Rodadas ímpares: pareiam os vencedores da rodada anterior.
    */
    private function criarRodadasRepescagem(
        array $rodadas,
        array $perdedoresPorRodada
    ): ?int {
        if ($rodadas === []) {
            // 2 times: sem repescagem, o perdedor da final
            // de vencedores vai direto para a Gran Final.
            return null;
        }

        $confrontosAnteriores = [];

        foreach ($rodadas as $indiceRodada => $cdRodada) {
            $novosConfrontos = [];

            if ($indiceRodada === 0) {
                // Pareia os perdedores da primeira rodada de vencedores
                $perdedores = $perdedoresPorRodada[0];

                for (
                    $indice = 0;
                    $indice < count($perdedores);
                    $indice += 2
                ) {
                    $numeroConfronto = ($indice / 2) + 1;

                    $cdConfronto = $this->confrontoModel->criar(
                        [
                            'cd_rodada' => $cdRodada,
                            'nr_confronto' => $numeroConfronto,
                            'status' => 'AGUARDANDO',
                            'cd_confronto_anterior_a' => $perdedores[$indice],
                            'cd_confronto_anterior_b' => $perdedores[$indice + 1]
                        ]
                    );

                    $novosConfrontos[] = $cdConfronto;

                    $this->adicionarOrigemPerdedor(
                        $cdConfronto,
                        1,
                        $perdedores[$indice]
                    );

                    $this->adicionarOrigemPerdedor(
                        $cdConfronto,
                        2,
                        $perdedores[$indice + 1]
                    );
                }
            } elseif ($indiceRodada % 2 === 0) {
                /*
                    Rodada par: vencedor da repescagem anterior
                    × perdedor que desce da chave de vencedores.
                */
                $perdedores = $perdedoresPorRodada[
                    ($indiceRodada / 2) + 1
                ];

                for (
                    $indice = 0;
                    $indice < count($perdedores);
                    $indice++
                ) {
                    $numeroConfronto = $indice + 1;

                    $cdConfronto = $this->confrontoModel->criar(
                        [
                            'cd_rodada' => $cdRodada,
                            'nr_confronto' => $numeroConfronto,
                            'status' => 'AGUARDANDO',
                            'cd_confronto_anterior_a' =>
                                $confrontosAnteriores[$indice],
                            'cd_confronto_anterior_b' => $perdedores[$indice]
                        ]
                    );

                    $novosConfrontos[] = $cdConfronto;

                    $this->adicionarOrigemConfronto(
                        $cdConfronto,
                        1,
                        $confrontosAnteriores[$indice]
                    );

                    $this->adicionarOrigemPerdedor(
                        $cdConfronto,
                        2,
                        $perdedores[$indice]
                    );
                }
            } else {
                /*
                    Rodada ímpar: apenas pareia os vencedores
                    da rodada anterior da repescagem.
                */
                for (
                    $indice = 0;
                    $indice < count($confrontosAnteriores);
                    $indice += 2
                ) {
                    $confrontoA = $confrontosAnteriores[$indice];

                    $confrontoB = $confrontosAnteriores[$indice + 1];

                    $numeroConfronto = ($indice / 2) + 1;

                    $cdConfronto = $this->confrontoModel->criar(
                        [
                            'cd_rodada' => $cdRodada,
                            'nr_confronto' => $numeroConfronto,
                            'status' => 'AGUARDANDO',
                            'cd_confronto_anterior_a' => $confrontoA,
                            'cd_confronto_anterior_b' => $confrontoB
                        ]
                    );

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
            }

            $confrontosAnteriores = $novosConfrontos;
        }

        return end($confrontosAnteriores);
    }

    /*
        Gran Final: vencedor da chave de vencedores × vencedor da
        repescagem. Com 2 times, o "vencedor da repescagem" não existe,
        então a posição 2 recebe o PERDEDOR da final de vencedores.
    */
    private function criarGranFinal(
        int $cdRodada,
        int $cdConfrontoVencedores,
        ?int $cdConfrontoRepescagem
    ): void {
        $cdConfronto = $this->confrontoModel->criar(
            [
                'cd_rodada' => $cdRodada,
                'nr_confronto' => 1,
                'status' => 'AGUARDANDO',
                'cd_confronto_anterior_a' => $cdConfrontoVencedores,
                'cd_confronto_anterior_b' => $cdConfrontoRepescagem
            ]
        );

        $this->adicionarOrigemConfronto(
            $cdConfronto,
            1,
            $cdConfrontoVencedores
        );

        if ($cdConfrontoRepescagem !== null) {
            $this->adicionarOrigemConfronto(
                $cdConfronto,
                2,
                $cdConfrontoRepescagem
            );
        } else {
            $this->adicionarOrigemPerdedor(
                $cdConfronto,
                2,
                $cdConfrontoVencedores
            );
        }
    }

    private function adicionarOrigemConfronto(
        int $cdConfronto,
        int $posicao,
        int $cdConfrontoAnterior
    ): void {
        $cdOrigem = $this->origemParticipanteConfrontoModel->criarVencedorConfronto(
                $cdConfrontoAnterior
        );

        $this->confrontoParticipanteModel->criar(
            [
                'cd_confronto' => $cdConfronto,
                'posicao' => $posicao,
                'cd_origem_participante' => $cdOrigem,
                'status' => 'PENDENTE'
            ]
        );
    }

    private function adicionarOrigemPerdedor(
        int $cdConfronto,
        int $posicao,
        int $cdConfrontoAnterior
    ): void {
        $cdOrigem = $this->origemParticipanteConfrontoModel->criarPerdedorConfronto(
                $cdConfrontoAnterior
        );

        $this->confrontoParticipanteModel->criar(
            [
                'cd_confronto' => $cdConfronto,
                'posicao' => $posicao,
                'cd_origem_participante' => $cdOrigem,
                'status' => 'PENDENTE'
            ]
        );
    }
}