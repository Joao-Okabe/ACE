BEGIN;

-- Adicionar a coluna de ordenação dos grupos
ALTER TABLE grupo_competicao
ADD COLUMN IF NOT EXISTS ordem INTEGER;

-- Remover a referência à chave antiga
ALTER TABLE classificacao_grupo
DROP CONSTRAINT classificacao_grupo_cd_vinculo_grupo_time_fkey;

ALTER TABLE classificacao_grupo
DROP CONSTRAINT classificacao_grupo_cd_vinculo_grupo_time_key;

-- Alterar a chave primária dos vínculos
ALTER TABLE vinculo_grupo_time
DROP CONSTRAINT vinculo_grupo_time_pkey;

ALTER TABLE vinculo_grupo_time
ADD CONSTRAINT vinculo_grupo_time_pkey
PRIMARY KEY (cd_grupo, cd_time);

-- Atualizar a classificação
ALTER TABLE classificacao_grupo
DROP COLUMN cd_vinculo_grupo_time;

ALTER TABLE classificacao_grupo
ADD COLUMN cd_time INTEGER NOT NULL;

ALTER TABLE classificacao_grupo
ADD CONSTRAINT classificacao_grupo_cd_grupo_cd_time_fkey
FOREIGN KEY (cd_grupo, cd_time)
REFERENCES vinculo_grupo_time (cd_grupo, cd_time)
ON DELETE CASCADE;

ALTER TABLE classificacao_grupo
ADD CONSTRAINT classificacao_grupo_cd_grupo_cd_time_key
UNIQUE (cd_grupo, cd_time);

-- Remover o identificador antigo do vínculo
ALTER TABLE vinculo_grupo_time
DROP COLUMN cd_vinculo_grupo_time;

COMMIT;
