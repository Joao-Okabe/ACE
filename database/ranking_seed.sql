-- Dados de demonstração para rankings individuais, de equipes e de escolas.
-- Execute após database/script.sql. Este arquivo pode ser executado novamente.

BEGIN;

INSERT INTO competicao (
    nm_competicao,
    cd_criador,
    cd_esporte,
    cd_modalidade,
    cd_formato,
    cd_escola,
    inicio_em,
    fim_em,
    status,
    descricao
)
SELECT
    'Ranking Demo ACE',
    criador.cd_usuario,
    esporte.cd_esporte,
    modalidade.cd_modalidade,
    formato.cd_formato,
    escola.cd_escola,
    CURRENT_DATE::TIMESTAMP - INTERVAL '14 days',
    CURRENT_DATE::TIMESTAMP + INTERVAL '30 days',
    'EM_ANDAMENTO',
    'Competição isolada para testar rankings individuais, de equipes e escolas.'
FROM usuario criador
JOIN esporte ON esporte.nm_esporte = 'Futsal'
JOIN modalidade
  ON modalidade.sexo = 'MISTO'
 AND modalidade.categoria = 'Sub18'
JOIN formato ON formato.nm_formato = 'GRUPOS_ELIMINATORIA'
JOIN escola ON escola.nm_escola = 'Etec de Itanhaém'
WHERE criador.email = 'biazzus@gmail.com'
  AND NOT EXISTS (
      SELECT 1
      FROM competicao existente
      WHERE existente.nm_competicao = 'Ranking Demo ACE'
  );

INSERT INTO inscricao_competicao (
    cd_competicao,
    dt_inicio_inscricao,
    dt_encerramento_inscricao
)
SELECT
    competicao.cd_competicao,
    competicao.inicio_em - INTERVAL '30 days',
    competicao.inicio_em - INTERVAL '15 days'
FROM competicao
WHERE competicao.nm_competicao = 'Ranking Demo ACE'
  AND NOT EXISTS (
      SELECT 1
      FROM inscricao_competicao inscricao
      WHERE inscricao.cd_competicao = competicao.cd_competicao
  );

WITH equipes (nm_time) AS (
    VALUES
        ('Equipe Seed E2-A'),
        ('Equipe Seed E2-B'),
        ('Equipe Seed E3-A'),
        ('Equipe Seed E3-B'),
        ('Equipe Seed E4-A'),
        ('Equipe Seed E4-B'),
        ('Equipe Seed E5-A'),
        ('Equipe Seed E6-A')
)
INSERT INTO vinculo_inscricao_time (cd_inscricao_competicao, cd_time)
SELECT inscricao.cd_inscricao_competicao, time.cd_time
FROM competicao
JOIN inscricao_competicao inscricao
  ON inscricao.cd_competicao = competicao.cd_competicao
JOIN equipes ON TRUE
JOIN time ON time.nm_time = equipes.nm_time
WHERE competicao.nm_competicao = 'Ranking Demo ACE'
ON CONFLICT (cd_inscricao_competicao, cd_time) DO NOTHING;

DO $$
DECLARE
    total_equipes INTEGER;
    total_atletas INTEGER;
    total_metricas INTEGER;
