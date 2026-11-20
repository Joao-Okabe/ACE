BEGIN;

CREATE TABLE IF NOT EXISTS importacao_csv (
    id UUID PRIMARY KEY,
    cd_usuario INTEGER NOT NULL REFERENCES usuario(cd_usuario) ON DELETE CASCADE,
    tipo VARCHAR(10) NOT NULL CHECK (tipo IN ('alunos', 'escolas')),
    cd_escola INTEGER REFERENCES escola(cd_escola) ON DELETE CASCADE,
    dados JSONB NOT NULL,
    status VARCHAR(10) NOT NULL DEFAULT 'PENDENTE'
        CHECK (status IN ('PENDENTE', 'CONCLUIDA')),
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expira_em TIMESTAMP NOT NULL,
    finalizada_em TIMESTAMP,
    CHECK (
        (tipo = 'alunos' AND cd_escola IS NOT NULL)
        OR (tipo = 'escolas' AND cd_escola IS NULL)
    )
);

CREATE INDEX IF NOT EXISTS idx_importacao_csv_usuario_expira
    ON importacao_csv (cd_usuario, expira_em);

COMMIT;
