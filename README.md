# ACE — Aplicativo de Competições Escolares

O **ACE (Aplicativo de Competições Escolares)** é uma aplicação web acadêmica para apoiar o cadastro e a organização de escolas, usuários, alunos, equipes e competições esportivas. O sistema possui uma interface web renderizada pelo PHP e uma API REST para operações selecionadas, usando PostgreSQL como banco de dados.

> **Estado do projeto:** este README foi atualizado a partir do código-fonte analisado. A importação de CSV e a geração de PDF não foram encontradas implementadas na versão examinada; são apresentadas abaixo como evoluções recomendadas, não como funcionalidades disponíveis.

## Sumário

- [Funcionalidades identificadas](#funcionalidades-identificadas)
- [Arquitetura](#arquitetura)
- [Tecnologias](#tecnologias)
- [Estrutura do repositório](#estrutura-do-repositório)
- [Requisitos](#requisitos)
- [Configuração e execução com Docker](#configuração-e-execução-com-docker)
- [Banco de dados](#banco-de-dados)
- [API REST](#api-rest)
- [Importação de CSV: proposta de implementação](#importação-de-csv-proposta-de-implementação)
- [Geração de PDF: proposta de implementação](#geração-de-pdf-proposta-de-implementação)
- [Segurança e qualidade](#segurança-e-qualidade)
- [Documentação relacionada](#documentação-relacionada)
- [Equipe](#equipe)

## Funcionalidades

### Cadastros e administração

- Autenticação por sessão e encerramento de sessão.
- Operações de cadastro, consulta, edição e remoção de usuários, escolas, alunos, equipes e competições, conforme as rotas e os controladores existentes.
- Consulta de endereço a partir de CEP via ViaCEP no front-end.
- Vínculos entre usuários, papéis e escolas.
- Vínculos de equipes com escolas, integrantes, responsáveis e técnicos.
- Definição de capitão, funções de integrantes e escalação.
- Gerenciamento de etapas de competição, inscrições de equipes e partidas.
- Painel com indicadores de alunos, equipes e competições.
- Token CSRF e configuração de cookies de sessão com `HttpOnly` e `SameSite=Lax`.

### Competições e chaveamentos

O código contém serviços de geração para estes formatos:

- **Eliminatória simples**.
- **Eliminatória dupla**.
- **Fase de grupos**, com distribuição em grupos e geração de confrontos dentro dos grupos.
- **Sistema suíço**, com geração de rodadas e pareamentos iniciais.

O comportamento de cada fluxo depende dos dados cadastrados, das etapas configuradas e das regras implementadas nos respectivos serviços.

### Arquivos e relatórios

A versão analisada não contém implementação identificada de importação/exportação de arquivos CSV nem de geração de documentos PDF.

## Arquitetura

O projeto utiliza uma organização inspirada em **MVC**, com separação adicional de serviços e modelos por módulo.

```text
Navegador
   │
   ├── Interface web (HTML, CSS, JavaScript, Bootstrap)
   │
   ▼
Rotas web / API REST
   │
   ▼
Controllers
   │
   ▼
Services ─── regras de negócio
   │
   ▼
Models
   │
   ▼
PostgreSQL
```

O front-end está em `web/front-end/`. A aplicação PHP e a API estão em `web/back-end/`. O ponto de entrada HTTP é `web/back-end/public/index.php`, e as rotas são declaradas em `web/back-end/routes/web.php` e `web/back-end/routes/api.php`.

## Tecnologias

- **Backend:** PHP 8.4 na imagem Docker.
- **Banco de dados:** PostgreSQL 17.
- **Acesso ao banco:** PDO com driver `pdo_pgsql`.
- **Servidor HTTP:** Apache com `mod_rewrite`.
- **Frontend:** HTML5, CSS3, JavaScript e Bootstrap 5.
- **Consulta de CEP:** ViaCEP.
- **Ambiente de desenvolvimento:** Docker Compose.

Não há `composer.json` identificado na versão analisada. Portanto, bibliotecas PHP adicionais, como uma biblioteca de PDF, precisam ser instaladas e integradas explicitamente.

## Estrutura do repositório

```text
ACE/
├── compose.yml
├── .env                         # configuração local do Docker
├── database/
│   ├── script.sql               # estrutura do banco
│   ├── triggers.sql
│   └── seeds/                   # dados iniciais
├── docker/
│   └── php/
│       └── Dockerfile
└── web/
    ├── back-end/
    │   ├── app/
    │   │   ├── Core/            # Router, Database, View, CSRF etc.
    │   │   ├── Middleware/
    │   │   ├── Modules/         # Controllers, Models, Services e Validators
    │   │   └── Views/           # páginas PHP
    │   ├── config/
    │   ├── public/              # ponto de entrada e arquivos públicos
    │   ├── routes/
    │   ├── storage/uploads/     # arquivos enviados
    │   └── tests/
    └── front-end/
        └── assets/
            ├── css/
            └── js/
```

## Requisitos

- Git.
- Docker Engine e Docker Compose v2 (`docker compose`).
- Portas locais disponíveis para a aplicação e para o PostgreSQL.
- Um arquivo `.env` na raiz do projeto, preenchido com as variáveis descritas abaixo.

## Configuração e execução com Docker

### 1. Clonar o repositório

```bash
git clone https://github.com/Joao-Okabe/ACE.git
cd ACE
```

### 2. Configurar o ambiente

Crie um `.env` na raiz do projeto. Exemplo **somente para desenvolvimento local**:

```dotenv
APP_NAME=ACE
APP_ENV=development
APP_PORT=8080

DB_HOST=db
DB_PORT=5432
DB_DATABASE=ace
DB_USERNAME=ace
DB_PASSWORD=troque-esta-senha
```

O serviço web lê essas variáveis pelo Compose; o PostgreSQL usa as mesmas credenciais para inicializar o banco. Dentro da rede Docker, `DB_HOST` deve ser o nome do serviço (`db`), não `localhost`.

> Não use credenciais de exemplo em produção. Não versione o `.env` real nem senhas, tokens ou dados pessoais. Se o arquivo `.env` estiver ausente, o Compose poderá não conseguir interpolar as variáveis.

### 3. Subir os contêineres

```bash
docker compose up -d --build
docker compose ps
```

Acompanhe os logs se necessário:

```bash
docker compose logs -f web
docker compose logs -f db
```

### 4. Criar o esquema e inserir os dados iniciais

O Compose cria o contêiner e o volume do PostgreSQL, mas o arquivo `database/script.sql` não é montado automaticamente como inicialização do banco no `compose.yml`. Execute o esquema e os seeds manualmente em um banco recém-criado.

Uma opção é usar `psql` dentro do contêiner do banco:

```bash
docker compose exec -T db psql -U ace -d ace < database/script.sql
```

Depois, execute os arquivos de `database/seeds/` **na ordem necessária às dependências**. Confirme a ordem e o conteúdo antes de executar em um banco que já contenha dados. Por exemplo, os arquivos numerados são destinados a dados iniciais; não os execute repetidamente sem verificar constraints e duplicidades.

Se as credenciais do `.env` forem diferentes do exemplo, ajuste `-U ace -d ace` para os valores configurados.

### 5. Acessar a aplicação

Abra:

```text
http://localhost:8080
```

A porta é definida por `APP_PORT` no `.env`.

### Comandos úteis

```bash
# Parar os contêineres sem apagar os dados
docker compose down

# Ver logs
docker compose logs -f

# Reiniciar os serviços
docker compose restart

# ATENÇÃO: remove também o volume do banco e seus dados
docker compose down -v
```

## Banco de dados

O banco utiliza PostgreSQL e possui entidades relacionadas a:

- Usuários, papéis e vínculos de usuários com escolas.
- Escolas e alunos.
- Esportes, modalidades, formatos e tipos de etapa.
- Competições, inscrições e equipes.
- Integrantes, responsáveis, técnicos e escalações.
- Etapas, rodadas, grupos, confrontos, participantes e partidas.

A definição principal está em `database/script.sql`; triggers estão em `database/triggers.sql`; dados iniciais ficam em `database/seeds/`.

Para inspeção e administração visual do PostgreSQL, pode-se utilizar o DBeaver Community ou outra ferramenta compatível.

## API REST

As rotas estão declaradas em `web/back-end/routes/api.php`. Os recursos encontrados incluem:

| Recurso | Operações identificadas |
|---|---|
| `/api/alunos` | GET lista, GET por ID, POST, PUT por ID, DELETE por ID |
| `/api/usuarios` | GET lista, GET por ID, POST, PUT por ID, DELETE por ID |
| `/api/escolas` | GET lista, GET por ID, POST, PUT por ID, DELETE por ID |
| `/api/times` | GET lista, GET por ID, POST, PUT por ID, DELETE por ID |
| `/api/auth/login` | POST para autenticação |
| `/api/auth/logout` | POST para encerramento de sessão |
| `/api/csrf` | GET para obter token CSRF |
| `/api/times/{id}/integrantes` | POST para vincular e DELETE para remover integrante |

### API V1 para clientes externos

As rotas `/api/v1/auth/login`, `/api/v1/auth/me`, `/api/v1/auth/refresh` e `/api/v1/auth/logout` usam Bearer JWT, sem sessão PHP. O contrato atualizado está em [`web/back-end/openapi.yaml`](web/back-end/openapi.yaml). O cliente deve agrupar renovações simultâneas em uma única chamada (*single-flight*), pois um refresh token consumido não pode ser reutilizado.
| `/api/times/{id}/integrantes/{idUsuario}/capitao` | PUT para definir capitão e DELETE para remover a condição |
| `/api/times/{id}/responsaveis` | POST para vincular responsável |
| `/api/times/{id}/tecnicos` | POST para vincular técnico |
| `/api/times/{id}/escalacao` | PUT para salvar escalação |

A existência de uma rota no código não garante que esteja acessível anonimamente: autenticação, autorização, validação e CSRF devem ser verificados no controlador e nos componentes de segurança correspondentes. Consulte os controladores para os formatos exatos de requisição e resposta.

## Recursos de segurança

- Sessões PHP para autenticação.
- Cookies de sessão configurados com `HttpOnly` e `SameSite=Lax`.
- Token CSRF disponível pela aplicação.
- Separação de rotas, controladores, serviços, modelos e validadores.
- Acesso ao banco de dados por PDO.

## Documentação relacionada

A documentação complementar do projeto está em:

- [ACE-docs](https://github.com/Joao-Okabe/ACE-docs)

## Equipe

- **Clara Lopes Romão** — Front-end: [Claralops](https://github.com/Claralops)
- **João Antônio Okabe Van Berghem** — Back-end: [Joao-Okabe](https://github.com/Joao-Okabe)
- **Kauã Santos da Silva** — Mobile: [kaua132465](https://github.com/kaua132465)
- **Yasmin da Silva Santos** — Documentação e liderança: [yaaszou](https://github.com/yaaszou)
- **Caroline Caetano** — Design: [carolicaetano3](https://github.com/carolicaetano3)
- **Gustavo Willians** — Documentação: [MaxyLygts](https://github.com/MaxyLygts)

## Licença

Projeto desenvolvido para fins acadêmicos. Uma licença de software específica não foi identificada no material analisado; defina e inclua uma licença formal caso o projeto precise estabelecer condições de uso, modificação e redistribuição.