BEGIN
    SELECT COUNT(DISTINCT time.cd_time)
    INTO total_equipes
    FROM vinculo_inscricao_time inscricao
    JOIN inscricao_competicao cadastro
      ON cadastro.cd_inscricao_competicao =
         inscricao.cd_inscricao_competicao
    JOIN competicao ON competicao.cd_competicao = cadastro.cd_competicao
    JOIN time ON time.cd_time = inscricao.cd_time
    WHERE competicao.nm_competicao = 'Ranking Demo ACE'
      AND time.nm_time LIKE 'Equipe Seed %';

    SELECT COUNT(DISTINCT integrante.cd_usuario)
    INTO total_atletas
    FROM vinculo_inscricao_time inscricao
    JOIN inscricao_competicao cadastro
      ON cadastro.cd_inscricao_competicao =
         inscricao.cd_inscricao_competicao
    JOIN competicao ON competicao.cd_competicao = cadastro.cd_competicao
    JOIN vinculo_time_integrante integrante
      ON integrante.cd_time = inscricao.cd_time
     AND integrante.ativo = TRUE
    WHERE competicao.nm_competicao = 'Ranking Demo ACE'
      AND integrante.cd_usuario IN (
          SELECT aluno.cd_usuario
          FROM aluno
          JOIN usuario ON usuario.cd_usuario = aluno.cd_usuario
          WHERE usuario.email LIKE 'seed.atleta.%@example.test'
      );

    SELECT COUNT(*)
    INTO total_metricas
    FROM metrica_atleta
    WHERE cd_esporte = (
        SELECT cd_esporte
        FROM competicao
        WHERE nm_competicao = 'Ranking Demo ACE'
    )
      AND chave IN ('pontuacao', 'assistencias', 'bloqueios');

    IF total_equipes <> 8 OR total_atletas <> 40 OR total_metricas <> 3 THEN
        RAISE EXCEPTION
            'Pré-requisitos do ranking ausentes: equipes %, atletas %, métricas %; execute database/script.sql antes.',
            total_equipes,
            total_atletas,
            total_metricas;
    END IF;
END
$$;

INSERT INTO etapa_competicao (
    cd_competicao,
    cd_tipo_etapa,
    nm_etapa,
    ordem,
    descricao
)
SELECT
    competicao.cd_competicao,
    tipo_etapa.cd_tipo_etapa,
    'Grupos para Ranking',
    1,
    'Etapa de demonstração para classificação de equipes e escolas.'
FROM competicao
JOIN tipo_etapa ON tipo_etapa.nm_tipo_etapa = 'GRUPOS'
WHERE competicao.nm_competicao = 'Ranking Demo ACE'
  AND NOT EXISTS (
      SELECT 1
      FROM etapa_competicao existente
      WHERE existente.cd_competicao = competicao.cd_competicao
        AND existente.ordem = 1
  );

INSERT INTO grupo_competicao (cd_etapa_competicao, nm_grupo, ordem)
SELECT etapa.cd_etapa_competicao, grupos.nm_grupo, grupos.ordem
FROM competicao
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN (
    VALUES ('Grupo A', 1), ('Grupo B', 2)
) AS grupos(nm_grupo, ordem) ON TRUE
WHERE competicao.nm_competicao = 'Ranking Demo ACE'
  AND NOT EXISTS (
      SELECT 1
      FROM grupo_competicao existente
      WHERE existente.cd_etapa_competicao = etapa.cd_etapa_competicao
        AND existente.nm_grupo = grupos.nm_grupo
  );

WITH classificacao (
    nm_grupo,
    nm_time,
    posicao,
    pontos,
    vitorias
) AS (
    VALUES
        ('Grupo A', 'Equipe Seed E2-A', 1, 3, 1),
        ('Grupo A', 'Equipe Seed E3-B', 2, 3, 1),
        ('Grupo A', 'Equipe Seed E2-B', 3, 0, 0),
        ('Grupo A', 'Equipe Seed E3-A', 4, 0, 0),
        ('Grupo B', 'Equipe Seed E6-A', 1, 3, 1),
        ('Grupo B', 'Equipe Seed E4-A', 2, 3, 1),
        ('Grupo B', 'Equipe Seed E4-B', 3, 0, 0),
        ('Grupo B', 'Equipe Seed E5-A', 4, 0, 0)
)
INSERT INTO vinculo_grupo_time (cd_grupo, cd_time)
SELECT grupo.cd_grupo, time.cd_time
FROM classificacao
JOIN competicao ON competicao.nm_competicao = 'Ranking Demo ACE'
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN grupo_competicao grupo
  ON grupo.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND grupo.nm_grupo = classificacao.nm_grupo
JOIN time ON time.nm_time = classificacao.nm_time
ON CONFLICT (cd_grupo, cd_time) DO NOTHING;

