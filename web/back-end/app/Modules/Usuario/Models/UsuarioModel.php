<?php

class Usuario extends Model
{
    // Cadastra Usuário e retorna o ID para auth
    public function cadastrar(array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuario
            (
                nm_usuario,
                email,
                senha,
                path_ft_usuario
            )
            VALUES
            (
                :nm_usuario,
                :email,
                :senha,
                :foto_perfil
            )
            RETURNING cd_usuario
        ");

        $stmt->execute([
            ':nm_usuario' => $dados['nm_usuario'],
            ':email' => $dados['email'],
            ':senha' => $dados['senha'],
            ':foto_perfil' => $dados['foto_perfil'] ?? null,
        ]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res === false || !isset($res['cd_usuario'])) {
            throw new Exception('Não foi possível cadastrar o usuário.');
        }

        return (int) $res['cd_usuario'];
    }

    //Busca usuario pelo email
    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                u.*,
                u.path_ft_usuario AS foto_perfil
            FROM usuario u
            WHERE u.email = :email
        ");

        $stmt->execute([
            ':email' => $email
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    //Lista usuários, com filtros opcionais de escola e papel (vínculos ativos)
    public function listar(array $filtros = []): array
    {
        $sql = "SELECT
                u.cd_usuario,
                u.nm_usuario,
                u.email,
                u.path_ft_usuario,
                u.ativo
            FROM usuario u";

        $parametros = [];

        if (!empty($filtros['escola']) && (int) $filtros['escola'] > 0) {
            $sql .= "
            INNER JOIN vinculo_usuario_escola v
                ON v.cd_usuario = u.cd_usuario
                AND v.ativo = TRUE
                AND v.cd_escola = :escola";

            $parametros[':escola'] = (int) $filtros['escola'];

            if (!empty($filtros['papel'])) {
                $sql .= "
            INNER JOIN papel p
                ON p.cd_papel = v.cd_papel
                AND p.nm_papel = :papel";

                $parametros[':papel'] = (string) $filtros['papel'];
            }
        }

        $sql .= "
            WHERE u.ativo = TRUE
            ORDER BY u.nm_usuario";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM usuario WHERE cd_usuario = :id AND ativo = TRUE'
        );
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    public function listarDisponiveisParaAluno(array $escolas = []): array
    {
        $sql = "SELECT DISTINCT u.cd_usuario, u.nm_usuario, u.email
            FROM usuario u
            WHERE u.ativo = TRUE";
        $parametros = [];
        if ($escolas !== []) {
            $escolas = array_values(array_unique(array_filter(array_map('intval', $escolas), static fn (int $id): bool => $id > 0)));
            if ($escolas === []) {
                return [];
            }
            $placeholders = [];
            foreach ($escolas as $i => $idEscola) {
                $placeholder = ':escola_' . $i;
                $placeholders[] = $placeholder;
                $parametros[$placeholder] = $idEscola;
            }
            $placeholdersComVinculoAluno = [];
            foreach ($escolas as $i => $idEscola) {
                $placeholder = ':escola_aluno_' . $i;
                $placeholdersComVinculoAluno[] = $placeholder;
                $parametros[$placeholder] = $idEscola;
            }
            $sql .= ' AND EXISTS (
                SELECT 1 FROM vinculo_usuario_escola vue
                WHERE vue.cd_usuario = u.cd_usuario
                  AND vue.cd_escola IN (' . implode(',', $placeholdersComVinculoAluno) . ')
                  AND vue.ativo = TRUE
            ) AND NOT EXISTS (
                SELECT 1 FROM vinculo_usuario_escola vue
                INNER JOIN papel p ON p.cd_papel = vue.cd_papel AND p.nm_papel = \'ALU\'
                WHERE vue.cd_usuario = u.cd_usuario
                  AND vue.cd_escola IN (' . implode(',', $placeholders) . ')
                  AND vue.ativo = TRUE
            )';
        } else {
            $sql .= " AND NOT EXISTS (
                SELECT 1 FROM aluno a
                INNER JOIN vinculo_usuario_escola vue ON vue.cd_usuario = a.cd_usuario AND vue.ativo = TRUE
                INNER JOIN papel p ON p.cd_papel = vue.cd_papel AND p.nm_papel = 'ALU'
                WHERE a.cd_usuario = u.cd_usuario AND a.ativo = TRUE
            )";
        }
        $sql .= ' ORDER BY u.nm_usuario';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function possuiVinculoEscolarAtivo(int $idUsuario, int $idEscola): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM vinculo_usuario_escola WHERE cd_usuario = :usuario AND cd_escola = :escola AND ativo = TRUE LIMIT 1');
        $stmt->execute([':usuario' => $idUsuario, ':escola' => $idEscola]);
        return $stmt->fetchColumn() !== false;
    }

    //Atualiza Senha
    public function atualizarSenha(int $id, string $senha): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuario
            SET senha = :senha
            WHERE cd_usuario = :id
        ");

        $stmt->execute([
            ':senha' => $senha,
            ':id' => $id
        ]);
    }

    //Atualiza foto de perfil
    public function atualizarFotoPerfil(int $id, ?string $fotoPerfil): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuario
            SET path_ft_usuario = :foto_perfil
            WHERE cd_usuario = :id
        ");

        $stmt->execute([
            ':foto_perfil' => $fotoPerfil,
            ':id' => $id
        ]);
    }

    public function atualizarNome(int $id, string $nome): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuario
            SET nm_usuario = :nome
            WHERE cd_usuario = :id
        ");

        $stmt->execute([
            ':nome' => $nome,
            ':id' => $id
        ]);
    }

    public function atualizarPerfil
    (
        int $id,
        string $nome,
        string $email,
        ?string $foto = null,
        ?string $senha = null
    ): void
    {
        $campos = [
            'nm_usuario = :nome',
            'email = :email'
        ];
        $parametros = [
            ':nome' => $nome,
            ':email' => $email,
            ':id' => $id
        ];

        if ($foto !== null) {
            $campos[] = 'path_ft_usuario = :foto';
            $parametros[':foto'] = $foto;
        }

        if ($senha !== null) {
            $campos[] = 'senha = :senha';
            $parametros[':senha'] = $senha;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE usuario SET ' . implode(', ', $campos) . ' WHERE cd_usuario = :id'
        );
        $stmt->execute($parametros);
    }

    //Remove usuario
    public function remover(int $id): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM usuario
            WHERE cd_usuario = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);
    }
}
