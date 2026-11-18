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

COMMIT;
