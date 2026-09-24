<?php

class VinculoTime extends Model{

    public function vincularTimeResponsavel(
        int $idResponsavel,
        int $idTime
    )
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO vinculo_time_responsavel(
                cd_responsavel,
                cd_time
            ) VALUES (
                :cd_responsavel,
                :cd_time
            )"
        );

        $stmt->execute([
            ':cd_responsavel' => $idResponsavel,
            ':cd_time' => $idTime
        ]);
    }

    public function listarResponsaveis(): array
    {
        $stmt = $this->pdo->query(
            "SELECT
                r.cd_responsavel,
                u.nm_usuario
            FROM responsavel r
            INNER JOIN usuario u
                ON u.cd_usuario = r.cd_usuario
            WHERE r.ativo = TRUE
            ORDER BY u.nm_usuario"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarUsuarios(): array
    {
        $stmt = $this->pdo->query(
            "SELECT cd_usuario, nm_usuario, email
            FROM usuario
            WHERE ativo = TRUE
            ORDER BY nm_usuario"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function usuarioPodeGerenciarTime(int $idUsuario, int $idTime): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT EXISTS (
                SELECT 1
                FROM vinculo_tecnico_time vt
                WHERE vt.cd_usuario = :usuario_tecnico
                  AND vt.cd_time = :time_tecnico
                  AND vt.ativo = TRUE
            )
            OR EXISTS (
                SELECT 1
                FROM vinculo_time_responsavel vtr
                INNER JOIN responsavel r
                    ON r.cd_responsavel = vtr.cd_responsavel
                WHERE r.cd_usuario = :usuario_responsavel
                  AND vtr.cd_time = :time_responsavel
                  AND r.ativo = TRUE
                  AND vtr.ativo = TRUE
            )
            OR EXISTS (
                SELECT 1
                FROM vinculo_time_escola vte
                INNER JOIN vinculo_usuario_escola vue
                    ON vue.cd_escola = vte.cd_escola
                INNER JOIN papel p
                    ON p.cd_papel = vue.cd_papel
                WHERE vte.cd_time = :time_diretor
                  AND vue.cd_usuario = :usuario_diretor
                  AND p.nm_papel = 'DIR'
                  AND vte.ativo = TRUE
                  AND vue.ativo = TRUE
            ) AS permitido"
        );

        $stmt->execute([
            ':usuario_tecnico' => $idUsuario,
            ':time_tecnico' => $idTime,
            ':usuario_responsavel' => $idUsuario,
            ':time_responsavel' => $idTime,
            ':time_diretor' => $idTime,
            ':usuario_diretor' => $idUsuario,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function vincularTimeIntegrante(
        int $idUsuario,
        int $idTime,
        int $idFuncaoIntegrante,
        ?int $numeroCamisa = null,
        bool $capitao = false
    )
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO vinculo_time_integrante(
                cd_usuario,
                cd_time,
                cd_funcao_integrante,
                numero_camisa,
                capitao
            ) VALUES (
                :usuario,
                :cd_time,
                :cd_funcao_integrante,
                :numero_camisa,
                :capitao
            )"
        );

        $stmt->execute([
            ':usuario' => $idUsuario,
            ':cd_time' => $idTime,
            ':cd_funcao_integrante' => $idFuncaoIntegrante,
            ':numero_camisa' => $numeroCamisa,
            ':capitao' => $capitao
        ]);
    }

    public function removerTimeIntegrante(int $idUsuario, int $idTime): void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM vinculo_time_integrante
            WHERE cd_usuario = :cd_usuario
              AND cd_time = :cd_time"
        );

        $stmt->execute([
            ':cd_usuario' => $idUsuario,
            ':cd_time' => $idTime
        ]);
    }

    public function tornarCapitao(int $idUsuario, int $idTime): void
    {
        // Bloqueia o time durante a verificação e a atualização para evitar duas nomeações simultâneas.
        $stmt = $this->pdo->prepare('SELECT cd_time FROM time WHERE cd_time = :cd_time FOR UPDATE');
        $stmt->execute([':cd_time' => $idTime]);
        if (!$stmt->fetchColumn()) {
            throw new Exception('Time não encontrado.');
        }

        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM vinculo_time_integrante WHERE cd_usuario = :cd_usuario AND cd_time = :cd_time AND ativo = TRUE'
        );
        $stmt->execute([':cd_usuario' => $idUsuario, ':cd_time' => $idTime]);
        if (!$stmt->fetchColumn()) {
            throw new Exception('Integrante não encontrado neste time.');
        }

        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM vinculo_time_integrante WHERE cd_time = :cd_time AND ativo = TRUE AND capitao = TRUE LIMIT 1'
        );
        $stmt->execute([':cd_time' => $idTime]);
        if ($stmt->fetchColumn()) {
            throw new Exception('O time já possui um capitão.');
        }

        $stmt = $this->pdo->prepare(
            'UPDATE vinculo_time_integrante SET capitao = TRUE
             WHERE cd_usuario = :cd_usuario AND cd_time = :cd_time AND ativo = TRUE'
        );
        $stmt->execute([':cd_usuario' => $idUsuario, ':cd_time' => $idTime]);
    }

    public function removerCapitao(int $idUsuario, int $idTime): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE vinculo_time_integrante SET capitao = FALSE
             WHERE cd_usuario = :cd_usuario AND cd_time = :cd_time AND ativo = TRUE AND capitao = TRUE'
        );
        $stmt->execute([':cd_usuario' => $idUsuario, ':cd_time' => $idTime]);
        if ($stmt->rowCount() === 0) {
            throw new Exception('Capitão não encontrado neste time.');
        }
    }

    public function listarResponsaveisTime(int $idTime): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                r.cd_responsavel,
                r.cd_usuario,
                u.nm_usuario,
                u.path_ft_usuario AS foto_perfil
            FROM responsavel r
            INNER JOIN usuario u
                ON u.cd_usuario = r.cd_usuario
            INNER JOIN vinculo_time_responsavel vtr
                ON vtr.cd_responsavel = r.cd_responsavel
            WHERE r.ativo = TRUE
                AND vtr.ativo = TRUE
                AND vtr.cd_time = :cd_time
            ORDER BY u.nm_usuario"
        );

        $stmt->execute([':cd_time' => $idTime]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarIntegrantesTime(int $idTime): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                vti.cd_usuario,
                vti.cd_time,
                vti.cd_funcao_integrante,
                vti.numero_camisa,
                vti.capitao,
                u.nm_usuario,
                u.path_ft_usuario AS foto_perfil,
                fi.nm_funcao AS nm_funcao_integrante
            FROM vinculo_time_integrante vti
            INNER JOIN usuario u
                ON u.cd_usuario = vti.cd_usuario
            INNER JOIN funcao_integrante fi
                ON fi.cd_funcao_integrante = vti.cd_funcao_integrante
            WHERE vti.ativo = TRUE
                AND vti.cd_time = :cd_time
            ORDER BY u.nm_usuario"
        );

        $stmt->execute([':cd_time' => $idTime]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAlunosTime(int $idTime): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT
                a.cd_aluno,
                a.cd_usuario,
                a.ra,
                u.nm_usuario,
                u.path_ft_usuario AS foto_perfil
            FROM aluno a
            INNER JOIN usuario u
                ON u.cd_usuario = a.cd_usuario
            INNER JOIN vinculo_usuario_escola vue
                ON vue.cd_usuario = a.cd_usuario
            INNER JOIN vinculo_time_escola vte
                ON vte.cd_escola = vue.cd_escola
            WHERE vte.cd_time = :cd_time
                AND vte.ativo = TRUE
                AND vue.ativo = TRUE
                AND a.ativo = TRUE
                AND u.ativo = TRUE
            ORDER BY u.nm_usuario"
        );

        $stmt->execute([':cd_time' => $idTime]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarFuncoesIntegranteTime(int $idTime): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                fi.cd_funcao_integrante,
                fi.nm_funcao,
                fi.ds_funcao
            FROM time t
            INNER JOIN funcao_integrante fi
                ON fi.cd_esporte = t.cd_esporte
            WHERE t.cd_time = :cd_time
            ORDER BY fi.nm_funcao"
        );

        $stmt->execute([':cd_time' => $idTime]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tornarTitular (int $idIntegrante) 
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO escalacao_time (
                cd_vinculo_time_integrante,
                titular
            ) VALUES (
                :cd_vinculo_time_integrante,
                :titular
            )"
        );

        ($stmt->execute([
            ':cd_vinculo_time_integrante' => $idIntegrante,
            ':titular' => true
        ]));
    }

    public function vincularTecnicoTime (int $idUsuario, int $idTime ) 
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO vinculo_tecnico_time (
                cd_usuario,
                cd_time
            ) VALUES (
                :cd_usuario,
                :cd_time
            )"
        );

        ($stmt->execute([
            ':cd_usuario' => $idUsuario,
            ':cd_time' => $idTime
        ]));
    }

    public function listarTecnicosTime(int $idTime): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                t.cd_usuario,
                u.nm_usuario,
                u.path_ft_usuario
            FROM tecnico t
            INNER JOIN usuario u
                ON u.cd_usuario = t.cd_usuario
            INNER JOIN vinculo_tecnico_time vtt
                ON vtt.cd_usuario = u.cd_usuario
            WHERE r.ativo = TRUE
                AND vtt.ativo = TRUE
                AND vtt.cd_time = :cd_time
            ORDER BY u.nm_usuario"
        );

        $stmt->execute([':cd_time' => $idTime]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}