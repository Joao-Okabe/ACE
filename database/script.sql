-- ============================================================
-- 1. PAPÉIS
-- ============================================================

CREATE TABLE papel (
    cd_papel INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_papel VARCHAR(50) NOT NULL UNIQUE
);

-- ============================================================
-- 2. USUÁRIOS
-- ============================================================

CREATE TABLE usuario (
    cd_usuario INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_usuario VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    path_ft_usuario VARCHAR,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuario_papel (
    cd_usuario INTEGER NOT NULL,
    cd_papel INTEGER NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (cd_usuario, cd_papel),
    FOREIGN KEY (cd_usuario) REFERENCES usuario(cd_usuario) ON DELETE CASCADE,
    FOREIGN KEY (cd_papel) REFERENCES papel(cd_papel) ON DELETE CASCADE
);

-- ============================================================
-- 3. ESCOLA
-- ============================================================

CREATE TABLE escola (
    cd_escola INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_escola VARCHAR(150) NOT NULL,
    telefone VARCHAR(20),
    cep VARCHAR(9),
    numero VARCHAR(20),
    email VARCHAR(150),
    categoria_administrativa VARCHAR(20) NOT NULL,
    path_brasao TEXT,
    criada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativa BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT chk_categoria CHECK (categoria_administrativa IN ('Escola Municipal', 'Escola Estadual', 'Privada'))
);

CREATE TABLE vinculo_usuario_escola (
    cd_usuario INTEGER NOT NULL,
    cd_escola INTEGER NOT NULL,
    cd_papel INTEGER NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_usuario, cd_escola, cd_papel),
    FOREIGN KEY (cd_usuario) REFERENCES usuario(cd_usuario) ON DELETE CASCADE,
    FOREIGN KEY (cd_escola) REFERENCES escola(cd_escola) ON DELETE CASCADE,
    FOREIGN KEY (cd_papel) REFERENCES papel(cd_papel) ON DELETE RESTRICT
);

-- ============================================================
-- 4. ALUNOS
-- ============================================================

CREATE TABLE aluno (
    cd_aluno INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_usuario INTEGER NOT NULL UNIQUE,
    ra VARCHAR(20) NOT NULL UNIQUE,
    data_nascimento DATE,
    sexo CHAR(1),
    telefone VARCHAR(20),
    cep VARCHAR(9),
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cd_usuario) REFERENCES usuario(cd_usuario) ON DELETE CASCADE,
    CONSTRAINT chk_sexo CHECK (sexo IN ('M', 'F', 'O') OR sexo IS NULL)
);

CREATE TABLE documentos_aluno (
    cd_documentos_aluno INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_aluno INTEGER NOT NULL,
    rg TEXT,
    comprovante_escolar TEXT,
    atestado_aptidao_fisica TEXT,
    FOREIGN KEY (cd_aluno) REFERENCES aluno(cd_aluno) ON DELETE CASCADE
);

-- ============================================================
-- 5. CONFIGURAÇÕES ESPORTIVAS
-- ============================================================

CREATE TABLE formato (
    cd_formato INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_formato VARCHAR(30) NOT NULL,
    ds_formato TEXT
);

CREATE TABLE modalidade (
    cd_modalidade INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    sexo VARCHAR(5) NOT NULL,
    categoria VARCHAR(10) NOT NULL,
    CHECK (sexo IN ('M', 'F', 'MISTO')),
    CHECK (categoria IN ('Sub14', 'Sub18', 'MISTO')),
    UNIQUE (sexo, categoria)
);

CREATE TABLE esporte (
    cd_esporte INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_esporte VARCHAR(30) NOT NULL,
    ds_esporte TEXT
);

CREATE TABLE tipo_etapa (
    cd_tipo_etapa INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_tipo_etapa VARCHAR(100) NOT NULL UNIQUE,
    ds_tipo_etapa TEXT
);

-- ============================================================
-- 6. COMPETIÇÃO
-- ============================================================

CREATE TABLE competicao (
    cd_competicao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_competicao VARCHAR(150) NOT NULL,
    cd_criador INTEGER,
    cd_esporte INTEGER NOT NULL,
    cd_modalidade INTEGER NOT NULL,
    cd_formato INTEGER NOT NULL,
    cd_escola INTEGER,
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    status VARCHAR(30) NOT NULL DEFAULT 'PLANEJADA',
    descricao TEXT,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cd_criador) REFERENCES usuario(cd_usuario) ON DELETE RESTRICT,
    FOREIGN KEY (cd_esporte) REFERENCES esporte(cd_esporte) ON DELETE RESTRICT,
    FOREIGN KEY (cd_modalidade) REFERENCES modalidade(cd_modalidade) ON DELETE RESTRICT,
    FOREIGN KEY (cd_formato) REFERENCES formato(cd_formato) ON DELETE RESTRICT,
    FOREIGN KEY (cd_escola) REFERENCES escola(cd_escola) ON DELETE SET NULL,
    CHECK (status IN ('PLANEJADA', 'INSCRICOES', 'EM_ANDAMENTO', 'FINALIZADA', 'CANCELADA'))
);

