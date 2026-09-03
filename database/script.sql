CREATE DATABASE ace;

CREATE TABLE papel (
    cd_papel INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT
);

CREATE TABLE usuario (
    cd_usuario INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    foto_perfil TEXT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE escola (
    cd_escola INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    telefone VARCHAR(20),
    cep VARCHAR(9),
    numero VARCHAR(20),
    categoria_administrativa VARCHAR(20) NOT NULL,
    img_logo TEXT,
    criada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativa BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT chk_categoria
        CHECK (categoria_administrativa IN ('PUBLICA', 'PRIVADA'))
);

CREATE TABLE vinculo_usuario_escola (
    cd_usuario INTEGER NOT NULL,
    cd_escola INTEGER NOT NULL,
    cd_papel INTEGER NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_usuario, cd_escola, cd_papel),
    CONSTRAINT fk_usuario_escola_usuario
        FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_usuario_escola_escola
        FOREIGN KEY (cd_escola)
        REFERENCES escola(cd_escola)
        ON DELETE CASCADE,
    CONSTRAINT fk_usuario_escola_papel
        FOREIGN KEY (cd_papel)
        REFERENCES papel(cd_papel)
        ON DELETE RESTRICT
);

CREATE TABLE aluno (
    cd_aluno INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_usuario INTEGER NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    ra VARCHAR(20) NOT NULL UNIQUE,
    data_nascimento DATE NOT NULL,
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
        CHECK (sexo IN ('M', 'F', 'O') OR sexo IS NULL)
);

CREATE TABLE documentos_aluno (
    cd_documentos_aluno INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_aluno INTEGER NOT NULL,
    rg text,
    comprovante_escolar text,
    atestado_aptidao_fisica text,
    CONSTRAINT fk_cd_aluno
        FOREIGN KEY (cd_aluno)
        REFERENCES aluno(cd_aluno)
        ON DELETE CASCADE,
);

CREATE TABLE competicao (
    cd_competicao INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_criador INTEGER NOT NULL
    nm_competicao VARCHAR(150) NOT NULL, 
    criado em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dt_inicio TIMESTAMP,
    dt_encerramento TIMESTAMP,
    CONSTRAINT fk_criador
        FOREIGN KEY (cd_criador)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE
)

CREATE TABLE formato (
    cd_formato INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_formato VARCHAR(30) NOT NULL,
    ds_formato VARCHAR(2048) NOT NULL,
)

CREATE TABLE modalidade (
    cd_modalidade INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    ds_restr_genero CHAR(5) NOT NULL
    ds_restr_idade CHAR(5) NOT NULL
)

CREATE TABLE esporte (
    cd_esporte INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_esporte VARCHAR(30) NOT NULL,
    ds_esporte VARCHAR(2048) NOT NULL,
)

CREATE TABLE tipo etapa (
    cd_tipo_etapa INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_esporte INTEGER NOT NULL,
    nm_tipo_etapa VARCHAR(50) NOT NULL,
    ds_tipo_etapa VARCHAR(255) NOT NULL,
    ordem INTEGER,
    CONSTRAINT fk_esporte
        FOREIGN KEY (cd_esporte)
        REFERENCES esporte(cd_esporte)
        ON DELETE CASCADE
)

CREATE TABLE time (
    cd_time INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nm_time VARCHAR(50) NOT NULL UNIQUE,
    path brasão VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    atualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
)

CREATE TABLE vinculo_time_escola (
    cd_escola INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    CONSTRAINT fk_escola
        FOREIGN KEY (cd_escola)
        REFERENCES escola(cd_escola)
        ON DELETE CASCADE 
)

CREATE TABLE vinculo_time_integrante (
    cd_usuario INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    cd_funcao_integrante INTEGER NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    CONSTRAINT fk_usuario 
        FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE
    CONSTRAINT fk_time 
        FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE
    CONSTRAINT fk_funcao_integrante 
        FOREIGN KEY (cd_funcao_integrante )
        REFERENCES usuario(cd_funcao_integrante )
        ON DELETE CASCADE
)

CREATE TABLE responsavel (
    cd_responsavel INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_usuario INTEGER NOT NULL UNIQUE,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario
        FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE
)

CREATE TABLE vinculo_time_responsavel (
    cd_responsavel INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    CONSTRAINT fk_time
        FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE
    CONSTRAINT fk_usuario
        FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE
)

CREATE TABLE funcao_integrate (
    cd_funcao_integrante INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_esporte INTEGER NOT NULL,
    nm_funcao VARCHAR(150) NOT NULL,
    ds_funcao VARCHAR(1024) NOT NULL,
    CONSTRAINT fk_esporte
        FOREIGN KEY (cd_esporte)
        REFERENCES esporte(cd_esporte)
        ON DELETE CASCADE 
)

CREATE TABLE partida (
    cd_partida INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_competicao INTEGER NOT NULL UNIQUE,
    cd_esporte INTEGER NOT NULL UNIQUE,
    cd_modalidade INTEGER NOT NULL UNIQUE,
    cd_formato INTEGER NOT NULL UNIQUE,
    CONSTRAINT fk_competicao
        FOREIGN KEY (cd_competicao)
        REFERENCES competicao(cd_competicao)
        ON DELETE CASCADE
    CONSTRAINT fk_esporte
        FOREIGN KEY (cd_esporte)
        REFERENCES esporte(cd_esporte)
        ON DELETE CASCADE
    CONSTRAINT fk_modalideda
        FOREIGN KEY (cd_modalidade)
        REFERENCES modalidade(cd_modalidade)
        ON DELETE CASCADE
    CONSTRAINT fk_formato
        FOREIGN KEY (cd_formato)
        REFERENCES formato(cd_formato)
        ON DELETE CASCADE
)

CREATE TABLE info_partida (
    cd_partida INTEGER NOT NULL UNIQUE,
    dt_partida TIMESTAMP NOT NULL,
    local_partida VARCHAR(1024) NOT NULL,
    CONSTRAINT fk_partida
        FOREIGN KEY (cd_partida)
        REFERENCES partida(cd_partida)
        ON DELETE CASCADE
)

CREATE TABLE etapa_partida (
    cd_etapa_partida INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_partida INTEGER NOT NULL,
    cd_tipo_etapa INTEGER NOT NULL,
    nr_etapa INTEGER NOT NULL,
    inicio_em TIMESTAMP NOT NULL,
    fim_em TIMESTAMP NOT NULL,
    CONSTRAINT fk_partida
        FOREIGN KEY (cd_partida)
        REFERENCES partida(cd_partida)
        ON DELETE CASCADE
    CONSTRAINT fk_tipo_etapa
        FOREIGN KEY (cd_tipo_etapa)
        REFERENCES tipo etapa(cd_tipo_etapa)
        ON DELETE CASCADE
)

CREATE TABLE resultado_etapa_partida (
    cd_etapa_partida INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    pontuação INTEGER NOT NULL,
    CONSTRAINT fk_etapa_partida 
        FOREIGN KEY (cd_etapa_partida )
        REFERENCES etapa_partida (cd_etapa_partida )
        ON DELETE CASCADE
    CONSTRAINT fk_time
        FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE
)

CREATE TABLE vinculo_partida_time (
    cd_partida INTEGER NOT NULL,
    cd_time INTEGER NOT NULL,
    CONSTRAINT fk_partida 
        FOREIGN KEY (cd_partida)
        REFERENCES partida(cd_partida)
        ON DELETE CASCADE
    CONSTRAINT fk_time 
        FOREIGN KEY (cd_time)
        REFERENCES time(cd_time)
        ON DELETE CASCADE
)
