BEGIN;

-- Evita alterar a estrutura se já existirem partidas cadastradas.
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM partida) THEN
        RAISE EXCEPTION
            'A tabela partida possui registros. A migration precisa ser adaptada.';
    END IF;
END
$$;

-- Atualiza a estrutura da partida.
ALTER TABLE partida
    ALTER COLUMN cd_competicao DROP NOT NULL,
    ADD COLUMN cd_criador INTEGER NOT NULL,
    ADD COLUMN cd_esporte INTEGER,
    ADD COLUMN cd_modalidade INTEGER,
    ADD COLUMN visibilidade VARCHAR(10) NOT NULL DEFAULT 'PUBLICA',
    ADD COLUMN motivo_cancelamento TEXT;

-- Relacionamentos.
ALTER TABLE partida
    ADD CONSTRAINT partida_cd_criador_fkey
        FOREIGN KEY (cd_criador)
        REFERENCES usuario(cd_usuario)
        ON DELETE RESTRICT,
    ADD CONSTRAINT partida_cd_esporte_fkey
        FOREIGN KEY (cd_esporte)
        REFERENCES esporte(cd_esporte)
        ON DELETE RESTRICT,
    ADD CONSTRAINT partida_cd_modalidade_fkey
        FOREIGN KEY (cd_modalidade)
        REFERENCES modalidade(cd_modalidade)
        ON DELETE RESTRICT;

-- Regras de visibilidade.
ALTER TABLE partida
    ADD CONSTRAINT partida_visibilidade_check
        CHECK (visibilidade IN ('PUBLICA', 'PRIVADA'));

-- Regras para partidas de competição e partidas avulsas.
ALTER TABLE partida
    ADD CONSTRAINT partida_origem_check
        CHECK (
            (
                cd_competicao IS NULL
                AND cd_esporte IS NOT NULL
                AND cd_modalidade IS NOT NULL
            )
            OR
            (
                cd_competicao IS NOT NULL
                AND cd_esporte IS NULL
                AND cd_modalidade IS NULL
            )
        ),
    ADD CONSTRAINT partida_confronto_competicao_check
        CHECK (
            cd_confronto IS NULL
            OR cd_competicao IS NOT NULL
        );

COMMIT;