-- ============================================================
-- 7. TIMES
-- ============================================================

CREATE TABLE time (
    cd_time INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_time VARCHAR(100) NOT NULL,
    cd_esporte INTEGER NOT NULL,
    descricao TEXT,
    principal BOOLEAN NOT NULL DEFAULT FALSE,
    path_escudo VARCHAR(255),
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cd_esporte) REFERENCES esporte(cd_esporte) ON DELETE RESTRICT
);

CREATE TABLE funcao_integrante (
    cd_funcao_integrante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_esporte INTEGER NOT NULL,
    nm_funcao VARCHAR(100) NOT NULL,
    ds_funcao TEXT,
    FOREIGN KEY (cd_esporte) REFERENCES esporte(cd_esporte) ON DELETE CASCADE,
    UNIQUE (cd_esporte, nm_funcao)
);

CREATE TABLE vinculo_time_escola (
    cd_time INTEGER NOT NULL,
    cd_escola INTEGER NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_time, cd_escola),
    FOREIGN KEY (cd_time) REFERENCES time(cd_time) ON DELETE CASCADE,
    FOREIGN KEY (cd_escola) REFERENCES escola(cd_escola) ON DELETE CASCADE
);

CREATE TABLE vinculo_time_integrante (
    cd_vinculo_time_integrante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_time INTEGER NOT NULL,
    cd_usuario INTEGER NOT NULL,
    cd_funcao_integrante INTEGER,
    numero_camisa INTEGER,
    capitao BOOLEAN NOT NULL DEFAULT FALSE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (cd_time) REFERENCES time(cd_time) ON DELETE CASCADE,
    FOREIGN KEY (cd_usuario) REFERENCES usuario(cd_usuario) ON DELETE CASCADE,
    FOREIGN KEY (cd_funcao_integrante) REFERENCES funcao_integrante(cd_funcao_integrante) ON DELETE SET NULL,
    UNIQUE (cd_time, cd_usuario),
    CHECK (numero_camisa IS NULL OR numero_camisa > 0)
);

CREATE TABLE escalacao_integrante (
    cd_vinculo_time_integrante INTEGER PRIMARY KEY,
    titular BOOLEAN NOT NULL DEFAULT FALSE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (cd_vinculo_time_integrante) REFERENCES vinculo_time_integrante(cd_vinculo_time_integrante) ON DELETE CASCADE
);

CREATE TABLE vinculo_tecnico_time (
    cd_usuario INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (cd_usuario, cd_time),
    FOREIGN KEY (cd_usuario) REFERENCES usuario(cd_usuario) ON DELETE CASCADE,
    FOREIGN KEY (cd_time) REFERENCES time(cd_time) ON DELETE CASCADE
);

CREATE TABLE responsavel (
    cd_responsavel INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_usuario INTEGER NOT NULL,
    CONSTRAINT responsavel_cd_usuario_fkey
        FOREIGN KEY (cd_usuario)
        REFERENCES usuario (cd_usuario),
    CONSTRAINT responsavel_cd_usuario_unique
        UNIQUE (cd_usuario)
);

CREATE TABLE responsavel_time (
    cd_time INTEGER NOT NULL,
    cd_responsavel INTEGER NOT NULL,
    CONSTRAINT responsavel_time_pk
        PRIMARY KEY (cd_time, cd_responsavel),
    CONSTRAINT responsavel_time_cd_time_fkey
        FOREIGN KEY (cd_time)
        REFERENCES time (cd_time),
    CONSTRAINT responsavel_time_cd_responsavel_fkey
        FOREIGN KEY (cd_responsavel)
        REFERENCES responsavel (cd_responsavel)
);

-- ============================================================
-- 8. INSCRIÇÃO NA COMPETIÇÃO
-- ============================================================

CREATE TABLE inscricao_competicao (
    cd_inscricao_competicao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL,
    dt_inicio_inscricao TIMESTAMP,
    dt_encerramento_inscricao TIMESTAMP,
    FOREIGN KEY (cd_competicao) REFERENCES competicao(cd_competicao) ON DELETE CASCADE
);

