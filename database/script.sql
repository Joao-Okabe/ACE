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
    PRIMARY KEY (
        cd_usuario,
        cd_papel
    ),
    FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_papel)
        REFERENCES papel(cd_papel)
        ON DELETE CASCADE
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
    CONSTRAINT chk_categoria
        CHECK (
            categoria_administrativa IN (
                'Escola Municipal',
                'Escola Estadual',
                'Privada'
            )
        )
);

CREATE TABLE vinculo_usuario_escola (
    cd_usuario INTEGER NOT NULL,
    cd_escola INTEGER NOT NULL,
    cd_papel INTEGER NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (
        cd_usuario,
        cd_escola,
        cd_papel
    ),
    FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_escola)
        REFERENCES escola(cd_escola)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_papel)
        REFERENCES papel(cd_papel)
        ON DELETE RESTRICT
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
    CONSTRAINT fk_aluno_usuario
        FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE,
    CONSTRAINT chk_sexo
        CHECK (
            sexo IN ('M', 'F', 'O')
            OR sexo IS NULL
        )
);

CREATE TABLE documentos_aluno (
    cd_documentos_aluno INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_aluno INTEGER NOT NULL,
    rg TEXT,
    comprovante_escolar TEXT,
    atestado_aptidao_fisica TEXT,
    CONSTRAINT fk_cd_aluno
        FOREIGN KEY (cd_aluno)
        REFERENCES aluno(cd_aluno)
        ON DELETE CASCADE
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
    CHECK (
        sexo IN (
            'M',
            'F',
            'MISTO'
        )
    ),
    CHECK (
        categoria IN (
            'Sub14',
            'Sub18',
            'MISTO'
        )
    ),
    UNIQUE (
        sexo,
        categoria
    )
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
    FOREIGN KEY (cd_criador)
        REFERENCES usuario(cd_usuario)
        ON DELETE RESTRICT,
    FOREIGN KEY (cd_esporte)
        REFERENCES esporte(cd_esporte)
        ON DELETE RESTRICT,
    FOREIGN KEY (cd_modalidade)
        REFERENCES modalidade(cd_modalidade)
        ON DELETE RESTRICT,
    FOREIGN KEY (cd_formato)
        REFERENCES formato(cd_formato)
        ON DELETE RESTRICT,
    FOREIGN KEY (cd_escola)
        REFERENCES escola(cd_escola)
        ON DELETE SET NULL,
    CHECK (
        status IN (
            'PLANEJADA',
            'INSCRICOES',
            'EM_ANDAMENTO',
            'FINALIZADA',
            'CANCELADA'
        )
    )
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
    FOREIGN KEY (cd_esporte)
        REFERENCES esporte(cd_esporte)
        ON DELETE RESTRICT
);

CREATE TABLE funcao_integrante (
    cd_funcao_integrante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_esporte INTEGER NOT NULL,
    nm_funcao VARCHAR(100) NOT NULL,
    ds_funcao TEXT,
    FOREIGN KEY (cd_esporte)
        REFERENCES esporte(cd_esporte)
        ON DELETE CASCADE,
    UNIQUE (
        cd_esporte,
        nm_funcao
    )
);

CREATE TABLE vinculo_time_escola (
    cd_time INTEGER NOT NULL,
    cd_escola INTEGER NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (
        cd_time,
        cd_escola
    ),
    FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_escola)
        REFERENCES escola(cd_escola)
        ON DELETE CASCADE
);

CREATE TABLE vinculo_time_integrante (
    cd_vinculo_time_integrante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_time INTEGER NOT NULL,
    cd_usuario INTEGER NOT NULL,
    cd_funcao_integrante INTEGER,
    numero_camisa INTEGER,
    capitao BOOLEAN NOT NULL DEFAULT FALSE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_funcao_integrante)
        REFERENCES funcao_integrante(cd_funcao_integrante)
        ON DELETE SET NULL,
    UNIQUE (
        cd_time,
        cd_usuario
    ),
    CHECK (
        numero_camisa IS NULL
        OR numero_camisa > 0
    )
);

-- ============================================================
-- 8. RESPONSÁVEIS
-- ============================================================
CREATE TABLE responsavel (
    cd_responsavel INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_usuario INTEGER NOT NULL UNIQUE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE
);

CREATE TABLE vinculo_time_responsavel (
    cd_time INTEGER NOT NULL,
    cd_responsavel INTEGER NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (
        cd_time,
        cd_responsavel
    ),
    FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_responsavel)
        REFERENCES responsavel(cd_responsavel)
        ON DELETE CASCADE
);

