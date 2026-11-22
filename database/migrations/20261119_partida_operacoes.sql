BEGIN;

CREATE TABLE IF NOT EXISTS pontuacao_partida (
    cd_pontuacao_partida INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_partida INTEGER NOT NULL REFERENCES partida ON DELETE CASCADE,
    cd_time INTEGER NOT NULL,
    cd_usuario INTEGER REFERENCES aluno (cd_usuario) ON DELETE SET NULL,
    pontos INTEGER NOT NULL CHECK (pontos > 0),
    minuto INTEGER CHECK (minuto IS NULL OR minuto >= 0),
    observacao TEXT,
    cd_usuario_registrador INTEGER NOT NULL REFERENCES usuario ON DELETE RESTRICT,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cd_partida, cd_time)
        REFERENCES vinculo_partida_time (cd_partida, cd_time) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_pontuacao_partida_eventos
    ON pontuacao_partida (cd_partida, criado_em DESC);

CREATE TABLE IF NOT EXISTS arbitro_partida (
    cd_partida INTEGER NOT NULL REFERENCES partida ON DELETE CASCADE,
    cd_usuario INTEGER NOT NULL REFERENCES usuario ON DELETE RESTRICT,
    funcao VARCHAR(20) NOT NULL
        CHECK (funcao IN ('PRINCIPAL', 'ASSISTENTE', 'MESARIO')),
    cd_usuario_vinculador INTEGER NOT NULL REFERENCES usuario ON DELETE RESTRICT,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_partida, cd_usuario)
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_arbitro_partida_principal
    ON arbitro_partida (cd_partida)
    WHERE funcao = 'PRINCIPAL';

CREATE INDEX IF NOT EXISTS idx_arbitro_partida_usuario
    ON arbitro_partida (cd_usuario, cd_partida);

<<<<<<< HEAD
ALTER TABLE penalidade
    ALTER COLUMN cd_competicao DROP NOT NULL;

ALTER TABLE penalidade
    ADD COLUMN IF NOT EXISTS cd_partida INTEGER
        REFERENCES partida ON DELETE CASCADE;

ALTER TABLE penalidade
    ADD COLUMN IF NOT EXISTS minuto INTEGER
        CHECK (minuto IS NULL OR minuto >= 0);

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
=======

-- Atualiza a estrutura da tabela de penalidades.
ALTER TABLE penalidade
    ALTER COLUMN cd_competicao DROP NOT NULL,
    ADD COLUMN IF NOT EXISTS cd_time INTEGER,
    ADD COLUMN IF NOT EXISTS cd_usuario INTEGER,
    ADD COLUMN IF NOT EXISTS cd_funcao_integrante INTEGER,
    ADD COLUMN IF NOT EXISTS cd_partida INTEGER
        REFERENCES partida ON DELETE CASCADE,
    ADD COLUMN IF NOT EXISTS minuto INTEGER
        CHECK (minuto IS NULL OR minuto >= 0);

-- Recupera os dados da estrutura antiga, caso existam.
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = 'public'
          AND table_name = 'penalidade'
          AND column_name = 'cd_vinculo_time_integrante'
    ) THEN
        EXECUTE '
            UPDATE penalidade p
            SET
                cd_time = vti.cd_time,
                cd_usuario = vti.cd_usuario,
                cd_funcao_integrante = vti.cd_funcao_integrante
            FROM vinculo_time_integrante vti
            WHERE p.cd_vinculo_time_integrante =
                  vti.cd_vinculo_time_integrante
              AND (
                  p.cd_time IS NULL
                  OR p.cd_usuario IS NULL
                  OR p.cd_funcao_integrante IS NULL
              )
        ';
    END IF;
END
$$;

-- Verifica se todos os registros podem utilizar a nova estrutura.
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM penalidade
        WHERE cd_time IS NULL
           OR cd_usuario IS NULL
           OR cd_funcao_integrante IS NULL
    ) THEN
        RAISE EXCEPTION
            'Existem penalidades sem dados suficientes para a migração.';
    END IF;
END
$$;

-- Aplica as obrigatoriedades do esquema atual.
ALTER TABLE penalidade
    ALTER COLUMN cd_time SET NOT NULL,
    ALTER COLUMN cd_usuario SET NOT NULL,
    ALTER COLUMN cd_funcao_integrante SET NOT NULL;

-- Remove o vínculo antigo.
ALTER TABLE penalidade
    DROP COLUMN IF EXISTS cd_vinculo_time_integrante;

-- Adiciona os relacionamentos do esquema atual.
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'penalidade_cd_funcao_integrante_fkey'
          AND conrelid = 'public.penalidade'::regclass
    ) THEN
        ALTER TABLE penalidade
            ADD CONSTRAINT penalidade_cd_funcao_integrante_fkey
            FOREIGN KEY (cd_funcao_integrante)
            REFERENCES funcao_integrante (cd_funcao_integrante)
            ON DELETE RESTRICT;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'penalidade_cd_time_cd_usuario_fkey'
          AND conrelid = 'public.penalidade'::regclass
    ) THEN
        ALTER TABLE penalidade
            ADD CONSTRAINT penalidade_cd_time_cd_usuario_fkey
            FOREIGN KEY (cd_time, cd_usuario)
            REFERENCES vinculo_time_integrante (cd_time, cd_usuario)
            ON DELETE RESTRICT;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
>>>>>>> origin/api_v1
        WHERE conname = 'penalidade_partida_ou_competicao_check'
          AND conrelid = 'public.penalidade'::regclass
    ) THEN
        ALTER TABLE penalidade
            ADD CONSTRAINT penalidade_partida_ou_competicao_check
<<<<<<< HEAD
            CHECK (cd_competicao IS NOT NULL OR cd_partida IS NOT NULL);
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
=======
            CHECK (
                cd_competicao IS NOT NULL
                OR cd_partida IS NOT NULL
            );
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
>>>>>>> origin/api_v1
        WHERE conname = 'penalidade_partida_time_fkey'
          AND conrelid = 'public.penalidade'::regclass
    ) THEN
        ALTER TABLE penalidade
            ADD CONSTRAINT penalidade_partida_time_fkey
            FOREIGN KEY (cd_partida, cd_time)
            REFERENCES vinculo_partida_time (cd_partida, cd_time)
            ON DELETE CASCADE
            NOT VALID;
    END IF;
END
$$;

CREATE INDEX IF NOT EXISTS idx_penalidade_partida_eventos
    ON penalidade (cd_partida, aplicado_em DESC);

<<<<<<< HEAD
=======
-- Remove coluna legada que não existe mais no esquema atual.
ALTER TABLE tipo_penalidade
    DROP COLUMN IF EXISTS origem_penalidade;

>>>>>>> origin/api_v1
INSERT INTO tipo_penalidade (nm_tipo_penalidade, descricao)
VALUES
    ('FALTA', 'Falta registrada durante a partida.'),
    ('CARTAO_AMARELO', 'Cartão amarelo aplicado durante a partida.'),
    ('CARTAO_VERMELHO', 'Cartão vermelho aplicado durante a partida.'),
    ('PENALIDADE', 'Penalidade registrada durante a partida.')
ON CONFLICT (nm_tipo_penalidade) DO NOTHING;

COMMIT;
