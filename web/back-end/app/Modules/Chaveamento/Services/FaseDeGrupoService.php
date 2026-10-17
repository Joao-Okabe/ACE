<?php
/*
    Serviço de chaveamento de FASE DE GRUPOS (GRUPOS_ELIMINATORIA).

    Divide os times inscritos em grupos, cria a classificação
    inicial de cada time dentro do grupo e gera os confrontos
    de turno único (todos contra todos) dentro de cada grupo.

    gerar();
        ->  gera grupos + classificação + confrontos (models)

    buscarTimes();
        ->  busca times inscritos na competição (model)

    validarQuantidadeTimes();
        ->  confere se existem pelo menos 2 times

    validarEtapa();
        ->  valida (model) se a etapa da competição é do tipo GRUPOS

    calcularQuantidadeGrupos();
        ->  ceil(n / tamanhoMaximoGrupo); tamanho padrão 4

    distribuirEmGrupos();
        ->  distribui os times em grupos (serpentina para equilibrar)

    criarGrupos();
        ->  cria os grupos e vincula times + classificação inicial (model)

    gerarConfrontosGrupos();
        ->  para cada grupo, cria rodadas e confrontos turno único

    nomeRodada();
        ->  rótulo da rodada ("Rodada 1", ...)
*/

class FaseDeGrupoService
{
    private const TAMANHO_MAXIMO_GRUPO = 4;

    private PDO $pdo;

    private Competicao $competicaoModel;

    private EtapaCompeticao $etapaCompeticaoModel;

    private RodadaCompeticao $rodadaCompeticaoModel;

    private Confronto $confrontoModel;

    private ConfrontoParticipante $confrontoParticipanteModel;

    private OrigemParticipanteConfronto $origemParticipanteConfrontoModel;

    private Grupo $grupoModel;

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

        $this->grupoModel = new Grupo();
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

            $grupos = $this->distribuirEmGrupos(
                $times,
                $this->calcularQuantidadeGrupos(count($times))
            );

            $this->criarGrupos(
                $cdEtapaCompeticao,
                $grupos
            );

            $this->gerarConfrontosGrupos(
                $cdEtapaCompeticao,
                $grupos
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
        return $this->competicaoModel
            ->listarTimesInscritos($cdCompeticao);
    }

    private function validarQuantidadeTimes(array $times): void
    {
        if (count($times) < 2) {
            throw new RuntimeException(
                'A fase de grupos precisa de pelo menos 2 times.'
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
                'A etapa informada não é uma etapa de grupos.'
            );
        }
    }

    private function calcularQuantidadeGrupos(
        int $quantidadeTimes
    ): int {
        return max(
            1,
            (int) ceil($quantidadeTimes / self::TAMANHO_MAXIMO_GRUPO)
        );
    }

    /*
        Distribui os times em grupos por serpentina:
        com 8 times e 2 grupos:
            Grupo A: 1º, 4º, 5º, 8º
            Grupo B: 2º, 3º, 6º, 7º
        Assim grupos ficam equilibrados por força de seed.
    */
    private function distribuirEmGrupos(
        array $times,
        int $quantidadeGrupos
    ): array {
        $grupos = array_fill(0, $quantidadeGrupos, []);

        /*
            Serpentina real: ciclo de comprimento 2*q.
            Com q = 2 e times 1..8:
                ciclo: 0,1,1,0, 0,1,1,0 ...
            Resultado:
                Grupo A: 1º, 4º, 5º, 8º
                Grupo B: 2º, 3º, 6º, 7º
        */
        foreach ($times as $indice => $time) {
            $posicaoCiclo = $indice % (2 * $quantidadeGrupos);

            if ($posicaoCiclo < $quantidadeGrupos) {
                $indiceGrupo = $posicaoCiclo;
            } else {
                $indiceGrupo = 2 * $quantidadeGrupos - 1 - $posicaoCiclo;
            }

            $grupos[$indiceGrupo][] = $time;
        }

        return $grupos;
    }

    private function criarGrupos(
        int $cdEtapaCompeticao,
        array $grupos
    ): void {
        $ordem = 1;

        foreach ($grupos as $indice => $timesDoGrupo) {
            $cdGrupo = $this->grupoModel->criar([
                'cd_etapa_competicao' => $cdEtapaCompeticao,
                'nm_grupo' => 'Grupo ' . chr(65 + $indice),
                'ordem' => $ordem++,
            ]);

            $posicao = 1;

            foreach ($timesDoGrupo as $time) {
                $this->grupoModel->vincularTime(
                    $cdGrupo,
                    (int) $time['cd_time']
                );

                $this->grupoModel->criarClassificacaoInicial(
                    $cdGrupo,
                    (int) $time['cd_time'],
                    $posicao++
                );
            }

            $grupos[$indice]['cd_grupo'] = $cdGrupo;
        }
    }

    /*
        Gera os confrontos de turno único (todos contra todos)
        dentro de cada grupo, usando o método do círculo
        (round-robin / tabela de Berger).
    */
    private function gerarConfrontosGrupos(
        int $cdEtapaCompeticao,
        array $grupos
    ): void {
        foreach ($grupos as $indiceGrupo => $dadosGrupo) {
            $idsTimes = array_map(
                static fn (array $time): int => (int) $time['cd_time'],
                $dadosGrupo
            );

            $quantidadeTimes = count($idsTimes);

            if ($quantidadeTimes < 2) {
                continue;
            }

            $quantidadeRodadas = $quantidadeTimes % 2 === 0
                ? $quantidadeTimes - 1
                : $quantidadeTimes;

            if ($quantidadeTimes % 2 === 1) {
                $idsTimes[] = null; // bye fictício
            }

            $rodadasDoGrupo = [];

            for ($numero = 1; $numero <= $quantidadeRodadas; $numero++) {
                $rodadasDoGrupo[] = $this->rodadaCompeticaoModel->criar([
                    'cd_etapa_competicao' => $cdEtapaCompeticao,
                    'nr_rodada' => $numero,
                    'nm_rodada' => $this->nomeRodada($numero),
                ]);
            }

            $n = count($idsTimes);

            for ($rodada = 0; $rodada < $quantidadeRodadas; $rodada++) {
                $posicoes = [];

                $posicoes[0] = $idsTimes[0];

                for ($i = 1; $i < $n; $i++) {
                    $posicoes[$i] =
                        $idsTimes[1 + (($i - 1 + $rodada) % ($n - 1))];
                }

                for ($i = 0; $i < $n / 2; $i++) {
                    $a = $posicoes[$i];

                    $b = $posicoes[$n - 1 - $i];

                    if ($a === null || $b === null) {
                        continue;
                    }

                    $cdConfronto = $this->confrontoModel->criar([
                        'cd_rodada' => $rodadasDoGrupo[$rodada],
                        'nr_confronto' => $i + 1,
                        'status' => 'PENDENTE',
                    ]);

                    $this->adicionarParticipante(
                        $cdConfronto,
                        1,
                        $a
                    );

                    $this->adicionarParticipante(
                        $cdConfronto,
                        2,
                        $b
                    );
                }
            }
        }
    }

    private function nomeRodada(int $numero): string
    {
        return "Rodada {$numero}";
    }

    private function adicionarParticipante(
        int $cdConfronto,
        int $posicao,
        int $idTime
    ): void {
        $cdOrigem = $this->origemParticipanteConfrontoModel
            ->criarTime($idTime);

        $this->confrontoParticipanteModel->criar([
            'cd_confronto' => $cdConfronto,
            'posicao' => $posicao,
            'cd_origem_participante' => $cdOrigem,
            'status' => 'DEFINIDO',
        ]);
    }
}