-- ============================================================
-- 9. TIMES INSCRITOS NA COMPETIÇÃO
-- ============================================================
CREATE TABLE inscricao_competicao (
    cd_inscricao_competicao INTEGER NOT NULL,
    cd_competicao INTEGER NOT NULL,
    dt_inicio_inscricao TIMESTAMP NOT NULL,
    dt_encerramento_inscricao TIMESTAMP,
    PRIMARY KEY (
        cd_inscricao_competicao
    ),
    FOREIGN KEY (cd_competicao)
        REFERENCES competicao(cd_competicao)
        ON DELETE CASCADE,
    CHECK (
        dt_encerramento_inscricao IS NULL
        OR dt_encerramento_inscricao >= dt_inicio_inscricao
    )
);

CREATE TABLE vinculo_inscricao_time (
    cd_inscricao_competicao INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    inscrito_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (
        cd_inscricao_competicao,
        cd_time
    ),
    FOREIGN KEY (cd_inscricao_competicao)
        REFERENCES inscricao_competicao(cd_inscricao_competicao)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE
);

-- ============================================================
-- 10. ETAPAS DA COMPETIÇÃO
-- ============================================================
CREATE TABLE etapa_competicao (
    cd_etapa_competicao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL,
    cd_tipo_etapa INTEGER NOT NULL,
    nm_etapa VARCHAR(100) NOT NULL,
    ordem INTEGER NOT NULL,
    descricao TEXT,
    FOREIGN KEY (cd_competicao)
        REFERENCES competicao(cd_competicao)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_tipo_etapa)
        REFERENCES tipo_etapa(cd_tipo_etapa)
        ON DELETE RESTRICT,
    UNIQUE (
        cd_competicao,
        ordem
    ),
    CHECK (
        ordem > 0
    )
);

-- ============================================================
-- 11. RODADAS
-- ============================================================
CREATE TABLE rodada_competicao (
    cd_rodada INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_etapa_competicao INTEGER NOT NULL,
    nr_rodada INTEGER NOT NULL,
    nm_rodada VARCHAR(100),
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    status VARCHAR(30) NOT NULL DEFAULT 'PENDENTE',
    FOREIGN KEY (cd_etapa_competicao)
        REFERENCES etapa_competicao(cd_etapa_competicao)
        ON DELETE CASCADE,
    UNIQUE (
        cd_etapa_competicao,
        nr_rodada
    ),
    CHECK (
        nr_rodada > 0
    ),
    CHECK (
        status IN (
            'PENDENTE',
            'EM_ANDAMENTO',
            'FINALIZADA'
        )
    ),
    CHECK (
        fim_em IS NULL
        OR inicio_em IS NULL
        OR fim_em >= inicio_em
    )
);

-- ============================================================
-- 12. GRUPOS
-- ============================================================
CREATE TABLE grupo_competicao (
    cd_grupo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_etapa_competicao INTEGER NOT NULL,
    nm_grupo VARCHAR(50) NOT NULL,
    FOREIGN KEY (cd_etapa_competicao)
        REFERENCES etapa_competicao(cd_etapa_competicao)
        ON DELETE CASCADE,
    UNIQUE (
        cd_etapa_competicao,
        nm_grupo
    )
);

CREATE TABLE vinculo_grupo_time (
    cd_vinculo_grupo_time INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_grupo INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    FOREIGN KEY (cd_grupo)
        REFERENCES grupo_competicao(cd_grupo)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE,
    UNIQUE (
        cd_grupo,
        cd_time
    )
);

-- ============================================================
-- 13. MÉTRICAS DE CLASSIFICAÇÃO
-- ============================================================
--
-- Catálogo de estatísticas que podem ser utilizadas
-- na classificação.
--
-- Exemplos:
--
-- PONTOS
-- VITORIAS
-- EMPATES
-- DERROTAS
-- GOLS_MARCADOS
-- GOLS_SOFRIDOS
-- SALDO_GOLS
-- SETS_VENCIDOS
-- SETS_PERDIDOS
-- SALDO_SETS
-- PONTOS_MARCADOS
-- PONTOS_SOFRIDOS
-- SALDO_PONTOS
--
-- ============================================================
CREATE TABLE metrica_classificacao (
    cd_metrica_classificacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_metrica_classificacao VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT
);

-- ============================================================
-- 14. CONFIGURAÇÃO DE CLASSIFICAÇÃO
-- ============================================================
CREATE TABLE configuracao_classificacao (
    cd_configuracao_classificacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    FOREIGN KEY (cd_competicao)
        REFERENCES competicao(cd_competicao)
        ON DELETE CASCADE
);