WITH classificacao (
    nm_grupo,
    nm_time,
    posicao,
    pontos,
    vitorias
) AS (
    VALUES
        ('Grupo A', 'Equipe Seed E2-A', 1, 3, 1),
        ('Grupo A', 'Equipe Seed E3-B', 2, 3, 1),
        ('Grupo A', 'Equipe Seed E2-B', 3, 0, 0),
        ('Grupo A', 'Equipe Seed E3-A', 4, 0, 0),
        ('Grupo B', 'Equipe Seed E6-A', 1, 3, 1),
        ('Grupo B', 'Equipe Seed E4-A', 2, 3, 1),
        ('Grupo B', 'Equipe Seed E4-B', 3, 0, 0),
        ('Grupo B', 'Equipe Seed E5-A', 4, 0, 0)
)
INSERT INTO classificacao_grupo (cd_grupo, cd_time, posicao)
SELECT grupo.cd_grupo, time.cd_time, classificacao.posicao
FROM classificacao
JOIN competicao ON competicao.nm_competicao = 'Ranking Demo ACE'
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN grupo_competicao grupo
  ON grupo.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND grupo.nm_grupo = classificacao.nm_grupo
JOIN time ON time.nm_time = classificacao.nm_time
ON CONFLICT (cd_grupo, cd_time)
DO UPDATE SET posicao = EXCLUDED.posicao;

INSERT INTO metrica_classificacao (nm_metrica_classificacao, descricao)
VALUES
    ('pontos', 'Pontos obtidos pela equipe.'),
    ('vitorias', 'Vitórias obtidas pela equipe.')
ON CONFLICT (nm_metrica_classificacao)
DO UPDATE SET descricao = EXCLUDED.descricao;

WITH classificacao (
    nm_grupo,
    nm_time,
    pontos,
    vitorias
) AS (
    VALUES
        ('Grupo A', 'Equipe Seed E2-A', 3, 1),
        ('Grupo A', 'Equipe Seed E3-B', 3, 1),
        ('Grupo A', 'Equipe Seed E2-B', 0, 0),
        ('Grupo A', 'Equipe Seed E3-A', 0, 0),
        ('Grupo B', 'Equipe Seed E6-A', 3, 1),
        ('Grupo B', 'Equipe Seed E4-A', 3, 1),
        ('Grupo B', 'Equipe Seed E4-B', 0, 0),
        ('Grupo B', 'Equipe Seed E5-A', 0, 0)
),
valores (chave, valor) AS (
    VALUES ('pontos', 'pontos'), ('vitorias', 'vitorias')
)
INSERT INTO estatistica_classificacao (
    cd_classificacao_grupo,
    cd_metrica_classificacao,
    valor
)
SELECT
    classificacao_grupo.cd_classificacao_grupo,
    metrica_classificacao.cd_metrica_classificacao,
    CASE valores.valor
        WHEN 'pontos' THEN classificacao.pontos
        ELSE classificacao.vitorias
    END
FROM classificacao
JOIN competicao ON competicao.nm_competicao = 'Ranking Demo ACE'
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN grupo_competicao grupo
  ON grupo.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND grupo.nm_grupo = classificacao.nm_grupo
JOIN time ON time.nm_time = classificacao.nm_time
JOIN classificacao_grupo
  ON classificacao_grupo.cd_grupo = grupo.cd_grupo
 AND classificacao_grupo.cd_time = time.cd_time
CROSS JOIN valores
JOIN metrica_classificacao
  ON metrica_classificacao.nm_metrica_classificacao = valores.chave
ON CONFLICT (cd_classificacao_grupo, cd_metrica_classificacao)
DO UPDATE SET valor = EXCLUDED.valor;

INSERT INTO rodada_competicao (
    cd_etapa_competicao,
    nr_rodada,
    nm_rodada,
    status
)
SELECT etapa.cd_etapa_competicao, 1, 'Rodada 1', 'FINALIZADA'
FROM competicao
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
WHERE competicao.nm_competicao = 'Ranking Demo ACE'
  AND NOT EXISTS (
      SELECT 1
      FROM rodada_competicao existente
      WHERE existente.cd_etapa_competicao = etapa.cd_etapa_competicao
        AND existente.nr_rodada = 1
  );

