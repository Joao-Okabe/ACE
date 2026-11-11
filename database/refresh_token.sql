-- Migração para refresh tokens do ACE (PostgreSQL).

CREATE TABLE IF NOT EXISTS refresh_token (
    cd_refresh_token BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_usuario INTEGER NOT NULL
        REFERENCES usuario(cd_usuario) ON DELETE CASCADE,
    token_hash CHAR(64) NOT NULL UNIQUE,
    familia UUID NOT NULL,
    expira_em TIMESTAMPTZ NOT NULL,
    criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usado_em TIMESTAMPTZ,
    revogado_em TIMESTAMPTZ,
    substituido_por_hash CHAR(64),

    CONSTRAINT ck_refresh_token_hash
        CHECK (token_hash ~ '^[a-f0-9]{64}$'),
    CONSTRAINT ck_refresh_token_substituido
        CHECK (
            substituido_por_hash IS NULL
            OR substituido_por_hash ~ '^[a-f0-9]{64}$'
        )
);

CREATE INDEX IF NOT EXISTS idx_refresh_token_usuario
    ON refresh_token (cd_usuario);

CREATE INDEX IF NOT EXISTS idx_refresh_token_familia
    ON refresh_token (familia);

CREATE INDEX IF NOT EXISTS idx_refresh_token_expira
    ON refresh_token (expira_em);
