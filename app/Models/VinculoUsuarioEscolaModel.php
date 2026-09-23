<?php

class VinculoUsuarioEscola extends Model
{
    //Vincula Papel do usuário
    public function vincularPapelEscola(int $idUsuario, int $idEscola, int $idPapel): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO vinculo_usuario_escola
            (
                cd_usuario,
                cd_escola,
                cd_papel
            )
            VALUES
            (
                :usuario,
                :escola,
                :papel
            )
        ");

        $stmt->execute([
            ':usuario' => $idUsuario,
            ':escola' => $idEscola,
            ':papel' => $idPapel
        ]);
    }

    public function listarVinculo (int $idUsuario) {
        $stmt = $this->pdo->prepare(
            "SELECT cd_escola, cd_papel
            FROM vinculo_usuario_escola
            WHERE cd_usuario = :cd_usuario"
        );

        $stmt->execute([
            ':cd_usuario' => $idUsuario
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    public function escolaAtualPorPapeis(int $idUsuario, array $papeis): ?int
    {
        if (empty($papeis)) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($papeis), '?'));
        $stmt = $this->pdo->prepare("
            SELECT v.cd_escola
            FROM vinculo_usuario_escola v
            INNER JOIN papel p ON p.cd_papel = v.cd_papel
            WHERE v.cd_usuario = ?
              AND p.nm_papel IN ($placeholders)
              AND v.ativo = TRUE
            ORDER BY v.criado_em DESC
            LIMIT 1
        ");

        $stmt->execute(array_merge([$idUsuario], $papeis));
        $escola = $stmt->fetchColumn();

        return $escola === false ? null : (int) $escola;
    }

    public function vincularTimeEscola(int $idTime, int $idEscola): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO vinculo_time_escola (cd_time, cd_escola)
            VALUES (:time, :escola)
        ");

        $stmt->execute([
            ':time' => $idTime,
            ':escola' => $idEscola,
        ]);
    }

    // Verifica se o usuário é Diretor da escola (vínculo ativo)
    public function isUsuarioDiretor(int $idUsuario, int $idEscola): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1
            FROM vinculo_usuario_escola v
            INNER JOIN papel p ON p.cd_papel = v.cd_papel
            WHERE v.cd_usuario = :usuario
              AND v.cd_escola = :escola
              AND p.nm_papel = 'DIR'
              AND v.ativo = TRUE
            LIMIT 1"
        );

        $stmt->execute([
            ':usuario' => $idUsuario,
            ':escola' => $idEscola
        ]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res !== false && $res !== null;
    }

    // Verifica se o usuário possui algum dos papéis informados na escola (vínculo ativo)
    public function isUsuarioComPapeis(int $idUsuario, int $idEscola, array $papeis): bool
    {
        if (empty($papeis)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($papeis), '?'));

        $sql = "SELECT 1
            FROM vinculo_usuario_escola v
            INNER JOIN papel p ON p.cd_papel = v.cd_papel
            WHERE v.cd_usuario = ?
              AND v.cd_escola = ?
              AND p.nm_papel IN ($placeholders)
              AND v.ativo = TRUE
            LIMIT 1";

        $stmt = $this->pdo->prepare($sql);

        $params = array_merge([(int) $idUsuario, (int) $idEscola], $papeis);

        $stmt->execute($params);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res !== false && $res !== null;
    }

    public function usuarioPodeGerenciarEscola(int $idUsuario, int $idEscola): bool
    {
        $stmt = $this->pdo->prepare(
          "SELECT EXISTS (
              SELECT 1
              FROM usuario_papel up
              INNER JOIN papel p ON p.cd_papel = up.cd_papel
              WHERE up.cd_usuario = :admin_usuario
                AND p.nm_papel = 'ADM'
           )
           OR EXISTS (
                SELECT 1
                FROM vinculo_usuario_escola vue
                INNER JOIN papel p ON p.cd_papel = vue.cd_papel
                WHERE vue.cd_usuario = :diretor_usuario
                  AND vue.cd_escola = :diretor_escola
                  AND p.nm_papel = 'DIR'
                  AND vue.ativo = TRUE
            )
            OR EXISTS (
                SELECT 1
                FROM vinculo_tecnico_time vtt
                INNER JOIN vinculo_time_escola vte ON vte.cd_time = vtt.cd_time
                WHERE vtt.cd_usuario = :tecnico_usuario
                  AND vte.cd_escola = :tecnico_escola
                  AND vtt.ativo = TRUE
                  AND vte.ativo = TRUE
            )
            OR EXISTS (
                SELECT 1
                FROM vinculo_time_responsavel vtr
                INNER JOIN responsavel r ON r.cd_responsavel = vtr.cd_responsavel
                INNER JOIN vinculo_time_escola vte ON vte.cd_time = vtr.cd_time
                WHERE r.cd_usuario = :responsavel_usuario
                  AND vte.cd_escola = :responsavel_escola
                  AND r.ativo = TRUE
                  AND vtr.ativo = TRUE
                  AND vte.ativo = TRUE
            ) AS permitido"
        );

        $stmt->execute([
            ':admin_usuario' => $idUsuario,
            ':diretor_usuario' => $idUsuario,
            ':diretor_escola' => $idEscola,
            ':tecnico_usuario' => $idUsuario,
            ':tecnico_escola' => $idEscola,
            ':responsavel_usuario' => $idUsuario,
            ':responsavel_escola' => $idEscola,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function escolaGerenciavelPorUsuario(int $idUsuario): ?int
    {
        $stmt = $this->pdo->prepare(
            "SELECT escola.cd_escola
            FROM (
                SELECT vue.cd_escola
                FROM vinculo_usuario_escola vue
                INNER JOIN papel p ON p.cd_papel = vue.cd_papel
                WHERE vue.cd_usuario = :usuario_diretor
                  AND p.nm_papel = 'DIR'
                  AND vue.ativo = TRUE
                UNION
                SELECT vte.cd_escola
                FROM vinculo_tecnico_time vtt
                INNER JOIN vinculo_time_escola vte ON vte.cd_time = vtt.cd_time
                WHERE vtt.cd_usuario = :usuario_tecnico
                  AND vtt.ativo = TRUE
                  AND vte.ativo = TRUE
                UNION
                SELECT vte.cd_escola
                FROM vinculo_time_responsavel vtr
                INNER JOIN responsavel r ON r.cd_responsavel = vtr.cd_responsavel
                INNER JOIN vinculo_time_escola vte ON vte.cd_time = vtr.cd_time
                WHERE r.cd_usuario = :usuario_responsavel
                  AND r.ativo = TRUE
                  AND vtr.ativo = TRUE
                  AND vte.ativo = TRUE
            ) escola
            LIMIT 1"
        );

        $stmt->execute([
            ':usuario_diretor' => $idUsuario,
            ':usuario_tecnico' => $idUsuario,
            ':usuario_responsavel' => $idUsuario,
        ]);

        $escola = $stmt->fetchColumn();
        return $escola === false ? null : (int) $escola;
    }
}