WITH jogos (
    nr_confronto,
    nm_grupo,
    nm_mandante,
    nm_visitante,
    nm_vencedor,
    placar_mandante,
    placar_visitante
) AS (
    VALUES
        (1, 'Grupo A', 'Equipe Seed E2-A', 'Equipe Seed E2-B', 'Equipe Seed E2-A', 3, 1),
        (2, 'Grupo A', 'Equipe Seed E3-A', 'Equipe Seed E3-B', 'Equipe Seed E3-B', 0, 2),
        (3, 'Grupo B', 'Equipe Seed E4-A', 'Equipe Seed E4-B', 'Equipe Seed E4-A', 2, 0),
        (4, 'Grupo B', 'Equipe Seed E5-A', 'Equipe Seed E6-A', 'Equipe Seed E6-A', 1, 4)
)
INSERT INTO origem_participante_confronto (tipo_origem, cd_time)
SELECT 'TIME', time.cd_time
FROM jogos
JOIN time ON time.nm_time IN (
    jogos.nm_mandante,
    jogos.nm_visitante
)
WHERE NOT EXISTS (
    SELECT 1
    FROM origem_participante_confronto existente
    WHERE existente.tipo_origem = 'TIME'
      AND existente.cd_time = time.cd_time
);

WITH jogos (
    nr_confronto,
    nm_grupo,
    nm_mandante,
    nm_visitante,
    nm_vencedor,
    placar_mandante,
    placar_visitante
) AS (
    VALUES
        (1, 'Grupo A', 'Equipe Seed E2-A', 'Equipe Seed E2-B', 'Equipe Seed E2-A', 3, 1),
        (2, 'Grupo A', 'Equipe Seed E3-A', 'Equipe Seed E3-B', 'Equipe Seed E3-B', 0, 2),
        (3, 'Grupo B', 'Equipe Seed E4-A', 'Equipe Seed E4-B', 'Equipe Seed E4-A', 2, 0),
        (4, 'Grupo B', 'Equipe Seed E5-A', 'Equipe Seed E6-A', 'Equipe Seed E6-A', 1, 4)
)
INSERT INTO confronto (
    cd_rodada,
    nr_confronto,
    nome,
    status,
    cd_vencedor
)
SELECT
    rodada.cd_rodada,
    jogos.nr_confronto,
    'Partida de ranking ' || jogos.nr_confronto,
    'FINALIZADO',
    vencedor.cd_time
FROM jogos
JOIN competicao ON competicao.nm_competicao = 'Ranking Demo ACE'
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN grupo_competicao grupo
  ON grupo.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND grupo.nm_grupo = jogos.nm_grupo
JOIN rodada_competicao rodada
  ON rodada.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND rodada.nr_rodada = 1
JOIN time vencedor ON vencedor.nm_time = jogos.nm_vencedor
WHERE NOT EXISTS (
    SELECT 1
    FROM confronto existente
    WHERE existente.cd_rodada = rodada.cd_rodada
      AND existente.nr_confronto = jogos.nr_confronto
);

WITH jogos (
    nr_confronto,
    nm_grupo,
    nm_mandante,
    nm_visitante,
    nm_vencedor,
    placar_mandante,
    placar_visitante
) AS (
    VALUES
        (1, 'Grupo A', 'Equipe Seed E2-A', 'Equipe Seed E2-B', 'Equipe Seed E2-A', 3, 1),
        (2, 'Grupo A', 'Equipe Seed E3-A', 'Equipe Seed E3-B', 'Equipe Seed E3-B', 0, 2),
        (3, 'Grupo B', 'Equipe Seed E4-A', 'Equipe Seed E4-B', 'Equipe Seed E4-A', 2, 0),
        (4, 'Grupo B', 'Equipe Seed E5-A', 'Equipe Seed E6-A', 'Equipe Seed E6-A', 1, 4)
)
INSERT INTO confronto_participante (
    cd_confronto,
    posicao,
    cd_origem_participante,
    status
)
SELECT
    confronto.cd_confronto,
    participantes.posicao,
    origem.cd_origem_participante,
    'DEFINIDO'
FROM jogos
JOIN competicao ON competicao.nm_competicao = 'Ranking Demo ACE'
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN grupo_competicao grupo
  ON grupo.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND grupo.nm_grupo = jogos.nm_grupo
JOIN rodada_competicao rodada
  ON rodada.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND rodada.nr_rodada = 1
JOIN confronto
  ON confronto.cd_rodada = rodada.cd_rodada
 AND confronto.nr_confronto = jogos.nr_confronto
