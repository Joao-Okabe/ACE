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

    //Lista Alunos, a = aluno, u = usuario 
    // a.* = tudo da tabela aluno
    // v = vinculo
    public function listar(array $filtros = []): array
    {
        $sql = "
        SELECT
            a.*,
            u.email,
            u.foto_perfil,
            (
                SELECT e.nome
                FROM vinculo_usuario_escola up
                INNER JOIN escola e ON e.cd_escola = up.cd_escola
                WHERE up.cd_usuario = a.cd_usuario
                    AND up.ativo = TRUE
                LIMIT 1
            ) AS escola
        FROM aluno a
        INNER JOIN usuario u
            ON u.cd_usuario = a.cd_usuario
        ";

        $params = [];
        $where = [];

        if (!empty($filtros['nome'])) {
            $where[] = 'a.nome ILIKE :nome';
            $params[':nome'] = '%' . $filtros['nome'] . '%';
        }

        if (!empty($filtros['escola'])) {
            $where[] = 'a.cd_usuario IN (
                SELECT up.cd_usuario
                FROM vinculo_usuario_escola up
                WHERE up.cd_escola = :cd_escola
                    AND up.ativo = TRUE
            )';
            $params[':cd_escola'] = $filtros['escola'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $ordem = strtoupper($filtros['ordem'] ?? 'ASC');
        $ordem = in_array($ordem, ['ASC', 'DESC'], true) ? $ordem : 'ASC';
        $sql .= " ORDER BY a.cd_aluno {$ordem}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Busca Escola
    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT a.*, u.foto_perfil FROM aluno a INNER JOIN usuario u ON u.cd_usuario = a.cd_usuario WHERE a.cd_aluno = :id");

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