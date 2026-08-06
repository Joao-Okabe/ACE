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