<?php

class RankingController
{
    public function escolas(): void { $this->consultar('escolas'); }
    public function equipes(): void { $this->consultar('equipes'); }

    public function metricasV1(int $esporteId): void
    {
        if ($esporteId <= 0) Response::error('VALIDATION_ERROR', 'ID do esporte inválido.', 400);
        try {
            $stmt = Database::connect()->prepare('SELECT cd_metrica_atleta AS id, chave, nome FROM metrica_atleta WHERE cd_esporte = :esporte ORDER BY nome');
            $stmt->execute([':esporte' => $esporteId]);
            Response::success($stmt->fetchAll(PDO::FETCH_ASSOC), 'Métricas do esporte consultadas.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar as métricas do esporte.', 500);
        }
    }

    public function atletas(): void
    {
        $esporteId = $this->filtroId('esporteId');
        $metricaId = $this->filtroId('metricaId');
        $escolaId = $this->filtroId('escolaId');
        $competicaoId = $this->filtroId('competicaoId');
        $escopo = $_GET['escopo'] ?? 'geral';
        if (!is_string($escopo) || !in_array($escopo, ['geral', 'esporte', 'escola', 'competicao'], true)) {
            Response::error('VALIDATION_ERROR', 'Escopo deve ser geral, esporte, escola ou competicao.', 400);
        }
        if ($esporteId === null || $metricaId === null) Response::error('VALIDATION_ERROR', 'Informe esporteId e metricaId.', 400);
        if (($escopo === 'escola' && $escolaId === null) || ($escopo === 'competicao' && $competicaoId === null)) {
            Response::error('VALIDATION_ERROR', 'O escopo selecionado exige escolaId ou competicaoId.', 400);
        }
        $limite = 100;
        if (isset($_GET['limite'])) {
            $valor = filter_var($_GET['limite'], FILTER_VALIDATE_INT);
            if ($valor === false || $valor < 1 || $valor > 100) Response::error('VALIDATION_ERROR', 'Parâmetro limite deve estar entre 1 e 100.', 400);
            $limite = (int) $valor;
        }
        try {
            $where = ['s.cd_esporte = :esporte', 's.cd_metrica_atleta = :metrica'];
            $params = [':esporte' => $esporteId, ':metrica' => $metricaId];
            if ($escopo === 'escola') { $where[] = 'esc.cd_escola = :escola'; $params[':escola'] = $escolaId; }
            if ($escopo === 'competicao') { $where[] = 'p.cd_competicao = :competicao'; $params[':competicao'] = $competicaoId; }
            if ($escopo !== 'escola' && $escolaId !== null) { $where[] = 'esc.cd_escola = :escola'; $params[':escola'] = $escolaId; }
            if ($escopo !== 'competicao' && $competicaoId !== null) { $where[] = 'p.cd_competicao = :competicao'; $params[':competicao'] = $competicaoId; }
            $pdo = Database::connect();
            $check = $pdo->prepare('SELECT nome, chave FROM metrica_atleta WHERE cd_metrica_atleta = :metrica AND cd_esporte = :esporte');
            $check->execute([':metrica' => $metricaId, ':esporte' => $esporteId]);
            $metrica = $check->fetch(PDO::FETCH_ASSOC);
            if (!$metrica) Response::error('NOT_FOUND', 'Métrica não encontrada para o esporte informado.', 404);
            $sql = "SELECT a.cd_aluno AS aluno_id, u.nm_usuario AS atleta, t.cd_time AS time_id, t.nm_time AS time,
                           esc.cd_escola AS escola_id, esc.nm_escola AS escola,
                           SUM(s.valor) AS valor, COUNT(DISTINCT p.cd_partida) AS jogos
                    FROM estatistica_atleta_partida s
                    JOIN partida p ON p.cd_partida = s.cd_partida
                    JOIN aluno a ON a.cd_usuario = s.cd_usuario
                    JOIN usuario u ON u.cd_usuario = s.cd_usuario
                    JOIN time t ON t.cd_time = s.cd_time
                    LEFT JOIN LATERAL (
                        SELECT e.cd_escola, e.nm_escola
                        FROM vinculo_time_escola vte JOIN escola e ON e.cd_escola = vte.cd_escola
                        WHERE vte.cd_time = s.cd_time AND vte.ativo = TRUE
                        ORDER BY vte.criado_em DESC, vte.cd_escola LIMIT 1
                    ) esc ON TRUE
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY a.cd_aluno, u.nm_usuario, t.cd_time, t.nm_time, esc.cd_escola, esc.nm_escola
                    HAVING SUM(s.valor) > 0
                    ORDER BY SUM(s.valor) DESC, u.nm_usuario ASC
                    LIMIT :limite";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) $stmt->bindValue($key, $value, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($itens as &$item) {
                $item['aluno_id'] = (int) $item['aluno_id']; $item['time_id'] = (int) $item['time_id'];
                $item['valor'] = (float) $item['valor']; $item['jogos'] = (int) $item['jogos'];
                $item['metrica'] = $metrica['chave']; $item['metrica_nome'] = $metrica['nome'];
            }
            unset($item);
            foreach ($itens as &$item) { $item['pontos'] = $item['valor']; }
            unset($item);
            Response::success($itens, 'Ranking de atletas consultado.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar o ranking de atletas.', 500);
        }
    }

    private function consultar(string $tipo): void
    {
        $competicao = $this->filtroId('competicaoId');
        $modalidade = $this->filtroId('modalidadeId');
        $esporte = $this->filtroId('esporteId');
        $escola = $this->filtroId('escolaId');
        $limite = 100;
        if (isset($_GET['limite'])) {
            $valor = filter_var($_GET['limite'], FILTER_VALIDATE_INT);
            if ($valor === false || $valor < 1 || $valor > 100) Response::error('VALIDATION_ERROR', 'Parâmetro limite deve estar entre 1 e 100.', 400);
            $limite = (int) $valor;
        }
        try {
            $filtros = [];
            $params = [];
            if ($competicao !== null) { $filtros[] = 'ec.cd_competicao = :competicao'; $params[':competicao'] = $competicao; }
            if ($modalidade !== null) { $filtros[] = 'c.cd_modalidade = :modalidade'; $params[':modalidade'] = $modalidade; }
            if ($esporte !== null) { $filtros[] = 'c.cd_esporte = :esporte'; $params[':esporte'] = $esporte; }
            if ($escola !== null) { $filtros[] = 'e.cd_escola = :escola'; $params[':escola'] = $escola; }
            $where = $filtros ? ' AND ' . implode(' AND ', $filtros) : '';
            $pdo = Database::connect();
            $sql = "SELECT t.cd_time AS time_id, t.nm_time AS time, e.cd_escola AS escola_id,
                           e.nm_escola AS escola, COUNT(DISTINCT cg.cd_classificacao_grupo) AS jogos,
                           COALESCE(SUM(CASE WHEN lower(mc.nm_metrica_classificacao) IN ('vitorias','vitórias','vitoria','vitória','wins') THEN est.valor ELSE 0 END),0) AS vitorias,
                           COALESCE(SUM(CASE WHEN lower(mc.nm_metrica_classificacao) IN ('pontos','points') THEN est.valor ELSE 0 END),0) AS pontos,
                           COALESCE(SUM(cg.posicao),0) AS _posicoes
                    FROM etapa_competicao ec
                    JOIN competicao c ON c.cd_competicao = ec.cd_competicao
                    JOIN grupo_competicao g ON g.cd_etapa_competicao = ec.cd_etapa_competicao
                    JOIN classificacao_grupo cg ON cg.cd_grupo = g.cd_grupo
                    JOIN time t ON t.cd_time = cg.cd_time
                    JOIN vinculo_time_escola vte ON vte.cd_time = t.cd_time AND vte.ativo = TRUE
                    JOIN escola e ON e.cd_escola = vte.cd_escola
                    LEFT JOIN estatistica_classificacao est ON est.cd_classificacao_grupo = cg.cd_classificacao_grupo
                    LEFT JOIN metrica_classificacao mc ON mc.cd_metrica_classificacao = est.cd_metrica_classificacao
                    WHERE 1=1 {$where}
                    GROUP BY t.cd_time,t.nm_time,e.cd_escola,e.nm_escola
                    ORDER BY pontos DESC,vitorias DESC,_posicoes ASC,t.nm_time ASC LIMIT :limite";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) $stmt->bindValue($key, $value, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($tipo === 'escolas') {
                $agrupado = [];
                foreach ($linhas as $linha) {
                    $id = (int) $linha['escola_id'];
                    if (!isset($agrupado[$id])) $agrupado[$id] = ['escola_id'=>$id,'escola'=>$linha['escola'],'jogos'=>0,'vitorias'=>0,'pontos'=>0];
                    $agrupado[$id]['jogos'] += (int) $linha['jogos'];
                    $agrupado[$id]['vitorias'] += (int) $linha['vitorias'];
                    $agrupado[$id]['pontos'] += (float) $linha['pontos'];
                }
                $linhas = array_values($agrupado);
                usort($linhas, fn($a,$b) => ($b['pontos'] <=> $a['pontos']) ?: (($b['vitorias'] <=> $a['vitorias']) ?: strcasecmp($a['escola'],$b['escola'])));
                $linhas = array_slice($linhas, 0, $limite);
            } else {
                foreach ($linhas as &$linha) { unset($linha['_posicoes']); $linha['time_id']=(int)$linha['time_id']; $linha['escola_id']=(int)$linha['escola_id']; $linha['jogos']=(int)$linha['jogos']; $linha['vitorias']=(int)$linha['vitorias']; $linha['pontos']=(float)$linha['pontos']; }
                unset($linha);
            }
            Response::success($linhas, 'Ranking consultado.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível consultar o ranking.', 500);
        }
    }

    private function filtroId(string $nome): ?int
    {
        if (!isset($_GET[$nome]) || $_GET[$nome] === '') return null;
        $id = filter_var($_GET[$nome], FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) Response::error('VALIDATION_ERROR', "Parâmetro {$nome} inválido.", 400);
        return (int) $id;
    }
}
