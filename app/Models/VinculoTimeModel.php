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

}