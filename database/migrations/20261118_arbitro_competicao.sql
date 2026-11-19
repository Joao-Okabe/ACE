BEGIN;

CREATE TABLE IF NOT EXISTS arbitro_competicao (
    cd_competicao INTEGER NOT NULL
        REFERENCES competicao ON DELETE CASCADE,
    cd_usuario INTEGER NOT NULL
        REFERENCES usuario ON DELETE CASCADE,
    cd_usuario_vinculador INTEGER NOT NULL
        REFERENCES usuario ON DELETE RESTRICT,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_competicao, cd_usuario)
);

CREATE INDEX IF NOT EXISTS idx_arbitro_competicao_usuario
    ON arbitro_competicao (cd_usuario, cd_competicao);

COMMIT;
