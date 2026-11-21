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

    public function recalcularClassificacao(
        PDO $pdo,
        int $cdCompeticao
    ): void {
        $this->pdo = $pdo;

        $etapa = $this->etapaCompeticaoModel
            ->buscarPorCompeticaoETipo($cdCompeticao, 'GRUPOS');

        if ($etapa === null) {
            return;
        }

        $cdEtapaGrupos = (int) $etapa['cd_etapa_competicao'];

        $stmt = $pdo->prepare("
            SELECT g.cd_grupo, g.nm_grupo, cg.cd_time
            FROM grupo_competicao g
            JOIN classificacao_grupo cg ON cg.cd_grupo = g.cd_grupo
            WHERE g.cd_etapa_competicao = :etapa
            ORDER BY g.ordem, cg.posicao
        ");
        $stmt->execute([':etapa' => $cdEtapaGrupos]);

        $grupos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $grupos[(int) $linha['cd_grupo']]['nm_grupo'] =
                (string) $linha['nm_grupo'];
            $grupos[(int) $linha['cd_grupo']]['times'][] =
                (int) $linha['cd_time'];
        }

        $partidas = $pdo->prepare("
            SELECT p.cd_partida
            FROM partida p
            JOIN vinculo_partida_time vpt
              ON vpt.cd_partida = p.cd_partida
            WHERE p.cd_competicao = :competicao
              AND p.cd_confronto IS NOT NULL
              AND p.status = 'FINALIZADA'
            GROUP BY p.cd_partida
            HAVING COUNT(DISTINCT vpt.cd_time) = 2
        ");
        $partidas->execute([':competicao' => $cdCompeticao]);
        $partidasFinalizadas = $partidas->fetchAll(PDO::FETCH_ASSOC);

        $metricas = [
            'JOGOS',
            'VITORIAS',
            'EMPATES',
            'DERROTAS',
            'PONTOS',
            'SALDO',
            'GOLS_PRO',
            'GOLS_CONTRA',
        ];

        foreach ($grupos as &$grupo) {
            $grupo['estatisticas'] = [];

            foreach ($grupo['times'] as $cdTime) {
                $grupo['estatisticas'][$cdTime] = [
                    'JOGOS' => 0,
                    'VITORIAS' => 0,
                    'EMPATES' => 0,
                    'DERROTAS' => 0,
                    'PONTOS' => 0,
                    'SALDO' => 0,
                    'GOLS_PRO' => 0,
                    'GOLS_CONTRA' => 0,
                ];
            }

            $timesDoGrupo = array_fill_keys($grupo['times'], true);

            foreach ($partidasFinalizadas as $partida) {
                $cdPartida = (int) $partida['cd_partida'];

                $timesStmt = $pdo->prepare("
                    SELECT cd_time
                    FROM vinculo_partida_time
                    WHERE cd_partida = :partida
                    ORDER BY cd_time
                ");
                $timesStmt->execute([':partida' => $cdPartida]);
                $times = array_map(
                    'intval',
                    $timesStmt->fetchAll(PDO::FETCH_COLUMN)
                );

                if (count($times) !== 2
                    || !isset($timesDoGrupo[$times[0]])
                    || !isset($timesDoGrupo[$times[1]])) {
                    continue;
                }

                $placarStmt = $pdo->prepare("
                    SELECT r.cd_time, r.pontuacao
                    FROM etapa_partida ep
                    JOIN resultado_etapa_partida r
                      ON r.cd_etapa_partida = ep.cd_etapa_partida
                    WHERE ep.cd_partida = :partida
                      AND ep.nr_etapa = (
                          SELECT MAX(nr_etapa)
                          FROM etapa_partida
                          WHERE cd_partida = :partida_max
                      )
                ");
                $placarStmt->execute([
                    ':partida' => $cdPartida,
                    ':partida_max' => $cdPartida,
                ]);

                $placares = [];
                foreach ($placarStmt->fetchAll(PDO::FETCH_ASSOC) as $placar) {
                    $placares[(int) $placar['cd_time']] =
                        (int) $placar['pontuacao'];
                }

                if (!array_key_exists($times[0], $placares)
                    || !array_key_exists($times[1], $placares)) {
                    continue;
                }

                [$timeA, $timeB] = $times;
                $golsA = $placares[$timeA];
                $golsB = $placares[$timeB];

                $grupo['estatisticas'][$timeA]['JOGOS']++;
                $grupo['estatisticas'][$timeB]['JOGOS']++;
                $grupo['estatisticas'][$timeA]['GOLS_PRO'] += $golsA;
                $grupo['estatisticas'][$timeA]['GOLS_CONTRA'] += $golsB;
                $grupo['estatisticas'][$timeB]['GOLS_PRO'] += $golsB;
                $grupo['estatisticas'][$timeB]['GOLS_CONTRA'] += $golsA;

                if ($golsA > $golsB) {
                    $grupo['estatisticas'][$timeA]['VITORIAS']++;
                    $grupo['estatisticas'][$timeA]['PONTOS'] += 3;
                    $grupo['estatisticas'][$timeB]['DERROTAS']++;
                } elseif ($golsB > $golsA) {
                    $grupo['estatisticas'][$timeB]['VITORIAS']++;
                    $grupo['estatisticas'][$timeB]['PONTOS'] += 3;
                    $grupo['estatisticas'][$timeA]['DERROTAS']++;
                } else {
                    $grupo['estatisticas'][$timeA]['EMPATES']++;
                    $grupo['estatisticas'][$timeB]['EMPATES']++;
                    $grupo['estatisticas'][$timeA]['PONTOS']++;
                    $grupo['estatisticas'][$timeB]['PONTOS']++;
                }
            }

            foreach ($grupo['estatisticas'] as &$estatisticas) {
                $estatisticas['SALDO'] =
                    $estatisticas['GOLS_PRO'] - $estatisticas['GOLS_CONTRA'];
            }
            unset($estatisticas);
        }
        unset($grupo);

        foreach ($grupos as $cdGrupo => $grupo) {
            $this->salvarClassificacaoGrupo(
                $cdGrupo,
                $grupo['estatisticas'],
                $metricas
            );
        }

        if ($this->todosConfrontosGruposFinalizados($cdEtapaGrupos)) {
            $this->gerarEliminatoria($cdCompeticao, $cdEtapaGrupos);
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
            $paresCriados = [];

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

                    $menor = min($a, $b);
                    $maior = max($a, $b);
                    $chavePar = $menor . ':' . $maior;
                    if (isset($paresCriados[$chavePar])) {
                        continue;
                    }
                    $paresCriados[$chavePar] = true;

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

    private function salvarClassificacaoGrupo(
        int $cdGrupo,
        array $estatisticas,
        array $nomesMetricas
    ): void {
        uasort(
            $estatisticas,
            static function (array $a, array $b): int {
                return ($b['PONTOS'] <=> $a['PONTOS'])
                    ?: ($b['VITORIAS'] <=> $a['VITORIAS'])
                    ?: ($b['SALDO'] <=> $a['SALDO'])
                    ?: ($b['GOLS_PRO'] <=> $a['GOLS_PRO']);
            }
        );

        $idsMetricas = [];
        foreach ($nomesMetricas as $nome) {
            $stmt = $this->pdo->prepare("
                INSERT INTO metrica_classificacao (nm_metrica_classificacao)
                VALUES (:nome)
                ON CONFLICT (nm_metrica_classificacao) DO UPDATE
                    SET nm_metrica_classificacao =
                        EXCLUDED.nm_metrica_classificacao
                RETURNING cd_metrica_classificacao
            ");
            $stmt->execute([':nome' => $nome]);
            $idsMetricas[$nome] = (int) $stmt->fetchColumn();
        }

        $posicaoTemp = 1000000;
        $posicaoFinal = 1;
        $classificacaoPorTime = [];

        foreach ($estatisticas as $cdTime => $valores) {
            $stmt = $this->pdo->prepare("
                SELECT cd_classificacao_grupo
                FROM classificacao_grupo
                WHERE cd_grupo = :grupo AND cd_time = :time
            ");
            $stmt->execute([
                ':grupo' => $cdGrupo,
                ':time' => $cdTime,
            ]);
            $cdClassificacao = (int) $stmt->fetchColumn();

            if ($cdClassificacao <= 0) {
                continue;
            }

            $classificacaoPorTime[$cdTime] = $cdClassificacao;

            $temporaria = $this->pdo->prepare("
                UPDATE classificacao_grupo
                SET posicao = :posicao, atualizado_em = CURRENT_TIMESTAMP
                WHERE cd_classificacao_grupo = :classificacao
            ");
            $temporaria->execute([
                ':posicao' => $posicaoTemp++,
                ':classificacao' => $cdClassificacao,
            ]);

            $estatistica = $this->pdo->prepare("
                INSERT INTO estatistica_classificacao (
                    cd_classificacao_grupo,
                    cd_metrica_classificacao,
                    valor
                )
                VALUES (:classificacao, :metrica, :valor)
                ON CONFLICT (
                    cd_classificacao_grupo,
                    cd_metrica_classificacao
                )
                DO UPDATE SET valor = EXCLUDED.valor
            ");

            foreach ($nomesMetricas as $nome) {
                $estatistica->execute([
                    ':classificacao' => $cdClassificacao,
                    ':metrica' => $idsMetricas[$nome],
                    ':valor' => $valores[$nome],
                ]);
            }
        }

        foreach ($estatisticas as $cdTime => $_valores) {
            if (!isset($classificacaoPorTime[$cdTime])) {
                continue;
            }

            $final = $this->pdo->prepare("
                UPDATE classificacao_grupo
                SET posicao = :posicao
                WHERE cd_classificacao_grupo = :classificacao
            ");
            $final->execute([
                ':posicao' => $posicaoFinal++,
                ':classificacao' => $classificacaoPorTime[$cdTime],
            ]);
        }
    }

    private function todosConfrontosGruposFinalizados(
        int $cdEtapaGrupos
    ): bool {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) AS total,
                COUNT(*) FILTER (WHERE c.status = 'FINALIZADO') AS finalizados
            FROM confronto c
            JOIN rodada_competicao r
              ON r.cd_rodada = c.cd_rodada
            WHERE r.cd_etapa_competicao = :etapa
        ");
        $stmt->execute([':etapa' => $cdEtapaGrupos]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int) ($resultado['total'] ?? 0);
        $finalizados = (int) ($resultado['finalizados'] ?? 0);

        return $total > 0 && $total === $finalizados;
    }

    private function nomeRodada(int $numero): string
    {
        return "Rodada {$numero}";
    }

    private function gerarEliminatoria(
        int $cdCompeticao,
        int $cdEtapaGrupos
    ): void {
        $classificados = $this->buscarClassificados($cdEtapaGrupos);

        $participantes = $this->montarParticipantesEliminatoria(
            $classificados
        );

        if (count($participantes) < 2) {
            return;
        }

        $cdEtapaEliminatoria = $this->buscarOuCriarEtapaEliminatoria(
            $cdCompeticao
        );

        $existente = $this->pdo->prepare("
            SELECT EXISTS (
                SELECT 1
                FROM confronto c
                JOIN rodada_competicao r
                  ON r.cd_rodada = c.cd_rodada
                WHERE r.cd_etapa_competicao = :etapa
            )
        ");
        $existente->execute([':etapa' => $cdEtapaEliminatoria]);
        if ((bool) $existente->fetchColumn()) {
            return;
        }

        $tamanhoChave = 1;
        while ($tamanhoChave < count($participantes)) {
            $tamanhoChave *= 2;
        }

        $posicoes = array_values($participantes);
        while (count($posicoes) < $tamanhoChave) {
            $posicoes[] = null;
        }

        $rodadas = $this->criarRodadasEliminatoria(
            $cdEtapaEliminatoria,
            $tamanhoChave
        );

        $confrontos = $this->criarPrimeiraRodadaEliminatoria(
            $rodadas[0],
            $posicoes
        );

        $this->criarRodadasSeguintesEliminatoria(
            $rodadas,
            $confrontos
        );
    }

    private function buscarClassificados(int $cdEtapaGrupos): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                g.cd_grupo,
                g.nm_grupo,
                g.ordem AS ordem_grupo,
                cg.cd_time,
                cg.posicao
            FROM grupo_competicao g
            INNER JOIN classificacao_grupo cg
                ON cg.cd_grupo = g.cd_grupo
            INNER JOIN time t
                ON t.cd_time = cg.cd_time
            WHERE g.cd_etapa_competicao = :etapa
              AND cg.posicao <= 2
            ORDER BY g.ordem, cg.posicao
        ");

        $stmt->execute([
            ':etapa' => $cdEtapaGrupos,
        ]);

        $grupos = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $cdGrupo = (int) $row['cd_grupo'];

            if (!isset($grupos[$cdGrupo])) {
                $grupos[$cdGrupo] = [];
            }

            $grupos[$cdGrupo][] = [
                'cd_time' => (int) $row['cd_time'],
                'posicao' => (int) $row['posicao'],
            ];
        }

        return array_values($grupos);
    }

    private function buscarOuCriarEtapaEliminatoria(
        int $cdCompeticao
    ): int {
        $etapa = $this->etapaCompeticaoModel
            ->buscarEliminatoriaPorCompeticao($cdCompeticao);

        if ($etapa !== null) {
            return (int) $etapa['cd_etapa_competicao'];
        }

        $cdTipoEtapa = $this->etapaCompeticaoModel
            ->buscarTipoPorNome('ELIMINATORIA');

        if ($cdTipoEtapa === null) {
            throw new RuntimeException(
                'Tipo de etapa eliminatória não cadastrado.'
            );
        }

        return $this->etapaCompeticaoModel->criar([
            'cd_competicao' => $cdCompeticao,
            'cd_tipo_etapa' => $cdTipoEtapa,
            'nm_etapa' => 'ELIMINATORIA',
            'ordem' => 2,
            'descricao' => 'Fase eliminatória dos grupos classificados.',
        ]);
    }

    private function montarParticipantesEliminatoria(
        array $grupos
    ): array {
        $primeiros = [];
        $segundos = [];

        foreach ($grupos as $grupo) {
            foreach ($grupo as $time) {
                if ((int) $time['posicao'] === 1) {
                    $primeiros[] = (int) $time['cd_time'];
                } elseif ((int) $time['posicao'] === 2) {
                    $segundos[] = (int) $time['cd_time'];
                }
            }
        }

        $participantes = [];
        $quantidadeGrupos = count($primeiros);

        for ($indice = 0; $indice < $quantidadeGrupos; $indice++) {
            $participantes[] = $primeiros[$indice];

            if (count($segundos) > 0) {
                $indiceSegundo = ($indice + 1) % count($segundos);
                $participantes[] = $segundos[$indiceSegundo];
            }
        }

        return array_values(array_unique(array_filter(
            $participantes,
            static fn ($time): bool => $time !== null
        )));
    }

    private function criarRodadasEliminatoria(
        int $cdEtapaEliminatoria,
        int $tamanhoChave
    ): array {
        $quantidadeRodadas = (int) log($tamanhoChave, 2);
        $rodadas = [];

        for ($numero = 1; $numero <= $quantidadeRodadas; $numero++) {
            $rodadas[] = $this->rodadaCompeticaoModel->criar([
                'cd_etapa_competicao' => $cdEtapaEliminatoria,
                'nr_rodada' => $numero,
                'nm_rodada' => $this->nomeRodadaEliminatoria(
                    $quantidadeRodadas,
                    $numero
                ),
            ]);
        }

        return $rodadas;
    }

    private function nomeRodadaEliminatoria(
        int $totalRodadas,
        int $rodada
    ): string {
        $distancia = $totalRodadas - $rodada;

        return match ($distancia) {
            0 => 'Final',
            1 => 'Semifinal',
            2 => 'Quartas de final',
            3 => 'Oitavas de final',
            4 => '16avos de final',
            5 => '32avos de final',
            default => "Rodada {$rodada}",
        };
    }

    private function criarPrimeiraRodadaEliminatoria(
        int $cdRodada,
        array $posicoes
    ): array {
        $confrontos = [];

        for ($indice = 0; $indice < count($posicoes); $indice += 2) {
            $cdConfronto = $this->confrontoModel->criar([
                'cd_rodada' => $cdRodada,
                'nr_confronto' => (int) (($indice / 2) + 1),
                'status' => 'PENDENTE',
            ]);

            $confrontos[] = $cdConfronto;

            $this->adicionarParticipanteEliminatoria(
                $cdConfronto,
                1,
                $posicoes[$indice]
            );

            $this->adicionarParticipanteEliminatoria(
                $cdConfronto,
                2,
                $posicoes[$indice + 1] ?? null
            );
        }

        return $confrontos;
    }

    private function criarRodadasSeguintesEliminatoria(
        array $rodadas,
        array $confrontosAnteriores
    ): void {
        for ($indiceRodada = 1; $indiceRodada < count($rodadas); $indiceRodada++) {
            $novosConfrontos = [];

            for ($indice = 0; $indice < count($confrontosAnteriores); $indice += 2) {
                $confrontoA = $confrontosAnteriores[$indice];
                $confrontoB = $confrontosAnteriores[$indice + 1] ?? null;

                if ($confrontoB === null) {
                    continue;
                }

                $cdConfronto = $this->confrontoModel->criar([
                    'cd_rodada' => $rodadas[$indiceRodada],
                    'nr_confronto' => (int) (($indice / 2) + 1),
                    'status' => 'AGUARDANDO',
                    'cd_confronto_anterior_a' => $confrontoA,
                    'cd_confronto_anterior_b' => $confrontoB,
                ]);

                $novosConfrontos[] = $cdConfronto;

                $this->adicionarOrigemConfrontoEliminatoria(
                    $cdConfronto,
                    1,
                    $confrontoA
                );

                $this->adicionarOrigemConfrontoEliminatoria(
                    $cdConfronto,
                    2,
                    $confrontoB
                );
            }

            $confrontosAnteriores = $novosConfrontos;
        }
    }

    private function adicionarParticipanteEliminatoria(
        int $cdConfronto,
        int $posicao,
        ?int $idTime
    ): void {
        if ($idTime === null) {
            $cdOrigem = $this->origemParticipanteConfrontoModel->criarBye();
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

    private function adicionarOrigemConfrontoEliminatoria(
        int $cdConfronto,
        int $posicao,
        int $cdConfrontoAnterior
    ): void {
        $cdOrigem = $this->origemParticipanteConfrontoModel
            ->criarVencedorConfronto($cdConfrontoAnterior);

        $this->confrontoParticipanteModel->criar([
            'cd_confronto' => $cdConfronto,
            'posicao' => $posicao,
            'cd_origem_participante' => $cdOrigem,
            'status' => 'PENDENTE',
        ]);
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
