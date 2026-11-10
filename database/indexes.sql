-- ============================================================
-- ÍNDICES
-- ============================================================

CREATE INDEX idx_competicao_criador
    ON competicao (cd_criador);

CREATE INDEX idx_competicao_escola
    ON competicao (cd_escola);

CREATE INDEX idx_partida_competicao
    ON partida (cd_competicao);

CREATE INDEX idx_partida_criador
    ON partida (cd_criador);

CREATE INDEX idx_partida_status
    ON partida (status);

CREATE INDEX idx_confronto_rodada
    ON confronto (cd_rodada);

CREATE INDEX idx_origem_confronto
    ON origem_participante_confronto (cd_confronto);

CREATE INDEX idx_origem_classificacao
    ON origem_participante_confronto (cd_classificacao_grupo);

CREATE INDEX idx_participante_competicao_usuario
    ON participante_competicao (cd_usuario, cd_competicao);

CREATE INDEX idx_participante_partida_usuario
    ON participante_partida (cd_usuario, cd_partida);

-- Impede mais de uma partida vinculada ao mesmo confronto.
CREATE UNIQUE INDEX uq_partida_confronto
    ON partida (cd_confronto)
    WHERE cd_confronto IS NOT NULL;

-- Impede mais de um mandante por partida.
CREATE UNIQUE INDEX uq_partida_mandante
    ON vinculo_partida_time (cd_partida)
    WHERE mandante = TRUE;