CREATE TABLE vinculo_inscricao_time (
    cd_inscricao_competicao INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    inscrito_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (cd_inscricao_competicao, cd_time),
    FOREIGN KEY (cd_inscricao_competicao) REFERENCES inscricao_competicao(cd_inscricao_competicao) ON DELETE CASCADE,
    FOREIGN KEY (cd_time) REFERENCES time(cd_time) ON DELETE CASCADE
);

-- ============================================================
-- 9. ETAPAS DA COMPETIÇÃO
-- ============================================================

CREATE TABLE etapa_competicao (
    cd_etapa_competicao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL,
    cd_tipo_etapa INTEGER NOT NULL,
    nm_etapa VARCHAR(100) NOT NULL,
    ordem INTEGER NOT NULL,
    descricao TEXT,
    FOREIGN KEY (cd_competicao) REFERENCES competicao(cd_competicao) ON DELETE CASCADE,
    FOREIGN KEY (cd_tipo_etapa) REFERENCES tipo_etapa(cd_tipo_etapa) ON DELETE RESTRICT,
    UNIQUE (cd_competicao, ordem),
    UNIQUE (cd_competicao, nm_etapa),
    CHECK (ordem > 0)
);

CREATE TABLE rodada_competicao (
    cd_rodada INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_etapa_competicao INTEGER NOT NULL,
    nr_rodada INTEGER NOT NULL,
    nm_rodada VARCHAR(100),
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    status VARCHAR(30) NOT NULL DEFAULT 'PENDENTE',
    FOREIGN KEY (cd_etapa_competicao) REFERENCES etapa_competicao(cd_etapa_competicao) ON DELETE CASCADE,
    UNIQUE (cd_etapa_competicao, nr_rodada),
    CHECK (nr_rodada > 0),
    CHECK (status IN ('PENDENTE', 'EM_ANDAMENTO', 'FINALIZADA')),
    CHECK (fim_em IS NULL OR inicio_em IS NULL OR fim_em >= inicio_em)
);

-- ============================================================
-- 10. GRUPOS
-- ============================================================

CREATE TABLE grupo_competicao (
    cd_grupo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_etapa_competicao INTEGER NOT NULL,
    nm_grupo VARCHAR(100) NOT NULL,
    ordem INTEGER,
    FOREIGN KEY (cd_etapa_competicao) REFERENCES etapa_competicao(cd_etapa_competicao) ON DELETE CASCADE,
    UNIQUE (cd_etapa_competicao, nm_grupo),
    CHECK (ordem IS NULL OR ordem > 0)
);

CREATE TABLE vinculo_grupo_time (
    cd_grupo INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    PRIMARY KEY (cd_grupo, cd_time),
    FOREIGN KEY (cd_grupo) REFERENCES grupo_competicao(cd_grupo) ON DELETE CASCADE,
    FOREIGN KEY (cd_time) REFERENCES time(cd_time) ON DELETE CASCADE
);

-- ============================================================
-- 11. CLASSIFICAÇÃO
-- ============================================================

CREATE TABLE metrica_classificacao (
    cd_metrica_classificacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_metrica_classificacao VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT
);

CREATE TABLE configuracao_classificacao (
    cd_configuracao_classificacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    FOREIGN KEY (cd_competicao) REFERENCES competicao(cd_competicao) ON DELETE CASCADE
);

CREATE TABLE criterio_classificacao (
    cd_criterio_classificacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_configuracao_classificacao INTEGER NOT NULL,
    cd_metrica_classificacao INTEGER NOT NULL,
    ordem INTEGER NOT NULL,
    direcao VARCHAR(4) NOT NULL DEFAULT 'DESC',
    FOREIGN KEY (cd_configuracao_classificacao) REFERENCES configuracao_classificacao(cd_configuracao_classificacao) ON DELETE CASCADE,
    FOREIGN KEY (cd_metrica_classificacao) REFERENCES metrica_classificacao(cd_metrica_classificacao) ON DELETE RESTRICT,
    UNIQUE (cd_configuracao_classificacao, ordem),
    UNIQUE (cd_configuracao_classificacao, cd_metrica_classificacao),
    CHECK (ordem > 0),
    CHECK (direcao IN ('ASC', 'DESC'))
);