-- ============================================================
-- 15. CRITÉRIOS DA CLASSIFICAÇÃO
-- ============================================================
CREATE TABLE criterio_classificacao (
    cd_criterio_classificacao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_configuracao_classificacao INTEGER NOT NULL,
    cd_metrica_classificacao INTEGER NOT NULL,
    ordem INTEGER NOT NULL,
    direcao VARCHAR(4) NOT NULL DEFAULT 'DESC',
    FOREIGN KEY (cd_configuracao_classificacao)
        REFERENCES configuracao_classificacao(
            cd_configuracao_classificacao
        )
        ON DELETE CASCADE,
    FOREIGN KEY (cd_metrica_classificacao)
        REFERENCES metrica_classificacao(
            cd_metrica_classificacao
        )
        ON DELETE RESTRICT,
    UNIQUE (
        cd_configuracao_classificacao,
        ordem
    ),
    UNIQUE (
        cd_configuracao_classificacao,
        cd_metrica_classificacao
    ),
    CHECK (
        ordem > 0
    ),
    CHECK (
        direcao IN (
            'ASC',
            'DESC'
        )
    )
);

-- ============================================================
-- 16. CLASSIFICAÇÃO DOS GRUPOS
-- ============================================================
--
-- Representa o estado atual da classificação.
--
-- cd_vinculo_grupo_time identifica o time dentro do grupo.
-- cd_grupo é mantido nesta tabela para permitir que a posição
-- seja controlada diretamente por grupo.
--
-- ============================================================
CREATE TABLE classificacao_grupo (
    cd_classificacao_grupo INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_vinculo_grupo_time INTEGER NOT NULL,
    cd_grupo INTEGER NOT NULL,
    posicao INTEGER NOT NULL,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cd_vinculo_grupo_time)
        REFERENCES vinculo_grupo_time(
            cd_vinculo_grupo_time
        )
        ON DELETE CASCADE,
    FOREIGN KEY (cd_grupo)
        REFERENCES grupo_competicao(cd_grupo)
        ON DELETE CASCADE,
    UNIQUE (
        cd_vinculo_grupo_time
    ),
    UNIQUE (
        cd_grupo,
        posicao
    ),
    CHECK (
        posicao > 0
    )
);

-- ============================================================
-- 17. ESTATÍSTICAS DA CLASSIFICAÇÃO
-- ============================================================
CREATE TABLE estatistica_classificacao (
    cd_classificacao_grupo INTEGER NOT NULL,
    cd_metrica_classificacao INTEGER NOT NULL,
    valor NUMERIC(15,3) NOT NULL DEFAULT 0,
    PRIMARY KEY (
        cd_classificacao_grupo,
        cd_metrica_classificacao
    ),
    FOREIGN KEY (cd_classificacao_grupo)
        REFERENCES classificacao_grupo(
            cd_classificacao_grupo
        )
        ON DELETE CASCADE,
    FOREIGN KEY (cd_metrica_classificacao)
        REFERENCES metrica_classificacao(
            cd_metrica_classificacao
        )
        ON DELETE RESTRICT
);

-- ============================================================
-- 18. CONFRONTOS
-- ============================================================
CREATE TABLE confronto (
    cd_confronto INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_etapa_competicao INTEGER NOT NULL,
    nr_confronto INTEGER NOT NULL,
    nome VARCHAR(100),
    FOREIGN KEY (cd_etapa_competicao)
        REFERENCES etapa_competicao(cd_etapa_competicao)
        ON DELETE CASCADE,
    UNIQUE (
        cd_etapa_competicao,
        nr_confronto
    ),
    CHECK (
        nr_confronto > 0
    )
);

-- ============================================================
-- 19. ORIGEM DO PARTICIPANTE
-- ============================================================
CREATE TABLE origem_participante_confronto (
    cd_origem_participante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tipo_origem VARCHAR(30) NOT NULL,
    cd_time INTEGER,
    cd_confronto INTEGER,
    cd_classificacao_grupo INTEGER,
    resultado_confronto VARCHAR(20),
    FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_confronto)
        REFERENCES confronto(cd_confronto)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_classificacao_grupo)
        REFERENCES classificacao_grupo(
            cd_classificacao_grupo
        )
        ON DELETE CASCADE,
    CHECK (
        tipo_origem IN (
            'TIME',
            'CONFRONTO',
            'CLASSIFICACAO_GRUPO'
        )
    ),
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
            AND resultado_confronto IS NOT NULL
        )
        OR
        (
            tipo_origem = 'CLASSIFICACAO_GRUPO'
            AND cd_time IS NULL
            AND cd_confronto IS NULL
            AND cd_classificacao_grupo IS NOT NULL
            AND resultado_confronto IS NULL
        )
    ),
    CHECK (
        resultado_confronto IS NULL
        OR resultado_confronto IN (
            'VENCEDOR',
            'PERDEDOR'
        )
    )
);