CROSS JOIN LATERAL (
    VALUES (1, jogos.nm_mandante), (2, jogos.nm_visitante)
) AS participantes(posicao, nm_time)
JOIN time ON time.nm_time = participantes.nm_time
JOIN origem_participante_confronto origem
  ON origem.tipo_origem = 'TIME'
 AND origem.cd_time = time.cd_time
ON CONFLICT (cd_confronto, posicao)
DO UPDATE SET
    cd_origem_participante = EXCLUDED.cd_origem_participante,
    status = EXCLUDED.status;

WITH jogos (
    nr_confronto,
    nm_grupo,
    nm_mandante,
    nm_visitante,
    nm_vencedor,
    placar_mandante,
    placar_visitante
) AS (
    VALUES
        (1, 'Grupo A', 'Equipe Seed E2-A', 'Equipe Seed E2-B', 'Equipe Seed E2-A', 3, 1),
        (2, 'Grupo A', 'Equipe Seed E3-A', 'Equipe Seed E3-B', 'Equipe Seed E3-B', 0, 2),
        (3, 'Grupo B', 'Equipe Seed E4-A', 'Equipe Seed E4-B', 'Equipe Seed E4-A', 2, 0),
        (4, 'Grupo B', 'Equipe Seed E5-A', 'Equipe Seed E6-A', 'Equipe Seed E6-A', 1, 4)
)
INSERT INTO partida (
    cd_competicao,
    cd_confronto,
    cd_criador,
    visibilidade,
    status,
    inicio_agendado
)
SELECT
    competicao.cd_competicao,
    confronto.cd_confronto,
    criador.cd_usuario,
    'PUBLICA',
    'FINALIZADA',
    competicao.inicio_em + jogos.nr_confronto * INTERVAL '1 day'
FROM jogos
JOIN competicao ON competicao.nm_competicao = 'Ranking Demo ACE'
JOIN usuario criador ON criador.email = 'biazzus@gmail.com'
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN grupo_competicao grupo
  ON grupo.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND grupo.nm_grupo = jogos.nm_grupo
JOIN rodada_competicao rodada
  ON rodada.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND rodada.nr_rodada = 1
JOIN confronto
  ON confronto.cd_rodada = rodada.cd_rodada
 AND confronto.nr_confronto = jogos.nr_confronto
WHERE NOT EXISTS (
    SELECT 1
    FROM partida existente
    WHERE existente.cd_confronto = confronto.cd_confronto
);

WITH jogos (nr_confronto, nm_grupo) AS (
    VALUES
        (1, 'Grupo A'),
        (2, 'Grupo A'),
        (3, 'Grupo B'),
        (4, 'Grupo B')
)
INSERT INTO info_partida (
    cd_partida,
    dt_partida,
    inicio_em,
    fim_em,
    local_partida,
    observacoes
)
SELECT
    partida.cd_partida,
    partida.inicio_agendado::DATE,
    partida.inicio_agendado,
    partida.inicio_agendado + INTERVAL '2 hours',
    'Ginásio da escola',
    'Partida finalizada criada pelo seed de ranking.'
FROM jogos
JOIN competicao ON competicao.nm_competicao = 'Ranking Demo ACE'
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN grupo_competicao grupo
  ON grupo.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND grupo.nm_grupo = jogos.nm_grupo
JOIN rodada_competicao rodada
  ON rodada.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND rodada.nr_rodada = 1
JOIN confronto
  ON confronto.cd_rodada = rodada.cd_rodada
 AND confronto.nr_confronto = jogos.nr_confronto
JOIN partida ON partida.cd_confronto = confronto.cd_confronto
WHERE NOT EXISTS (
    SELECT 1
    FROM info_partida existente
    WHERE existente.cd_partida = partida.cd_partida
);