CREATE TABLE classificacao_grupo (
    cd_classificacao_grupo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_grupo INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    posicao INTEGER NOT NULL,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cd_grupo, cd_time) REFERENCES vinculo_grupo_time(cd_grupo, cd_time) ON DELETE CASCADE,
    UNIQUE (cd_grupo, cd_time),
    UNIQUE (cd_grupo, posicao),
    CHECK (posicao > 0)
);

CREATE TABLE estatistica_classificacao (
    cd_classificacao_grupo INTEGER NOT NULL,
    cd_metrica_classificacao INTEGER NOT NULL,
    valor NUMERIC(15,3) NOT NULL DEFAULT 0,
    PRIMARY KEY (cd_classificacao_grupo, cd_metrica_classificacao),
    FOREIGN KEY (cd_classificacao_grupo) REFERENCES classificacao_grupo(cd_classificacao_grupo) ON DELETE CASCADE,
    FOREIGN KEY (cd_metrica_classificacao) REFERENCES metrica_classificacao(cd_metrica_classificacao) ON DELETE RESTRICT
);

-- ============================================================
-- 12. CONFRONTOS / CHAVEAMENTO
-- ============================================================

CREATE TABLE confronto (
    cd_confronto INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_rodada INTEGER NOT NULL,
    nr_confronto INTEGER NOT NULL,
    nome VARCHAR(100),
    status VARCHAR(30) NOT NULL DEFAULT 'PENDENTE',
    cd_vencedor INTEGER,
    cd_confronto_anterior_a INTEGER,
    cd_confronto_anterior_b INTEGER,
    FOREIGN KEY (cd_rodada) REFERENCES rodada_competicao(cd_rodada) ON DELETE CASCADE,
    FOREIGN KEY (cd_vencedor) REFERENCES time(cd_time) ON DELETE SET NULL,
    FOREIGN KEY (cd_confronto_anterior_a) REFERENCES confronto(cd_confronto) ON DELETE SET NULL,
    FOREIGN KEY (cd_confronto_anterior_b) REFERENCES confronto(cd_confronto) ON DELETE SET NULL,
    UNIQUE (cd_rodada, nr_confronto),
    CHECK (nr_confronto > 0),
    CHECK (status IN ('PENDENTE', 'AGUARDANDO', 'EM_ANDAMENTO', 'FINALIZADO', 'CANCELADO')),
    CHECK (cd_confronto_anterior_a IS NULL OR cd_confronto_anterior_a <> cd_confronto),
    CHECK (cd_confronto_anterior_b IS NULL OR cd_confronto_anterior_b <> cd_confronto)
);

CREATE TABLE origem_participante_confronto (
    cd_origem_participante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tipo_origem VARCHAR(30) NOT NULL,
    cd_time INTEGER,
    cd_confronto INTEGER,
    cd_classificacao_grupo INTEGER,
    resultado_confronto VARCHAR(20),
    FOREIGN KEY (cd_time) REFERENCES time(cd_time) ON DELETE CASCADE,
    FOREIGN KEY (cd_confronto) REFERENCES confronto(cd_confronto) ON DELETE CASCADE,
    FOREIGN KEY (cd_classificacao_grupo) REFERENCES classificacao_grupo(cd_classificacao_grupo) ON DELETE CASCADE,
    CHECK (tipo_origem IN ('TIME', 'CONFRONTO', 'CLASSIFICACAO_GRUPO', 'BYE')),
    CHECK (
        (tipo_origem = 'TIME' AND cd_time IS NOT NULL AND cd_confronto IS NULL AND cd_classificacao_grupo IS NULL AND resultado_confronto IS NULL)
        OR
        (tipo_origem = 'CONFRONTO' AND cd_time IS NULL AND cd_confronto IS NOT NULL AND cd_classificacao_grupo IS NULL AND resultado_confronto IN ('VENCEDOR', 'PERDEDOR'))
        OR
        (tipo_origem = 'CLASSIFICACAO_GRUPO' AND cd_time IS NULL AND cd_confronto IS NULL AND cd_classificacao_grupo IS NOT NULL AND resultado_confronto IS NULL)
        OR
        (tipo_origem = 'BYE' AND cd_time IS NULL AND cd_confronto IS NULL AND cd_classificacao_grupo IS NULL AND resultado_confronto IS NULL)
    )
);

CREATE TABLE confronto_participante (
    cd_confronto INTEGER NOT NULL,
    posicao SMALLINT NOT NULL,
    cd_origem_participante INTEGER NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDENTE',
    PRIMARY KEY (cd_confronto, posicao),
    FOREIGN KEY (cd_confronto) REFERENCES confronto(cd_confronto) ON DELETE CASCADE,
    FOREIGN KEY (cd_origem_participante) REFERENCES origem_participante_confronto(cd_origem_participante) ON DELETE CASCADE,
    CHECK (posicao IN (1, 2)),
    CHECK (status IN ('PENDENTE', 'DEFINIDO', 'BYE', 'ELIMINADO', 'CLASSIFICADO')),
    UNIQUE (cd_confronto, cd_origem_participante)
);

