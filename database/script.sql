-- ============================================================
-- ACE - SCRIPT COMPLETO DO BANCO DE DADOS
-- PostgreSQL
-- Schema destinado à criação de um banco novo.
-- ============================================================

BEGIN;

-- ============================================================
-- USUÁRIOS, PAPÉIS E ESCOLAS
-- ============================================================

CREATE TABLE papel (
    cd_papel INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_papel VARCHAR(50) NOT NULL UNIQUE
);

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
    cd_usuario INTEGER NOT NULL REFERENCES usuario ON DELETE CASCADE,
    cd_papel INTEGER NOT NULL REFERENCES papel ON DELETE CASCADE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (cd_usuario, cd_papel)
);

CREATE TABLE escola (
    cd_escola INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_escola VARCHAR(150) NOT NULL,
    telefone VARCHAR(20),
    cep VARCHAR(9),
    numero VARCHAR(20),
    email VARCHAR(150),
    categoria_administrativa VARCHAR(30) NOT NULL
        CHECK (categoria_administrativa IN (
            'Escola Municipal',
            'Escola Estadual',
            'Privada'
        )),
    path_brasao TEXT,
    criada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativa BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE vinculo_usuario_escola (
    cd_usuario INTEGER NOT NULL REFERENCES usuario ON DELETE CASCADE,
    cd_escola INTEGER NOT NULL REFERENCES escola ON DELETE CASCADE,
    cd_papel INTEGER NOT NULL REFERENCES papel ON DELETE RESTRICT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_usuario, cd_escola, cd_papel)
);