WITH jogos (
    nr_confronto,
    nm_grupo,
    nm_mandante,
    nm_visitante,
    nm_vencedor,
    placar_mandante,
    placar_visitante
) AS (
    VALUES
        (1, 'Grupo A', 'Equipe Seed E2-A', 'Equipe Seed E2-B', 'Equipe Seed E2-A', 3, 1),
        (2, 'Grupo A', 'Equipe Seed E3-A', 'Equipe Seed E3-B', 'Equipe Seed E3-B', 0, 2),
        (3, 'Grupo B', 'Equipe Seed E4-A', 'Equipe Seed E4-B', 'Equipe Seed E4-A', 2, 0),
        (4, 'Grupo B', 'Equipe Seed E5-A', 'Equipe Seed E6-A', 'Equipe Seed E6-A', 1, 4)
)
INSERT INTO vinculo_partida_time (cd_partida, cd_time, mandante)
SELECT
    partida.cd_partida,
    time.cd_time,
    participantes.posicao = 1
FROM jogos
JOIN competicao ON competicao.nm_competicao = 'Ranking Demo ACE'
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN grupo_competicao grupo
  ON grupo.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND grupo.nm_grupo = jogos.nm_grupo
JOIN rodada_competicao rodada
  ON rodada.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND rodada.nr_rodada = 1
JOIN confronto
  ON confronto.cd_rodada = rodada.cd_rodada
 AND confronto.nr_confronto = jogos.nr_confronto
JOIN partida ON partida.cd_confronto = confronto.cd_confronto
CROSS JOIN LATERAL (
    VALUES (1, jogos.nm_mandante), (2, jogos.nm_visitante)
) AS participantes(posicao, nm_time)
JOIN time ON time.nm_time = participantes.nm_time
ON CONFLICT (cd_partida, cd_time) DO NOTHING;

INSERT INTO participante_partida (
    cd_partida,
    cd_usuario,
    cd_time,
    origem
)
SELECT
    partida.cd_partida,
    integrante.cd_usuario,
    integrante.cd_time,
    'INTEGRANTE'
FROM partida
JOIN competicao ON competicao.cd_competicao = partida.cd_competicao
JOIN vinculo_partida_time participante
  ON participante.cd_partida = partida.cd_partida
JOIN vinculo_time_integrante integrante
  ON integrante.cd_time = participante.cd_time
 AND integrante.ativo = TRUE
WHERE competicao.nm_competicao = 'Ranking Demo ACE'
ON CONFLICT (cd_partida, cd_usuario, cd_time, origem) DO NOTHING;

WITH jogos (
    nr_confronto,
    nm_grupo,
    nm_mandante,
    nm_visitante,
    nm_vencedor,
    placar_mandante,
    placar_visitante
) AS (
    VALUES
        (1, 'Grupo A', 'Equipe Seed E2-A', 'Equipe Seed E2-B', 'Equipe Seed E2-A', 3, 1),
        (2, 'Grupo A', 'Equipe Seed E3-A', 'Equipe Seed E3-B', 'Equipe Seed E3-B', 0, 2),
        (3, 'Grupo B', 'Equipe Seed E4-A', 'Equipe Seed E4-B', 'Equipe Seed E4-A', 2, 0),
        (4, 'Grupo B', 'Equipe Seed E5-A', 'Equipe Seed E6-A', 'Equipe Seed E6-A', 1, 4)
)
INSERT INTO etapa_partida (cd_partida, nr_etapa, inicio_em, fim_em)
SELECT
    partida.cd_partida,
    1,
    partida.inicio_agendado,
    partida.inicio_agendado + INTERVAL '2 hours'
FROM jogos
JOIN competicao ON competicao.nm_competicao = 'Ranking Demo ACE'
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN grupo_competicao grupo
  ON grupo.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND grupo.nm_grupo = jogos.nm_grupo
JOIN rodada_competicao rodada
  ON rodada.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND rodada.nr_rodada = 1
JOIN confronto
  ON confronto.cd_rodada = rodada.cd_rodada
 AND confronto.nr_confronto = jogos.nr_confronto
JOIN partida ON partida.cd_confronto = confronto.cd_confronto
ON CONFLICT (cd_partida, nr_etapa)
DO UPDATE SET
    inicio_em = EXCLUDED.inicio_em,
    fim_em = EXCLUDED.fim_em;

