BEGIN;

CREATE TABLE IF NOT EXISTS metrica_atleta (
    cd_metrica_atleta INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_esporte INTEGER NOT NULL REFERENCES esporte ON DELETE CASCADE,
    chave VARCHAR(50) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    UNIQUE (cd_esporte, chave),
    UNIQUE (cd_metrica_atleta, cd_esporte)
);

CREATE TABLE IF NOT EXISTS estatistica_atleta_partida (
    cd_partida INTEGER NOT NULL REFERENCES partida ON DELETE CASCADE,
    cd_usuario INTEGER NOT NULL REFERENCES aluno (cd_usuario) ON DELETE CASCADE,
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE RESTRICT,
    cd_esporte INTEGER NOT NULL REFERENCES esporte ON DELETE CASCADE,
    cd_metrica_atleta INTEGER NOT NULL,
    valor NUMERIC(15,3) NOT NULL DEFAULT 0 CHECK (valor >= 0),
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_partida, cd_usuario, cd_time, cd_metrica_atleta),
    FOREIGN KEY (cd_metrica_atleta, cd_esporte)
        REFERENCES metrica_atleta (cd_metrica_atleta, cd_esporte) ON DELETE CASCADE
);

INSERT INTO metrica_atleta (cd_esporte, chave, nome)
SELECT e.cd_esporte, m.chave, m.nome
FROM esporte e
CROSS JOIN (VALUES
    ('futsal', 'pontuacao', 'Gols'), ('futsal', 'assistencias', 'Assistências'), ('futsal', 'bloqueios', 'Bloqueios'),
    ('basquete', 'pontuacao', 'Pontos'), ('basquete', 'assistencias', 'Assistências'), ('basquete', 'bloqueios', 'Bloqueios'),
    ('handebol', 'pontuacao', 'Gols'), ('handebol', 'assistencias', 'Assistências'), ('handebol', 'bloqueios', 'Bloqueios'),
    ('vôlei', 'pontuacao', 'Pontos'), ('vôlei', 'assistencias', 'Assistências'), ('vôlei', 'bloqueios', 'Bloqueios'),
    ('queimada', 'pontuacao', 'Eliminações'), ('queimada', 'assistencias', 'Assistências'), ('queimada', 'bloqueios', 'Bloqueios'),
    ('xadrez', 'pontuacao', 'Pontos'), ('tênis de mesa', 'pontuacao', 'Pontos'),
    ('natação', 'pontuacao', 'Pontos'), ('judô', 'pontuacao', 'Pontos')
) AS m(esporte, chave, nome)
WHERE lower(trim(e.nm_esporte)) = m.esporte
ON CONFLICT (cd_esporte, chave) DO UPDATE SET nome = EXCLUDED.nome;

COMMIT;