CREATE TABLE aluno (
    cd_aluno INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_usuario INTEGER NOT NULL UNIQUE REFERENCES usuario ON DELETE CASCADE,
    ra VARCHAR(20) NOT NULL UNIQUE,
    data_nascimento DATE,
    sexo CHAR(1) CHECK (sexo IN ('M', 'F', 'O') OR sexo IS NULL),
    telefone VARCHAR(20),
    cep VARCHAR(9),
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE documentos_aluno (
    cd_documentos_aluno INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_aluno INTEGER NOT NULL REFERENCES aluno ON DELETE CASCADE,
    rg TEXT,
    comprovante_escolar TEXT,
    atestado_aptidao_fisica TEXT
);

-- ============================================================
-- ESPORTES, MODALIDADES E FORMATOS
-- ============================================================

CREATE TABLE formato (
    cd_formato INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_formato VARCHAR(30) NOT NULL,
    ds_formato TEXT
);

CREATE TABLE modalidade (
    cd_modalidade INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    sexo VARCHAR(5) NOT NULL
        CHECK (sexo IN ('M', 'F', 'MISTO')),
    categoria VARCHAR(10) NOT NULL
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
-- COMPETIÇÕES
-- ============================================================

CREATE TABLE competicao (
    cd_competicao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_competicao VARCHAR(150) NOT NULL,
    cd_criador INTEGER NOT NULL REFERENCES usuario ON DELETE RESTRICT,
    cd_esporte INTEGER NOT NULL REFERENCES esporte ON DELETE RESTRICT,
    cd_modalidade INTEGER NOT NULL REFERENCES modalidade ON DELETE RESTRICT,
    cd_formato INTEGER NOT NULL REFERENCES formato ON DELETE RESTRICT,
    cd_escola INTEGER REFERENCES escola ON DELETE SET NULL,
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    status VARCHAR(30) NOT NULL DEFAULT 'PLANEJADA'
        CHECK (status IN (
            'PLANEJADA',
            'INSCRICOES',
            'EM_ANDAMENTO',
            'FINALIZADA',
            'CANCELADA'
        )),
    descricao TEXT,
    visibilidade VARCHAR(10) NOT NULL DEFAULT 'PUBLICA'
        CHECK (visibilidade IN ('PUBLICA', 'PRIVADA')),
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CHECK (
        fim_em IS NULL
        OR inicio_em IS NULL
        OR fim_em >= inicio_em
    )
);

-- ============================================================
-- TIMES E VÍNCULOS
-- ============================================================

CREATE TABLE time (
    cd_time INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_time VARCHAR(100) NOT NULL,
    cd_esporte INTEGER NOT NULL REFERENCES esporte ON DELETE RESTRICT,
    descricao TEXT,
    principal BOOLEAN NOT NULL DEFAULT FALSE,
    path_escudo VARCHAR(255),
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE funcao_integrante (
    cd_funcao_integrante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_esporte INTEGER NOT NULL REFERENCES esporte ON DELETE CASCADE,
    nm_funcao VARCHAR(100) NOT NULL,
    ds_funcao TEXT,
    UNIQUE (cd_esporte, nm_funcao)
);

CREATE TABLE vinculo_time_escola (
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE CASCADE,
    cd_escola INTEGER NOT NULL REFERENCES escola ON DELETE CASCADE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_time, cd_escola)
);

CREATE TABLE vinculo_time_integrante (
    cd_vinculo_time_integrante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE RESTRICT,
    cd_usuario INTEGER NOT NULL REFERENCES usuario ON DELETE RESTRICT,
    cd_funcao_integrante INTEGER REFERENCES funcao_integrante ON DELETE SET NULL,
    numero_camisa INTEGER CHECK (
        numero_camisa IS NULL OR numero_camisa > 0
    ),
    capitao BOOLEAN NOT NULL DEFAULT FALSE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE (cd_time, cd_usuario)
);

CREATE TABLE escalacao_time (
    cd_vinculo_time_integrante INTEGER PRIMARY KEY
        REFERENCES vinculo_time_integrante ON DELETE CASCADE,
    titular BOOLEAN NOT NULL DEFAULT FALSE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE vinculo_tecnico_time (
    cd_usuario INTEGER NOT NULL REFERENCES usuario ON DELETE RESTRICT,
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE RESTRICT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (cd_usuario, cd_time)
);

CREATE TABLE responsavel (
    cd_responsavel INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_usuario INTEGER NOT NULL UNIQUE REFERENCES usuario ON DELETE RESTRICT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE vinculo_time_responsavel (
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE RESTRICT,
    cd_responsavel INTEGER NOT NULL REFERENCES responsavel ON DELETE RESTRICT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (cd_time, cd_responsavel)
);

-- ============================================================
-- INSCRIÇÕES E PARTICIPANTES DE COMPETIÇÃO
-- ============================================================

CREATE TABLE inscricao_competicao (
    cd_inscricao_competicao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL REFERENCES competicao ON DELETE CASCADE,
    dt_inicio_inscricao TIMESTAMP,
    dt_encerramento_inscricao TIMESTAMP,

    CHECK (
        dt_encerramento_inscricao IS NULL
        OR dt_inicio_inscricao IS NULL
        OR dt_encerramento_inscricao >= dt_inicio_inscricao
    )
);

CREATE TABLE vinculo_inscricao_time (
    cd_inscricao_competicao INTEGER NOT NULL
        REFERENCES inscricao_competicao ON DELETE CASCADE,
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE RESTRICT,
    inscrito_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (cd_inscricao_competicao, cd_time)
);

-- Snapshot dos participantes no momento da inscrição.
CREATE TABLE participante_competicao (
    cd_competicao INTEGER NOT NULL REFERENCES competicao ON DELETE CASCADE,
    cd_usuario INTEGER NOT NULL REFERENCES usuario ON DELETE RESTRICT,
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE RESTRICT,
    origem VARCHAR(20) NOT NULL
        CHECK (origem IN ('INTEGRANTE', 'TECNICO', 'RESPONSAVEL')),
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_competicao, cd_usuario, cd_time, origem)
);

-- ============================================================
-- ETAPAS, RODADAS E GRUPOS
-- ============================================================

CREATE TABLE etapa_competicao (
    cd_etapa_competicao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL REFERENCES competicao ON DELETE CASCADE,
    cd_tipo_etapa INTEGER NOT NULL REFERENCES tipo_etapa ON DELETE RESTRICT,
    nm_etapa VARCHAR(100) NOT NULL,
    ordem INTEGER NOT NULL CHECK (ordem > 0),
    descricao TEXT,
    UNIQUE (cd_competicao, ordem),
    UNIQUE (cd_competicao, nm_etapa)
);

CREATE TABLE rodada_competicao (
    cd_rodada INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_etapa_competicao INTEGER NOT NULL
        REFERENCES etapa_competicao ON DELETE CASCADE,
    nr_rodada INTEGER NOT NULL CHECK (nr_rodada > 0),
    nm_rodada VARCHAR(100),
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    status VARCHAR(30) NOT NULL DEFAULT 'PENDENTE'
        CHECK (status IN ('PENDENTE', 'EM_ANDAMENTO', 'FINALIZADA')),
    UNIQUE (cd_etapa_competicao, nr_rodada),

    CHECK (
        fim_em IS NULL
        OR inicio_em IS NULL
        OR fim_em >= inicio_em
    )
);

CREATE TABLE grupo_competicao (
    cd_grupo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_etapa_competicao INTEGER NOT NULL
        REFERENCES etapa_competicao ON DELETE CASCADE,
    nm_grupo VARCHAR(100) NOT NULL,
    ordem INTEGER CHECK (ordem IS NULL OR ordem > 0),
    UNIQUE (cd_etapa_competicao, nm_grupo)
);

CREATE TABLE vinculo_grupo_time (
    cd_grupo INTEGER NOT NULL REFERENCES grupo_competicao ON DELETE CASCADE,
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE RESTRICT,
    PRIMARY KEY (cd_grupo, cd_time)
);

-- ============================================================
-- CLASSIFICAÇÃO
-- ============================================================

CREATE TABLE metrica_classificacao (
    cd_metrica_classificacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_metrica_classificacao VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT
);

CREATE TABLE configuracao_classificacao (
    cd_configuracao_classificacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL UNIQUE
        REFERENCES competicao ON DELETE CASCADE,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT
);

CREATE TABLE criterio_classificacao (
    cd_criterio_classificacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_configuracao_classificacao INTEGER NOT NULL
        REFERENCES configuracao_classificacao ON DELETE CASCADE,
    cd_metrica_classificacao INTEGER NOT NULL
        REFERENCES metrica_classificacao ON DELETE RESTRICT,
    ordem INTEGER NOT NULL CHECK (ordem > 0),
    direcao VARCHAR(4) NOT NULL DEFAULT 'DESC'
        CHECK (direcao IN ('ASC', 'DESC')),
    UNIQUE (cd_configuracao_classificacao, ordem),
    UNIQUE (cd_configuracao_classificacao, cd_metrica_classificacao)
);

CREATE TABLE classificacao_grupo (
    cd_classificacao_grupo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_grupo INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    posicao INTEGER NOT NULL CHECK (posicao > 0),
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cd_grupo, cd_time)
        REFERENCES vinculo_grupo_time (cd_grupo, cd_time)
        ON DELETE CASCADE,

    UNIQUE (cd_grupo, cd_time),
    UNIQUE (cd_grupo, posicao)
);

CREATE TABLE estatistica_classificacao (
    cd_classificacao_grupo INTEGER NOT NULL
        REFERENCES classificacao_grupo ON DELETE CASCADE,
    cd_metrica_classificacao INTEGER NOT NULL
        REFERENCES metrica_classificacao ON DELETE RESTRICT,
    valor NUMERIC(15,3) NOT NULL DEFAULT 0,
    PRIMARY KEY (cd_classificacao_grupo, cd_metrica_classificacao)
);

-- ============================================================
-- CONFRONTOS E ORIGEM DOS PARTICIPANTES
-- ============================================================

CREATE TABLE confronto (
    cd_confronto INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_rodada INTEGER NOT NULL REFERENCES rodada_competicao ON DELETE CASCADE,
    nr_confronto INTEGER NOT NULL CHECK (nr_confronto > 0),
    nome VARCHAR(100),
    status VARCHAR(30) NOT NULL DEFAULT 'PENDENTE'
        CHECK (status IN (
            'PENDENTE',
            'AGUARDANDO',
            'EM_ANDAMENTO',
            'FINALIZADO',
            'CANCELADO'
        )),
    cd_vencedor INTEGER REFERENCES time ON DELETE SET NULL,
    cd_confronto_anterior_a INTEGER REFERENCES confronto ON DELETE SET NULL,
    cd_confronto_anterior_b INTEGER REFERENCES confronto ON DELETE SET NULL,

    UNIQUE (cd_rodada, nr_confronto),

    CHECK (
        cd_confronto_anterior_a IS NULL
        OR cd_confronto_anterior_a <> cd_confronto
    ),

    CHECK (
        cd_confronto_anterior_b IS NULL
        OR cd_confronto_anterior_b <> cd_confronto
    )
);

CREATE TABLE origem_participante_confronto (
    cd_origem_participante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tipo_origem VARCHAR(30) NOT NULL
        CHECK (tipo_origem IN (
            'TIME',
            'CONFRONTO',
            'CLASSIFICACAO_GRUPO',
            'BYE'
        )),
    cd_time INTEGER REFERENCES time ON DELETE CASCADE,
    cd_confronto INTEGER REFERENCES confronto ON DELETE CASCADE,
    cd_classificacao_grupo INTEGER
        REFERENCES classificacao_grupo ON DELETE CASCADE,
    resultado_confronto VARCHAR(20),

    CHECK (
        (
            tipo_origem = 'TIME'
            AND cd_time IS NOT NULL
            AND cd_confronto IS NULL
            AND cd_classificacao_grupo IS NULL
            AND resultado_confronto IS NULL
        )
        OR
        (
            tipo_origem = 'CONFRONTO'
            AND cd_time IS NULL
            AND cd_confronto IS NOT NULL
            AND cd_classificacao_grupo IS NULL
            AND resultado_confronto IN ('VENCEDOR', 'PERDEDOR')
        )
        OR
        (
            tipo_origem = 'CLASSIFICACAO_GRUPO'
            AND cd_time IS NULL
            AND cd_confronto IS NULL
            AND cd_classificacao_grupo IS NOT NULL
            AND resultado_confronto IS NULL
        )
        OR
        (
            tipo_origem = 'BYE'
            AND cd_time IS NULL
            AND cd_confronto IS NULL
            AND cd_classificacao_grupo IS NULL
            AND resultado_confronto IS NULL
        )
    )
);

CREATE TABLE confronto_participante (
    cd_confronto INTEGER NOT NULL REFERENCES confronto ON DELETE CASCADE,
    posicao SMALLINT NOT NULL CHECK (posicao IN (1, 2)),
    cd_origem_participante INTEGER NOT NULL
        REFERENCES origem_participante_confronto ON DELETE CASCADE,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDENTE'
        CHECK (status IN (
            'PENDENTE',
            'DEFINIDO',
            'BYE',
            'ELIMINADO',
            'CLASSIFICADO'
        )),
    PRIMARY KEY (cd_confronto, posicao),
    UNIQUE (cd_confronto, cd_origem_participante)
);

-- ============================================================
-- PARTIDAS
-- ============================================================

CREATE TABLE partida (
    cd_partida INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER REFERENCES competicao ON DELETE CASCADE,
    cd_confronto INTEGER REFERENCES confronto ON DELETE SET NULL,
    cd_criador INTEGER NOT NULL REFERENCES usuario ON DELETE RESTRICT,
    cd_esporte INTEGER REFERENCES esporte ON DELETE RESTRICT,
    cd_modalidade INTEGER REFERENCES modalidade ON DELETE RESTRICT,
    visibilidade VARCHAR(10) NOT NULL DEFAULT 'PUBLICA'
        CHECK (visibilidade IN ('PUBLICA', 'PRIVADA')),
    status VARCHAR(30) NOT NULL DEFAULT 'AGENDADA'
        CHECK (status IN (
            'AGENDADA',
            'EM_ANDAMENTO',
            'FINALIZADA',
            'CANCELADA'
        )),
    inicio_agendado TIMESTAMP,
    motivo_cancelamento TEXT,

    CHECK (
        cd_confronto IS NULL
        OR cd_competicao IS NOT NULL
    ),

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
    )
);

-- Informações de realização da partida.
-- O horário agendado permanece em partida.inicio_agendado.
-- Os horários reais ficam separados nesta tabela.
CREATE TABLE info_partida (
    cd_partida INTEGER PRIMARY KEY REFERENCES partida ON DELETE CASCADE,
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    local_partida VARCHAR(150),
    observacoes TEXT,

    CHECK (
        fim_em IS NULL
        OR inicio_em IS NULL
        OR fim_em >= inicio_em
    )
);

CREATE TABLE etapa_partida (
    cd_etapa_partida INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_partida INTEGER NOT NULL REFERENCES partida ON DELETE CASCADE,
    nr_etapa INTEGER NOT NULL CHECK (nr_etapa > 0),
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    UNIQUE (cd_partida, nr_etapa),

    CHECK (
        fim_em IS NULL
        OR inicio_em IS NULL
        OR fim_em >= inicio_em
    )
);

CREATE TABLE resultado_etapa_partida (
    cd_etapa_partida INTEGER NOT NULL
        REFERENCES etapa_partida ON DELETE CASCADE,
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE RESTRICT,
    pontuacao INTEGER NOT NULL DEFAULT 0 CHECK (pontuacao >= 0),
    PRIMARY KEY (cd_etapa_partida, cd_time)
);

CREATE TABLE vinculo_partida_time (
    cd_partida INTEGER NOT NULL REFERENCES partida ON DELETE CASCADE,
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE RESTRICT,
    mandante BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (cd_partida, cd_time)
);

-- Snapshot dos participantes no momento da partida.
-- Alterações futuras nos vínculos dos times não modificam este histórico.
CREATE TABLE participante_partida (
    cd_partida INTEGER NOT NULL REFERENCES partida ON DELETE CASCADE,
    cd_usuario INTEGER NOT NULL REFERENCES usuario ON DELETE RESTRICT,
    cd_time INTEGER NOT NULL REFERENCES time ON DELETE RESTRICT,
    origem VARCHAR(20) NOT NULL
        CHECK (origem IN ('INTEGRANTE', 'TECNICO', 'RESPONSAVEL')),
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_partida, cd_usuario, cd_time, origem)
);

-- ============================================================
-- PENALIDADES E NOTIFICAÇÕES
-- ============================================================

CREATE TABLE tipo_penalidade (
    cd_tipo_penalidade INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_tipo_penalidade VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT
);

CREATE TABLE penalidade (
    cd_penalidade INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL REFERENCES competicao ON DELETE CASCADE,
    cd_time INTEGER NOT NULL,
    cd_usuario INTEGER NOT NULL,
    cd_funcao_integrante INTEGER NOT NULL
        REFERENCES funcao_integrante ON DELETE RESTRICT,
    cd_tipo_penalidade INTEGER NOT NULL
        REFERENCES tipo_penalidade ON DELETE RESTRICT,
    descricao TEXT,
    valor NUMERIC(10,3),
    aplicado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,

    FOREIGN KEY (cd_time, cd_usuario)
        REFERENCES vinculo_time_integrante (cd_time, cd_usuario)
        ON DELETE RESTRICT
);

CREATE TABLE tipo_notificacao (
    cd_tipo_notificacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_tipo_notificacao VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT
);