-- ============================================================
-- 13. PARTIDAS
-- ============================================================

CREATE TABLE partida (
    cd_partida INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL,
    cd_confronto INTEGER,
    status VARCHAR(30) NOT NULL DEFAULT 'AGENDADA',
    inicio_agendado TIMESTAMP,
    FOREIGN KEY (cd_competicao) REFERENCES competicao(cd_competicao) ON DELETE CASCADE,
    FOREIGN KEY (cd_confronto) REFERENCES confronto(cd_confronto) ON DELETE SET NULL,
    CHECK (status IN ('AGENDADA', 'EM_ANDAMENTO', 'FINALIZADA', 'CANCELADA'))
);

CREATE TABLE info_partida (
    cd_partida INTEGER PRIMARY KEY,
    dt_partida DATE NOT NULL,
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    local_partida VARCHAR(150),
    observacoes TEXT,
    FOREIGN KEY (cd_partida) REFERENCES partida(cd_partida) ON DELETE CASCADE,
    CHECK (fim_em IS NULL OR inicio_em IS NULL OR fim_em >= inicio_em)
);

CREATE TABLE etapa_partida (
    cd_etapa_partida INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_partida INTEGER NOT NULL,
    nr_etapa INTEGER NOT NULL,
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    FOREIGN KEY (cd_partida) REFERENCES partida(cd_partida) ON DELETE CASCADE,
    UNIQUE (cd_partida, nr_etapa),
    CHECK (nr_etapa > 0),
    CHECK (fim_em IS NULL OR inicio_em IS NULL OR fim_em >= inicio_em)
);

CREATE TABLE resultado_etapa_partida (
    cd_etapa_partida INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    pontuacao INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (cd_etapa_partida, cd_time),
    FOREIGN KEY (cd_etapa_partida) REFERENCES etapa_partida(cd_etapa_partida) ON DELETE CASCADE,
    FOREIGN KEY (cd_time) REFERENCES time(cd_time) ON DELETE CASCADE,
    CHECK (pontuacao >= 0)
);

CREATE TABLE vinculo_partida_time (
    cd_partida INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    mandante BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (cd_partida, cd_time),
    FOREIGN KEY (cd_partida) REFERENCES partida(cd_partida) ON DELETE CASCADE,
    FOREIGN KEY (cd_time) REFERENCES time(cd_time) ON DELETE CASCADE
);

-- ============================================================
-- 14. PENALIDADES
-- ============================================================

CREATE TABLE tipo_penalidade (
    cd_tipo_penalidade INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_tipo_penalidade VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT
);

CREATE TABLE penalidade (
    cd_penalidade INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    cd_usuario INTEGER NOT NULL,
    cd_funcao_integrante INTEGER NOT NULL,
    cd_tipo_penalidade INTEGER NOT NULL,
    descricao TEXT,
    valor NUMERIC(10,3),
    aplicado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (cd_competicao) REFERENCES competicao(cd_competicao) ON DELETE CASCADE,
    FOREIGN KEY (cd_time, cd_usuario) REFERENCES vinculo_time_integrante(cd_time, cd_usuario) ON DELETE CASCADE,
    FOREIGN KEY (cd_funcao_integrante) REFERENCES funcao_integrante(cd_funcao_integrante) ON DELETE RESTRICT,
    FOREIGN KEY (cd_tipo_penalidade) REFERENCES tipo_penalidade(cd_tipo_penalidade) ON DELETE RESTRICT
);

-- ============================================================
-- 15. ÍNDICES
-- ============================================================

CREATE INDEX idx_confronto_rodada ON confronto(cd_rodada);
CREATE INDEX idx_confronto_anterior_a ON confronto(cd_confronto_anterior_a);
CREATE INDEX idx_confronto_anterior_b ON confronto(cd_confronto_anterior_b);
CREATE INDEX idx_origem_confronto ON origem_participante_confronto(cd_confronto);
CREATE INDEX idx_origem_classificacao ON origem_participante_confronto(cd_classificacao_grupo);
CREATE INDEX idx_confronto_participante_origem ON confronto_participante(cd_origem_participante);
CREATE INDEX idx_partida_confronto ON partida(cd_confronto);