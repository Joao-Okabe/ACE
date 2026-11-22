<?php
/*
    Serviço de chaveamento do SISTEMA SUÍÇO.

    gerar();
        ->  cria o grupo de classificação e a primeira rodada

    processarResultado();
        ->  recalcula a classificação e cria a próxima rodada
            somente quando todos os confrontos da rodada atual terminarem

    buscarTimes();
        ->  busca times inscritos na competição (model)

    validarQuantidadeTimes();
        ->  confere se existem pelo menos 2 times

    validarEtapa();
        ->  valida (model) se a etapa da competição é do tipo GRUPOS

    calcularQuantidadeRodadas();
        ->  ceil(log2(n)) rodadas, mínimo 1

    sortear();
        ->  embaralha os times para a primeira rodada

    emparelhar();
        ->  pareia equipes por pontuação e evita revanche

    registrarConfrontos();
        ->  insere os confrontos da rodada, incluindo BYE
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

            $idsTimes = array_map(
                static fn (array $time): int => (int) $time['cd_time'],
                $times
            );
            sort($idsTimes);

            if ($this->etapaJaPossuiRodadas($cdEtapaCompeticao)) {
                throw new RuntimeException(
                    'Esta etapa já possui rodadas do sistema suíço.'
                );
            }

            $cdGrupo = $this->criarGrupoSuico($cdEtapaCompeticao, $idsTimes);
            $this->sortear($idsTimes);

            $pares = $this->emparelharPrimeiraRodada($idsTimes);
            $cdRodada = $this->criarRodada($cdEtapaCompeticao, 1);
            $this->registrarConfrontos($cdRodada, $pares);
            $this->confrontoModel->processarByesEtapa($cdEtapaCompeticao);
            $this->salvarClassificacao(
                $cdGrupo,
                $this->calcularClassificacao(
                    $cdEtapaCompeticao,
                    $idsTimes
                )
            );

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function processarResultado(
        PDO $pdo,
        int $cdCompeticao,
        int $cdEtapaCompeticao
    ): void
    {
        if (!$pdo->inTransaction()) {
            throw new LogicException(
                'O processamento do suíço deve ocorrer dentro da transação do resultado.'
            );
        }

        $this->pdo = $pdo;
        $etapa = $this->etapaCompeticaoModel->buscarPorCompeticao(
            $cdEtapaCompeticao,
            $cdCompeticao
        );
        if ($etapa === null || $etapa['nm_tipo_etapa'] !== 'GRUPOS') {
            return;
        }

        $etapaSuica = $this->etapaCompeticaoModel
            ->buscarPorCompeticaoETipo($cdCompeticao, 'GRUPOS');
        if (
            $etapaSuica === null
            || (int) $etapaSuica['cd_etapa_competicao'] !== $cdEtapaCompeticao
        ) {
            return;
        }

        $cdEtapa = $cdEtapaCompeticao;
        $bloquearEtapa = $pdo->prepare("
            SELECT cd_etapa_competicao
            FROM etapa_competicao
            WHERE cd_etapa_competicao = :etapa
            FOR UPDATE
        ");
        $bloquearEtapa->execute([':etapa' => $cdEtapa]);

        $cdGrupo = $this->buscarGrupoSuico($cdEtapa);
        $idsTimes = $this->buscarIdsTimesGrupo($cdGrupo);
        $classificacao = $this->calcularClassificacao($cdEtapa, $idsTimes);
        $this->salvarClassificacao($cdGrupo, $classificacao);

        $rodadaAtual = $this->buscarRodadaAtual($cdEtapa);
        if ($rodadaAtual === null) {
            return;
        }

        $totalRodadas = $this->calcularQuantidadeRodadas(count($idsTimes));
        if (
            $rodadaAtual['finalizados'] !== $rodadaAtual['total']
            || $rodadaAtual['numero'] >= $totalRodadas
        ) {
            return;
        }

        $proximaRodada = $rodadaAtual['numero'] + 1;
        if ($this->rodadaExiste($cdEtapa, $proximaRodada)) {
            return;
        }

        $pares = $this->emparelharPorClassificacao($classificacao);
        $cdRodada = $this->criarRodada($cdEtapa, $proximaRodada);
        $this->registrarConfrontos($cdRodada, $pares);
        $this->confrontoModel->processarByesEtapa($cdEtapa);
        $this->salvarClassificacao(
            $cdGrupo,
            $this->calcularClassificacao($cdEtapa, $idsTimes)
        );
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

    private function sortear(array &$times): void
    {
        shuffle($times);
    }

    private function etapaJaPossuiRodadas(int $cdEtapaCompeticao): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT EXISTS (
                SELECT 1
                FROM rodada_competicao
                WHERE cd_etapa_competicao = :etapa
            )
        ");
        $stmt->execute([':etapa' => $cdEtapaCompeticao]);

        return (bool) $stmt->fetchColumn();
    }

    private function criarGrupoSuico(
        int $cdEtapaCompeticao,
        array $idsTimes
    ): int {
        $stmt = $this->pdo->prepare("
            SELECT cd_grupo, nm_grupo
            FROM grupo_competicao
            WHERE cd_etapa_competicao = :etapa
            ORDER BY ordem, cd_grupo
        ");
        $stmt->execute([':etapa' => $cdEtapaCompeticao]);
        $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grupoSuico = null;
        foreach ($grupos as $grupo) {
            if ($grupo['nm_grupo'] === 'Sistema Suíço') {
                $grupoSuico = (int) $grupo['cd_grupo'];
                break;
            }
        }

        if ($grupoSuico === null && $grupos !== []) {
            throw new RuntimeException(
                'A etapa suíça já contém grupos de outro formato.'
            );
        }

        $grupoSuico ??= $this->grupoModel->criar([
            'cd_etapa_competicao' => $cdEtapaCompeticao,
            'nm_grupo' => 'Sistema Suíço',
            'ordem' => 1,
        ]);

        foreach ($idsTimes as $indice => $idTime) {
            $this->grupoModel->vincularTime($grupoSuico, $idTime);
            $this->grupoModel->criarClassificacaoInicial(
                $grupoSuico,
                $idTime,
                $indice + 1
            );
        }

        return $grupoSuico;
    }

    private function buscarGrupoSuico(int $cdEtapaCompeticao): int
    {
        $stmt = $this->pdo->prepare("
            SELECT cd_grupo
            FROM grupo_competicao
            WHERE cd_etapa_competicao = :etapa
              AND nm_grupo = 'Sistema Suíço'
        ");
        $stmt->execute([':etapa' => $cdEtapaCompeticao]);
        $id = $stmt->fetchColumn();

        if ($id === false) {
            throw new RuntimeException(
                'O grupo de classificação do sistema suíço não foi encontrado.'
            );
        }

        return (int) $id;
    }

    private function buscarIdsTimesGrupo(int $cdGrupo): array
    {
        $stmt = $this->pdo->prepare("
            SELECT cd_time
            FROM classificacao_grupo
            WHERE cd_grupo = :grupo
            ORDER BY cd_time
        ");
        $stmt->execute([':grupo' => $cdGrupo]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function criarRodada(
        int $cdEtapaCompeticao,
        int $numero
    ): int {
        return $this->rodadaCompeticaoModel->criar([
            'cd_etapa_competicao' => $cdEtapaCompeticao,
            'nr_rodada' => $numero,
            'nm_rodada' => "Rodada {$numero}",
        ]);
    }

    private function buscarRodadaAtual(int $cdEtapaCompeticao): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                r.nr_rodada AS numero,
                COUNT(c.cd_confronto) AS total,
                COUNT(*) FILTER (
                    WHERE c.status = 'FINALIZADO'
                ) AS finalizados
            FROM rodada_competicao r
            LEFT JOIN confronto c ON c.cd_rodada = r.cd_rodada
            WHERE r.cd_etapa_competicao = :etapa
            GROUP BY r.cd_rodada, r.nr_rodada
            HAVING COUNT(c.cd_confronto) > 0
            ORDER BY r.nr_rodada DESC
            LIMIT 1
        ");
        $stmt->execute([':etapa' => $cdEtapaCompeticao]);
        $rodada = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rodada) {
            return null;
        }

        return [
            'numero' => (int) $rodada['numero'],
            'total' => (int) $rodada['total'],
            'finalizados' => (int) $rodada['finalizados'],
        ];
    }

    private function rodadaExiste(
        int $cdEtapaCompeticao,
        int $numero
    ): bool {
        $stmt = $this->pdo->prepare("
            SELECT EXISTS (
                SELECT 1
                FROM rodada_competicao
                WHERE cd_etapa_competicao = :etapa
                  AND nr_rodada = :numero
            )
        ");
        $stmt->execute([
            ':etapa' => $cdEtapaCompeticao,
            ':numero' => $numero,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    private function calcularClassificacao(
        int $cdEtapaCompeticao,
        array $idsTimes
    ): array {
        $classificacao = [];
        foreach ($idsTimes as $idTime) {
            $classificacao[$idTime] = [
                'id' => $idTime,
                'pontos' => 0.0,
                'jogos' => 0,
                'vitorias' => 0,
                'empates' => 0,
                'derrotas' => 0,
                'byes' => 0,
                'oponentes' => [],
                'buchholz' => 0.0,
            ];
        }

        $stmt = $this->pdo->prepare("
            SELECT
                c.cd_confronto,
                c.cd_vencedor,
                cp.posicao,
                opc.tipo_origem,
                opc.cd_time
            FROM confronto c
            JOIN rodada_competicao r
              ON r.cd_rodada = c.cd_rodada
            JOIN confronto_participante cp
              ON cp.cd_confronto = c.cd_confronto
            JOIN origem_participante_confronto opc
              ON opc.cd_origem_participante = cp.cd_origem_participante
            WHERE r.cd_etapa_competicao = :etapa
              AND c.status = 'FINALIZADO'
            ORDER BY r.nr_rodada, c.nr_confronto, cp.posicao
        ");
        $stmt->execute([':etapa' => $cdEtapaCompeticao]);

        $confrontos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $idConfronto = (int) $linha['cd_confronto'];
            $confrontos[$idConfronto]['vencedor'] =
                $linha['cd_vencedor'] === null
                    ? null
                    : (int) $linha['cd_vencedor'];
            $confrontos[$idConfronto]['participantes'][] = $linha;
        }

        foreach ($confrontos as $confronto) {
            $times = [];
            $temBye = false;
            foreach ($confronto['participantes'] as $participante) {
                if (
                    $participante['tipo_origem'] === 'TIME'
                    && $participante['cd_time'] !== null
                ) {
                    $times[] = (int) $participante['cd_time'];
                } elseif ($participante['tipo_origem'] === 'BYE') {
                    $temBye = true;
                }
            }

            if (count($times) === 2) {
                [$timeA, $timeB] = $times;
                if (!isset($classificacao[$timeA], $classificacao[$timeB])) {
                    continue;
                }

                $classificacao[$timeA]['jogos']++;
                $classificacao[$timeB]['jogos']++;
                $classificacao[$timeA]['oponentes'][] = $timeB;
                $classificacao[$timeB]['oponentes'][] = $timeA;

                if ($confronto['vencedor'] === null) {
                    $classificacao[$timeA]['pontos'] += 0.5;
                    $classificacao[$timeB]['pontos'] += 0.5;
                    $classificacao[$timeA]['empates']++;
                    $classificacao[$timeB]['empates']++;
                } elseif ($confronto['vencedor'] === $timeA) {
                    $classificacao[$timeA]['pontos']++;
                    $classificacao[$timeA]['vitorias']++;
                    $classificacao[$timeB]['derrotas']++;
                } elseif ($confronto['vencedor'] === $timeB) {
                    $classificacao[$timeB]['pontos']++;
                    $classificacao[$timeB]['vitorias']++;
                    $classificacao[$timeA]['derrotas']++;
                } else {
                    throw new RuntimeException(
                        'O vencedor do confronto não participa do sistema suíço.'
                    );
                }
            } elseif (count($times) === 1 && $temBye) {
                $idTime = $times[0];
                if (!isset($classificacao[$idTime])) {
                    continue;
                }

                $classificacao[$idTime]['byes']++;
                if ($confronto['vencedor'] === $idTime) {
                    $classificacao[$idTime]['pontos']++;
                }
            }
        }

        foreach ($classificacao as &$time) {
            foreach ($time['oponentes'] as $idOponente) {
                $time['buchholz'] +=
                    $classificacao[$idOponente]['pontos'];
            }
        }
        unset($time);

        $classificacao = array_values($classificacao);
        usort(
            $classificacao,
            static fn (array $a, array $b): int =>
                ($b['pontos'] <=> $a['pontos'])
                ?: ($b['buchholz'] <=> $a['buchholz'])
                ?: ($b['vitorias'] <=> $a['vitorias'])
                ?: ($a['id'] <=> $b['id'])
        );

        return $classificacao;
    }

    private function salvarClassificacao(
        int $cdGrupo,
        array $classificacao
    ): void {
        $stmt = $this->pdo->prepare("
            SELECT cd_time, cd_classificacao_grupo
            FROM classificacao_grupo
            WHERE cd_grupo = :grupo
        ");
        $stmt->execute([':grupo' => $cdGrupo]);
        $idsClassificacao = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $idsClassificacao[(int) $linha['cd_time']] =
                (int) $linha['cd_classificacao_grupo'];
        }

        $nomesMetricas = [
            'JOGOS',
            'VITORIAS',
            'EMPATES',
            'DERROTAS',
            'PONTOS',
            'SALDO',
            'GOLS_PRO',
            'GOLS_CONTRA',
        ];
        $idsMetricas = [];
        foreach ($nomesMetricas as $nome) {
            $metrica = $this->pdo->prepare("
                INSERT INTO metrica_classificacao (nm_metrica_classificacao)
                VALUES (:nome)
                ON CONFLICT (nm_metrica_classificacao) DO UPDATE
                    SET nm_metrica_classificacao =
                        EXCLUDED.nm_metrica_classificacao
                RETURNING cd_metrica_classificacao
            ");
            $metrica->execute([':nome' => $nome]);
            $idsMetricas[$nome] = (int) $metrica->fetchColumn();
        }

        $atualizarPosicao = $this->pdo->prepare("
            UPDATE classificacao_grupo
            SET posicao = :posicao,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE cd_classificacao_grupo = :classificacao
        ");
        $salvarMetrica = $this->pdo->prepare("
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

        foreach ($classificacao as $indice => $time) {
            $idClassificacao = $idsClassificacao[$time['id']] ?? null;
            if ($idClassificacao === null) {
                throw new RuntimeException(
                    'Falta a classificação inicial de uma equipe suíça.'
                );
            }

            $atualizarPosicao->execute([
                ':posicao' => $indice + 1,
                ':classificacao' => $idClassificacao,
            ]);

            $valores = [
                'JOGOS' => $time['jogos'],
                'VITORIAS' => $time['vitorias'],
                'EMPATES' => $time['empates'],
                'DERROTAS' => $time['derrotas'],
                'PONTOS' => $time['pontos'],
                'SALDO' => 0,
                'GOLS_PRO' => 0,
                'GOLS_CONTRA' => 0,
            ];
            foreach ($valores as $nome => $valor) {
                $salvarMetrica->execute([
                    ':classificacao' => $idClassificacao,
                    ':metrica' => $idsMetricas[$nome],
                    ':valor' => $valor,
                ]);
            }
        }
    }

    private function emparelharPrimeiraRodada(array $idsTimes): array
    {
        $pares = [];
        for ($indice = 0; $indice < count($idsTimes); $indice += 2) {
            $pares[] = [
                $idsTimes[$indice],
                $idsTimes[$indice + 1] ?? null,
            ];
        }

        return $pares;
    }

    private function emparelharPorClassificacao(
        array $classificacao
    ): array {
        $idsTimes = array_column($classificacao, 'id');
        $estatisticas = [];
        $posicoes = [];
        $oponentes = [];

        foreach ($classificacao as $indice => $time) {
            $estatisticas[$time['id']] = $time;
            $posicoes[$time['id']] = $indice;
            $oponentes[$time['id']] = $time['oponentes'];
        }

        if (count($idsTimes) % 2 === 0) {
            $memo = [];
            $pares = $this->buscarPareamento(
                $idsTimes,
                $oponentes,
                $estatisticas,
                $posicoes,
                $memo
            );
            if ($pares === null) {
                throw new RuntimeException(
                    'Não foi possível gerar pareamentos suíços sem revanche.'
                );
            }

            return $pares;
        }

        $candidatosBye = $idsTimes;
        usort(
            $candidatosBye,
            static fn (int $a, int $b): int =>
                ($estatisticas[$a]['byes'] <=> $estatisticas[$b]['byes'])
                ?: ($estatisticas[$a]['pontos'] <=> $estatisticas[$b]['pontos'])
                ?: ($estatisticas[$a]['buchholz'] <=> $estatisticas[$b]['buchholz'])
                ?: ($posicoes[$b] <=> $posicoes[$a])
        );

        foreach ($candidatosBye as $idBye) {
            $restantes = array_values(array_filter(
                $idsTimes,
                static fn (int $idTime): bool => $idTime !== $idBye
            ));
            $memo = [];
            $pares = $this->buscarPareamento(
                $restantes,
                $oponentes,
                $estatisticas,
                $posicoes,
                $memo
            );
            if ($pares !== null) {
                $pares[] = [$idBye, null];
                return $pares;
            }
        }

        throw new RuntimeException(
            'Não foi possível gerar pareamentos suíços sem repetir adversários ou BYE.'
        );
    }

    private function buscarPareamento(
        array $idsTimes,
        array $oponentes,
        array $estatisticas,
        array $posicoes,
        array &$memo
    ): ?array {
        if ($idsTimes === []) {
            return [];
        }

        $chave = implode(',', $idsTimes);
        if (array_key_exists($chave, $memo)) {
            return $memo[$chave];
        }

        $idTime = $idsTimes[0];
        $candidatos = array_values(array_filter(
            array_slice($idsTimes, 1),
            static fn (int $candidato): bool =>
                !in_array($candidato, $oponentes[$idTime], true)
        ));
        usort(
            $candidatos,
            static fn (int $a, int $b): int =>
                abs($estatisticas[$idTime]['pontos'] - $estatisticas[$a]['pontos'])
                <=> abs($estatisticas[$idTime]['pontos'] - $estatisticas[$b]['pontos'])
                ?: ($posicoes[$a] <=> $posicoes[$b])
        );

        foreach ($candidatos as $candidato) {
            $restantes = array_values(array_filter(
                array_slice($idsTimes, 1),
                static fn (int $id): bool => $id !== $candidato
            ));
            $proximos = $this->buscarPareamento(
                $restantes,
                $oponentes,
                $estatisticas,
                $posicoes,
                $memo
            );
            if ($proximos !== null) {
                return array_merge([[$idTime, $candidato]], $proximos);
            }
        }

        $memo[$chave] = null;
        return null;
    }

    private function registrarConfrontos(
        int $cdRodada,
        array $pares
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