WITH jogos (
    nr_confronto,
    nm_grupo,
    nm_mandante,
    nm_visitante,
    nm_vencedor,
    placar_mandante,
    placar_visitante
) AS (
    VALUES
        (1, 'Grupo A', 'Equipe Seed E2-A', 'Equipe Seed E2-B', 'Equipe Seed E2-A', 3, 1),
        (2, 'Grupo A', 'Equipe Seed E3-A', 'Equipe Seed E3-B', 'Equipe Seed E3-B', 0, 2),
        (3, 'Grupo B', 'Equipe Seed E4-A', 'Equipe Seed E4-B', 'Equipe Seed E4-A', 2, 0),
        (4, 'Grupo B', 'Equipe Seed E5-A', 'Equipe Seed E6-A', 'Equipe Seed E6-A', 1, 4)
)
INSERT INTO resultado_etapa_partida (
    cd_etapa_partida,
    cd_time,
    pontuacao
)
SELECT
    etapa_partida.cd_etapa_partida,
    time.cd_time,
    placares.pontuacao
FROM jogos
JOIN competicao ON competicao.nm_competicao = 'Ranking Demo ACE'
JOIN etapa_competicao etapa
  ON etapa.cd_competicao = competicao.cd_competicao
 AND etapa.ordem = 1
JOIN grupo_competicao grupo
  ON grupo.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND grupo.nm_grupo = jogos.nm_grupo
JOIN rodada_competicao rodada
  ON rodada.cd_etapa_competicao = etapa.cd_etapa_competicao
 AND rodada.nr_rodada = 1
JOIN confronto
  ON confronto.cd_rodada = rodada.cd_rodada
 AND confronto.nr_confronto = jogos.nr_confronto
JOIN partida ON partida.cd_confronto = confronto.cd_confronto
JOIN etapa_partida
  ON etapa_partida.cd_partida = partida.cd_partida
 AND etapa_partida.nr_etapa = 1
CROSS JOIN LATERAL (
    VALUES
        (jogos.nm_mandante, jogos.placar_mandante),
        (jogos.nm_visitante, jogos.placar_visitante)
) AS placares(nm_time, pontuacao)
JOIN time ON time.nm_time = placares.nm_time
ON CONFLICT (cd_etapa_partida, cd_time)
DO UPDATE SET pontuacao = EXCLUDED.pontuacao;

WITH metricas (chave, fator) AS (
    VALUES
        ('pontuacao', 0),
        ('assistencias', 1),
        ('bloqueios', 2)
)
INSERT INTO estatistica_atleta_partida (
    cd_partida,
    cd_usuario,
    cd_time,
    cd_esporte,
    cd_metrica_atleta,
    valor
)
SELECT
    partida.cd_partida,
    integrante.cd_usuario,
    integrante.cd_time,
    competicao.cd_esporte,
    metrica.cd_metrica_atleta,
    CASE metricas.fator
        WHEN 0 THEN integrante.numero_camisa + confronto.nr_confronto
        WHEN 1 THEN (integrante.numero_camisa + confronto.nr_confronto) % 4
        ELSE (integrante.numero_camisa + confronto.nr_confronto) % 3
    END
FROM partida
JOIN competicao ON competicao.cd_competicao = partida.cd_competicao
JOIN confronto ON confronto.cd_confronto = partida.cd_confronto
JOIN vinculo_partida_time participante
  ON participante.cd_partida = partida.cd_partida
JOIN vinculo_time_integrante integrante
  ON integrante.cd_time = participante.cd_time
 AND integrante.ativo = TRUE
CROSS JOIN metricas
JOIN metrica_atleta metrica
  ON metrica.cd_esporte = competicao.cd_esporte
 AND metrica.chave = metricas.chave
WHERE competicao.nm_competicao = 'Ranking Demo ACE'
ON CONFLICT (cd_partida, cd_usuario, cd_time, cd_metrica_atleta)
DO UPDATE SET
    valor = EXCLUDED.valor,
    atualizado_em = CURRENT_TIMESTAMP;

COMMIT;

SELECT
    competicao.cd_competicao,
    competicao.nm_competicao,
    esporte.cd_esporte,
    metricas.cd_metrica_atleta,
    metricas.chave,
    metricas.nome
FROM competicao
JOIN esporte ON esporte.cd_esporte = competicao.cd_esporte
JOIN metrica_atleta metricas ON metricas.cd_esporte = esporte.cd_esporte
WHERE competicao.nm_competicao = 'Ranking Demo ACE'
ORDER BY metricas.nome;
