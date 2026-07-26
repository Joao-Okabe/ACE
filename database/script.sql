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
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuario_papel (
    cd_usuario INTEGER NOT NULL,
    cd_papel INTEGER NOT NULL,
    PRIMARY KEY (cd_usuario, cd_papel),
    CONSTRAINT fk_usuario_papel_usuario
        FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_usuario_papel_papel
        FOREIGN KEY (cd_papel)
        REFERENCES papel(cd_papel)
        ON DELETE RESTRICT
);

CREATE TABLE escola (
    cd_escola INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_usuario INTEGER NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    telefone VARCHAR(20),
    cep VARCHAR(9),
    logradouro VARCHAR(150),
    numero VARCHAR(20),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    uf CHAR(2),
    categoria_administrativa VARCHAR(20) NOT NULL,
    img_logo TEXT,
    criada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativa BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_escola_usuario
        FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE,
    CONSTRAINT chk_categoria
        CHECK (categoria_administrativa IN ('PUBLICA', 'PRIVADA'))
);

CREATE TABLE aluno (
    cd_aluno INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    cd_usuario INTEGER NOT NULL UNIQUE,
    cd_escola INTEGER NOT NULL,
    nome VARCHAR(150) NOT NULL,
    ra VARCHAR(20) UNIQUE,
    data_nascimento DATE NOT NULL,
    sexo CHAR(1),
    telefone VARCHAR(20),
    endereco VARCHAR(255),
    foto_perfil TEXT,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_aluno_usuario
        FOREIGN KEY (cd_usuario)
        REFERENCES usuario(cd_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_aluno_escola
        FOREIGN KEY (cd_escola)
        REFERENCES escola(cd_escola)
        ON DELETE RESTRICT,
    CONSTRAINT chk_sexo
        CHECK (sexo IN ('M', 'F', 'O') OR sexo IS NULL)
);
