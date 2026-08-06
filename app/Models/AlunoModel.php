<?php
class Aluno extends Model
{
    //Cadastra Aluno
    public function cadastrar(array $dados): void
    {
        $stmt = $this->pdo->prepare("
        INSERT INTO aluno(
            cd_usuario,
            nome,
            ra,
            data_nascimento,
            sexo,
            telefone,
            cep
        ) VALUES (
            :usuario,
            :nome,
            :ra,
            :data_nascimento,
            :sexo,
            :telefone,
            :cep
        )");

        $stmt->execute([
            ':usuario' => $dados['usuario'],
            ':nome' => $dados['nome'],
            ':ra' => $dados['ra'] ?? null,
            ':data_nascimento' => $dados['data_nascimento'] ?? null,
            ':sexo' => $dados['sexo'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':cep' => $dados['cep'] ?? null,
        ]);
    }

    // Atualizar Aluno
    public function atualizar(int $id, array $dados): void
    {
        $stmt = $this->pdo->prepare("
         UPDATE aluno SET 
            nome = :nome, 
            ra = :ra,
            data_nascimento = :data_nascimento,
            sexo = :sexo,
            telefone = :telefone,                
            cep = :cep,                
            cd_escola = :cd_escola          
        WHERE cd_aluno = :id");

        $stmt->execute([
            ':nome' => $dados['nome'],
            ':ra' => $dados['ra'] ?? null,
            ':data_nascimento' => $dados['data_nascimento'] ?? null,
            ':sexo' => $dados['sexo'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':cep' => $dados['cep'] ?? null,
            ':cd_escola' => $dados['cd_escola'] ?? $dados['escola'] ?? null,
            ':id' => $id
        ]);
    }

    //Pega a foto do aluno baseado no cd usuario, aluno = "a" e usuario = "u"
    public function pegarFotoAluno(){
        $stmt = $this->pdo->query("
        SELECT 
            u.foto_perfil
        FROM aluno a INNER JOIN usuario u ON u.cd_usuario = a.cd_usuario;
        ");
    }

    //Lista Alunos, a = aluno, u = usuario 
    // a.* = tudo da tabela aluno
    public function listar(): array
    {
        $stmt = $this->pdo->query("
        SELECT
            a.*,
            u.email,
            u.foto_perfil
        FROM aluno a
        INNER JOIN usuario u
            ON u.cd_usuario = a.cd_usuario
        ORDER BY a.nome
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Busca Escola
    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM aluno WHERE cd_aluno = :id");

        $stmt->execute([
            ':id' => $id
        ]);

        $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

        return $aluno ?: null;
    }

    //Remove aluno
    public function remover(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM aluno WHERE cd_aluno = :id");

        $stmt->execute([
            ':id' => $id
        ]);
    }
}