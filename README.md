# ACE - Sistema de Gerenciamento de Interclasses e Jogos Escolares

## 📖 Sobre o Projeto

O **ACE (Sistema de Gerenciamento de Interclasses e Jogos Escolares)** é uma aplicação web desenvolvida como Trabalho de Conclusão de Curso (TCC), com o objetivo de auxiliar instituições de ensino na organização e gerenciamento de competições esportivas escolares.

O sistema permite o gerenciamento de escolas, usuários, alunos e, futuramente, competições, modalidades, equipes e partidas.

---

## 🎯 Objetivos

- Centralizar o gerenciamento de competições escolares;
- Facilitar o cadastro e administração de escolas;
- Gerenciar usuários com diferentes níveis de acesso;
- Organizar alunos participantes;
- Automatizar processos administrativos.

---

## ✨ Funcionalidades

### Implementadas

- Autenticação de usuários;
- Cadastro de escolas;
- Consulta automática de endereço por CEP;
- Cadastro de usuários;
- Cadastro de alunos;
- Controle de permissões por papel.

### Em desenvolvimento

- Gerenciamento de modalidades;
- Gerenciamento de equipes;
- Gerenciamento de campeonatos;
- Controle de partidas;
- Ranking e classificação.

---

## 🏗 Arquitetura

O projeto utiliza o padrão arquitetural **MVC (Model-View-Controller)**.

```
Cliente
    │
    ▼
Controller
    │
    ▼
Service
    │
    ▼
Model
    │
    ▼
PostgreSQL
```

### Estrutura do Projeto

```
app/
├── Controllers/
├── Core/
├── Middleware/
├── Models/
├── Services/
└── Views/

database/
docker/
public/
uploads/
```

---

## 🛠 Tecnologias

### Backend

- PHP 8
- PostgreSQL
- Apache

### Frontend

- HTML5
- CSS3
- JavaScript

### Infraestrutura

- Docker
- Docker Compose

### APIs

- ViaCEP

---

## 🗄 Banco de Dados

O sistema utiliza PostgreSQL.

### Principais entidades

- Usuário
- Papel
- Escola
- Vínculo Usuário-Escola
- Aluno

Modelo simplificado:

```
Usuário
    │
    ▼
Vínculo
    ├── Escola
    └── Papel

Aluno
    │
    ▼
Usuário
```

---

## 🔐 Controle de Permissões

Cada usuário pode possuir diferentes papéis em diferentes escolas.

Exemplo:

| Usuário | Escola | Papel |
|---------|---------|--------|
| João | Escola A | Administrador |
| João | Escola B | Professor |
| Maria | Escola A | Aluno |

---

## 🚀 Executando o Projeto

### Pré-requisitos

- Docker
- Docker Compose

### Clonar o repositório

```bash
git clone https://github.com/Joao-Okabe/ACE.git
cd ACE
```

### Executar

```bash
docker compose up -d
```

### Acessar

```
http://localhost:8080
```

---

## 📂 Documentação

A documentação do projeto encontra-se no repositório de documentação.

Inclui:

- Requisitos Funcionais
- Requisitos Não Funcionais
- Casos de Uso
- Diagramas de Sequência
- MER
- DER
- Diagrama de Classes

---

## 👥 Equipe

- João Antônio Okabe Van Berghem
- (Demais integrantes)

---

## 📄 Licença

Projeto desenvolvido exclusivamente para fins acadêmicos.