-- ============================================================
-- 20. PARTICIPANTES DO CONFRONTO
-- ============================================================
CREATE TABLE confronto_participante (
    cd_confronto INTEGER NOT NULL,
    posicao SMALLINT NOT NULL,
    cd_origem_participante INTEGER NOT NULL,
    PRIMARY KEY (
        cd_confronto,
        posicao
    ),
    FOREIGN KEY (cd_confronto)
        REFERENCES confronto(cd_confronto)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_origem_participante)
        REFERENCES origem_participante_confronto(
            cd_origem_participante
        )
        ON DELETE CASCADE,
    CHECK (
        posicao IN (
            1,
            2
        )
    ),
    UNIQUE (
        cd_confronto,
        cd_origem_participante
    )
);

-- ============================================================
-- 21. PARTIDAS
-- ============================================================
CREATE TABLE partida (
    cd_partida INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL,
    cd_rodada INTEGER,
    cd_confronto INTEGER,
    status VARCHAR(30) NOT NULL DEFAULT 'AGENDADA',
    inicio_agendado TIMESTAMP,
    FOREIGN KEY (cd_competicao)
        REFERENCES competicao(cd_competicao)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_rodada)
        REFERENCES rodada_competicao(cd_rodada)
        ON DELETE SET NULL,
    FOREIGN KEY (cd_confronto)
        REFERENCES confronto(cd_confronto)
        ON DELETE SET NULL,
    CHECK (
        status IN (
            'AGENDADA',
            'EM_ANDAMENTO',
            'FINALIZADA',
            'CANCELADA'
        )
    )
);

-- ============================================================
-- 22. INFORMAÇÕES DA PARTIDA
-- ============================================================
CREATE TABLE info_partida (
    cd_partida INTEGER PRIMARY KEY,
    dt_partida DATE NOT NULL,
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    local_partida VARCHAR(150),
    observacoes TEXT,
    FOREIGN KEY (cd_partida)
        REFERENCES partida(cd_partida)
        ON DELETE CASCADE,
    CHECK (
        fim_em IS NULL
        OR inicio_em IS NULL
        OR fim_em >= inicio_em
    )
);

-- ============================================================
-- 23. ETAPAS INTERNAS DA PARTIDA
-- ============================================================
--
-- NÃO representa uma etapa da competição.
--
-- Futsal:
--   1º tempo
--   2º tempo
--
-- Vôlei:
--   1º set
--   2º set
--   3º set
--
-- ============================================================
CREATE TABLE etapa_partida (
    cd_etapa_partida INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_partida INTEGER NOT NULL,
    nr_etapa INTEGER NOT NULL,
    inicio_em TIMESTAMP,
    fim_em TIMESTAMP,
    FOREIGN KEY (cd_partida)
        REFERENCES partida(cd_partida)
        ON DELETE CASCADE,
    UNIQUE (
        cd_partida,
        nr_etapa
    ),
    CHECK (
        nr_etapa > 0
    ),
    CHECK (
        fim_em IS NULL
        OR inicio_em IS NULL
        OR fim_em >= inicio_em
    )
);

-- ============================================================
-- 24. RESULTADO POR ETAPA DA PARTIDA
-- ============================================================
CREATE TABLE resultado_etapa_partida (
    cd_etapa_partida INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    pontuacao INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (
        cd_etapa_partida,
        cd_time
    ),
    FOREIGN KEY (cd_etapa_partida)
        REFERENCES etapa_partida(cd_etapa_partida)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE,
    CHECK (
        pontuacao >= 0
    )
);

-- ============================================================
-- 25. TIMES DA PARTIDA
-- ============================================================
CREATE TABLE vinculo_partida_time (
    cd_partida INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    mandante BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (
        cd_partida,
        cd_time
    ),
    FOREIGN KEY (cd_partida)
        REFERENCES partida(cd_partida)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE
);

-- ============================================================
-- 26. PENALIDADES
-- ============================================================
CREATE TABLE tipo_penalidade (
    cd_tipo_penalidade INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_tipo_penalidade VARCHAR(100) NOT NULL UNIQUE,
    origem_penalidade VARCHAR(100) NOT NULL,
    descricao TEXT
);

CREATE TABLE penalidade (
    cd_penalidade INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL,
    cd_partida INTEGER,
    cd_vinculo_time_integrante INTEGER NOT NULL,
    cd_tipo_penalidade INTEGER NOT NULL,
    descricao TEXT,
    valor NUMERIC(10,3),
    aplicado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (cd_competicao)
        REFERENCES competicao(cd_competicao)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_partida)
        REFERENCES partida(cd_partida)
        ON DELETE CASCADE,
    FOREIGN KEY (cd_vinculo_time_integrante)
        REFERENCES vinculo_time_integrante(
            cd_vinculo_time_integrante
        )
        ON DELETE CASCADE,
    FOREIGN KEY (cd_tipo_penalidade)
        REFERENCES tipo_penalidade(cd_tipo_penalidade)
        ON DELETE RESTRICT,
    CHECK (
        valor IS NULL
        OR valor >= 0
    )
);