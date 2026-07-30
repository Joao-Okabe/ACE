<?php

class Aluno extends Model 
{
    public function cadastrar(array $dados): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO aluno
            (
                cd_aluno,
                cd_usuario,
                cd_escola,
                nome,
                ra,
                data_nascimento,
                sexo,
                telefone,
                cep,
                foto_perfil,
            )
            VALUES
            (
                :cd_aluno,
                :cd_usuario,
                :cd_escola,
                :nome,
                :ra,
                :data_nascimento,
                :sexo,
                :telefone,
                :cep,
                :foto_perfil
            )
        ");

        $stmt->execute([
            ':cd_aluno' => $dados['cd_aluno'],
            ':cd_usuario' => $dados['usuario'],
            ':cd_escola' => $dados['escola'],
            ':nome' => $dados['nome'],
            ':ra' => $dados['ra'],
            ':data_nascimento' => $dados['data_nascimento'],
            ':sexo' => $dados['sexo'],
            ':telefone' => $dados['telefone'],
            ':cep' => $dados['cep'],
            ':foto_perfil' => $dados['foto_perfil'],
        ]);
    }

    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                cd_aluno,
                cd_usuario,
                cd_escola,
                nome,
                ra,
                data_nascimento,
                sexo,
                telefone,
                cep,
                foto_perfil,
            FROM aluno
            ORDER BY cd_aluno
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Busca Aluno
    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM aluno
            WHERE cd_aluno = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);

        $escola = $stmt->fetch(PDO::FETCH_ASSOC);

        return $escola ?: null;
    }
}