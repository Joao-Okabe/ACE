<?php
class Aluno extends Model
{
    private Filtro $filtro;

    public function __construct()
    {
        parent::__construct();
        $this->filtro = new Filtro();
    }

    //Cadastra Aluno
    public function cadastrar(array $dados): void
    {
        $stmt = $this->pdo->prepare("
        INSERT INTO aluno(
            cd_usuario,
            ra,
            data_nascimento,
            sexo,
            telefone,
            cep
        ) VALUES (
            :usuario,
            :ra,
            :data_nascimento,
            :sexo,
            :telefone,
            :cep
        )");

        $stmt->execute([
            ':usuario' => $dados['usuario'],
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
            ra = :ra,
            data_nascimento = :data_nascimento,
            sexo = :sexo,
            telefone = :telefone,                
            cep = :cep
        WHERE cd_aluno = :id");

        $stmt->execute([
            ':ra' => $dados['ra'] ?? null,
            ':data_nascimento' => $dados['data_nascimento'] ?? null,
            ':sexo' => $dados['sexo'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':cep' => $dados['cep'] ?? null,
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
            u.nm_usuario AS nome,
            u.path_ft_usuario,
            (
                SELECT e.nm_escola
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

        $filtrosSql = $this->filtro->filtrosAluno($filtros);

        if (!empty($filtrosSql['onde'])) {
            $sql .= ' WHERE ' . implode(' AND ', $filtrosSql['onde']);
        }

        $ordem = $this->filtro->ordem($filtros);
        $sql .= " ORDER BY a.cd_aluno {$ordem}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($filtrosSql['parametros']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Busca Aluno
    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT
            a.*,
            u.email,
            u.nm_usuario AS nome,
            u.foto_perfil,
            (
                SELECT e.nm_escola
                FROM vinculo_usuario_escola up
                INNER JOIN escola e ON e.cd_escola = up.cd_escola
                WHERE up.cd_usuario = a.cd_usuario
                    AND up.ativo = TRUE
                ORDER BY up.criado_em DESC
                LIMIT 1
            ) AS escola
        FROM aluno a
        INNER JOIN usuario u ON u.cd_usuario = a.cd_usuario
        WHERE a.cd_aluno = :id");

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