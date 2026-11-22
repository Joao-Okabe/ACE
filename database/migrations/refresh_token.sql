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

-- Compatibilidade com instalações que criaram a tabela antes da rotação.
ALTER TABLE refresh_token
    ADD COLUMN IF NOT EXISTS revogado_em TIMESTAMPTZ,
    ADD COLUMN IF NOT EXISTS usado_em TIMESTAMPTZ,
    ADD COLUMN IF NOT EXISTS substituido_por_hash CHAR(64);

SELECT
    conname AS nome,
    contype AS tipo,
    pg_get_constraintdef(oid) AS definicao
FROM pg_constraint
WHERE conrelid = 'refresh_token'::regclass;

SELECT
    indexname,
    indexdef
FROM pg_indexes
WHERE tablename = 'refresh_token';

SELECT
    familia,
    COUNT(*) AS total_tokens,
    COUNT(*) FILTER (
        WHERE revogado_em IS NULL
          AND expira_em > CURRENT_TIMESTAMP
    ) AS tokens_ativos,
    COUNT(*) FILTER (
        WHERE usado_em IS NOT NULL
    ) AS tokens_consumidos,
    COUNT(*) FILTER (
        WHERE revogado_em IS NOT NULL
    ) AS tokens_revogados,
    MAX(expira_em) AS ultima_expiracao,
    CASE
        WHEN BOOL_OR(
            revogado_em IS NULL
            AND expira_em > CURRENT_TIMESTAMP
        )
        THEN 'ATIVA'
        WHEN MAX(expira_em) > CURRENT_TIMESTAMP
        THEN 'REVOGADA'
        ELSE 'EXPIRADA'
    END AS situacao_familia,
    CASE
        WHEN MAX(expira_em) <= CURRENT_TIMESTAMP - INTERVAL '30 days'
        THEN 'ELEGIVEL_PARA_EXCLUSAO'

        ELSE 'PRESERVAR'
    END AS retencao
FROM refresh_token
GROUP BY familia
ORDER BY MAX(criado_em) DESC;
