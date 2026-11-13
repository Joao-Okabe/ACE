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

    public function existePorUsuario(int $idUsuario): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM aluno WHERE cd_usuario = :usuario LIMIT 1'
        );
        $stmt->execute([':usuario' => $idUsuario]);

        return $stmt->fetchColumn() !== false;
    }

    public function buscarPorUsuario(int $idUsuario): ?array
    {
        $stmt = $this->pdo->prepare('SELECT cd_aluno, ativo FROM aluno WHERE cd_usuario = :usuario');
        $stmt->execute([':usuario' => $idUsuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function reativar(int $id, array $dados): void
    {
        $stmt = $this->pdo->prepare("UPDATE aluno SET
            ra = :ra,
            data_nascimento = :data_nascimento,
            sexo = :sexo,
            telefone = :telefone,
            cep = :cep,
            ativo = TRUE,
            atualizado_em = CURRENT_TIMESTAMP
            WHERE cd_aluno = :id");
        $stmt->execute([
            ':ra' => $dados['ra'] ?? null,
            ':data_nascimento' => $dados['data_nascimento'] ?? null,
            ':sexo' => $dados['sexo'] ?? null,
            ':telefone' => $dados['telefone'] ?? null,
            ':cep' => $dados['cep'] ?? null,
            ':id' => $id,
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
    // v = vinculoapp/Models/AlunoModel.php
    public function listar(array $filtros = []): array
    {
        $filtroSubqueryEscola = '';
        if (isset($filtros['escolas']) && is_array($filtros['escolas'])) {
            $idsEscola = array_values(array_unique(array_filter(
                array_map('intval', $filtros['escolas']),
                static fn (int $id): bool => $id > 0
            )));
            if ($idsEscola === []) {
                return [];
            }
            $filtroSubqueryEscola = ' AND up.cd_escola IN (' . implode(',', $idsEscola) . ')';
        }

        $sql = 
        "SELECT
            a.*,
            u.email,
            u.nm_usuario,
            u.nm_usuario AS nome,
            u.path_ft_usuario,
            u.path_ft_usuario AS foto_perfil,
            (
                SELECT e.nm_escola
                FROM vinculo_usuario_escola up
                INNER JOIN papel p ON p.cd_papel = up.cd_papel
                INNER JOIN escola e ON e.cd_escola = up.cd_escola
                WHERE up.cd_usuario = a.cd_usuario
                    AND p.nm_papel = 'ALU'
                    AND up.ativo = TRUE
                    {$filtroSubqueryEscola}
                ORDER BY up.criado_em DESC
                LIMIT 1
            ) AS escola,

            (
                SELECT up.cd_escola
                FROM vinculo_usuario_escola up
                INNER JOIN papel p ON p.cd_papel = up.cd_papel
                WHERE up.cd_usuario = a.cd_usuario
                    AND p.nm_papel = 'ALU'
                    AND up.ativo = TRUE
                    {$filtroSubqueryEscola}
                ORDER BY up.criado_em DESC
                LIMIT 1
            ) AS cd_escola
        FROM aluno a
        INNER JOIN usuario u
            ON u.cd_usuario = a.cd_usuario
        WHERE a.ativo = TRUE AND u.ativo = TRUE
          AND EXISTS (
            SELECT 1 FROM vinculo_usuario_escola vue
            INNER JOIN papel p ON p.cd_papel = vue.cd_papel AND p.nm_papel = 'ALU'
            WHERE vue.cd_usuario = a.cd_usuario AND vue.ativo = TRUE
          )";

        $filtrosSql = $this->filtro->filtrosAluno($filtros);

        if (!empty($filtros['escolas']) && is_array($filtros['escolas'])) {
            $escolas = array_values(array_unique(array_filter(
                array_map('intval', $filtros['escolas']),
                static fn (int $id): bool => $id > 0
            )));

            if ($escolas === []) {
                return [];
            }

            $placeholders = [];
            foreach ($escolas as $i => $idEscola) {
                $placeholder = ':escola_permitida_' . $i;
                $placeholders[] = $placeholder;
                $filtrosSql['parametros'][$placeholder] = $idEscola;
            }

            $filtrosSql['onde'][] = 'a.cd_usuario IN (
                SELECT up.cd_usuario
                FROM vinculo_usuario_escola up
                INNER JOIN papel p ON p.cd_papel = up.cd_papel
                WHERE up.cd_escola IN (' . implode(',', $placeholders) . ')
                  AND p.nm_papel = \'ALU\'
                  AND up.ativo = TRUE
            )';
        }

        if (!empty($filtrosSql['onde'])) {
            $sql .= ' AND ' . implode(' AND ', $filtrosSql['onde']);
        }

        $ordem = $this->filtro->ordem($filtros);
        $sql .= " ORDER BY a.cd_aluno {$ordem}";

        $limite = max(1, min(100, (int) ($filtros['limite'] ?? 50)));
        $offset = max(0, (int) ($filtros['offset'] ?? 0));
        $sql .= ' LIMIT :limite OFFSET :offset';

        $stmt = $this->pdo->prepare($sql);
        foreach ($filtrosSql['parametros'] as $nome => $valor) {
            $stmt->bindValue($nome, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Busca Aluno
    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT
            a.*,
            u.email,
            u.nm_usuario AS nome,
            u.path_ft_usuario,
            u.path_ft_usuario AS foto_perfil,
            (
                SELECT e.nm_escola
                FROM vinculo_usuario_escola up
                INNER JOIN papel p ON p.cd_papel = up.cd_papel
                INNER JOIN escola e ON e.cd_escola = up.cd_escola
                WHERE up.cd_usuario = a.cd_usuario
                    AND p.nm_papel = 'ALU'
                    AND up.ativo = TRUE
                ORDER BY up.criado_em DESC
                LIMIT 1
            ) AS escola
        FROM aluno a
        INNER JOIN usuario u ON u.cd_usuario = a.cd_usuario
        WHERE a.cd_aluno = :id AND a.ativo = TRUE AND u.ativo = TRUE");

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

    public function obterEscolaDoAluno(int $idUsuario): ?int
    {
        $escolas = $this->listarEscolasAtivasDoAluno($idUsuario);
        return count($escolas) === 1 ? $escolas[0] : null;
    }

    public function listarEscolasAtivasDoAluno(int $idUsuario): array
    {
        return array_map('intval', array_column($this->listarEscolasAtivasDoAlunoComNome($idUsuario), 'cd_escola'));
    }

    public function listarEscolasAtivasDoAlunoComNome(int $idUsuario): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT vue.cd_escola, e.nm_escola
            FROM vinculo_usuario_escola vue
            INNER JOIN escola e ON e.cd_escola = vue.cd_escola AND e.ativa = TRUE
            INNER JOIN papel p ON p.cd_papel = vue.cd_papel
            WHERE vue.cd_usuario = :cd_usuario
            AND p.nm_papel = 'ALU'
            AND vue.ativo = TRUE
            ORDER BY vue.cd_escola"
        );

        $stmt->execute([
            ':cd_usuario' => $idUsuario
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function desativar(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE aluno SET ativo = FALSE WHERE cd_aluno = :id');
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('Aluno não encontrado.');
        }
    }

    public function possuiAlunoAtivoNaEscola(int $idUsuario, int $idEscola): bool
    {
        $stmt = $this->pdo->prepare("SELECT EXISTS (
            SELECT 1 FROM vinculo_usuario_escola vue
            INNER JOIN papel p ON p.cd_papel = vue.cd_papel AND p.nm_papel = 'ALU'
            WHERE vue.cd_usuario = :usuario AND vue.cd_escola = :escola AND vue.ativo = TRUE
        )");
        $stmt->execute([':usuario' => $idUsuario, ':escola' => $idEscola]);
        return (bool) $stmt->fetchColumn();
    }

}
