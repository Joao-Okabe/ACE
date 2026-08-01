<?php
class Aluno extends Model
{
    //Cadastra Aluno
    public function cadastrar(array $dados): void
    {
        $stmt = $this->pdo->prepare("
        INSERT INTO aluno(
            cd_usuario,
            cd_escola,
            nome,
            ra,
            data_nascimento,
            sexo,
            telefone,
            cep,
            foto_perfil
        ) VALUES (
            :usuario,
            :escola,
            :nome,
            :ra,
            :data_nascimento,
            :sexo,
            :telefone,
            :cep,
            :foto_perfil
        )");

        $stmt->execute([
            ':usuario' => $dados['usuario'],
            ':escola' => $dados['escola'],
            ':nome' => $dados['nome'],
            ':ra' => $dados['ra'] ?? null,
            ':data_nascimento' => $dados['data_nascimento'],
            ':sexo' => $dados['sexo'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':cep' => $dados['cep'] ?? null,
            ':foto_perfil' => $dados['foto_perfil'] ?? null,
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
            foto_perfil = :foto_perfil,              
            cd_escola = :cd_escola          
        WHERE cd_aluno = :id");

        $stmt->execute([
            ':nome' => $dados['nome'],
            ':ra' => $dados['ra'] ?? null,
            ':data_nascimento' => $dados['data_nascimento'] ?? null,
            ':sexo' => $dados['sexo'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':cep' => $dados['cep'] ?? null,
            ':foto_perfil' => $dados['foto_perfil'] ?? null,
            ':cd_escola' => $dados['cd_escola'] ?? $dados['escola'] ?? null,
            ':id' => $id
        ]);
    }

    //Lista Alunos
    public function listar(): array
    {
        $stmt = $this->pdo->query("
        SELECT  cd_aluno, 
                cd_usuario, 
                cd_escola, 
                nome, 
                ra, 
                data_nascimento, 
                sexo, 
                telefone,
                cep,
                foto_perfil,
                criado_em,
                ativo 
        FROM aluno ORDER BY cd_aluno");

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