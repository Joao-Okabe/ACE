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
        int $idFuncaoIntegrante
    )
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO vinculo_time_integrante(
                cd_usuario,
                cd_time,
                cd_funcao_integrante
            ) VALUES (
                :usuario,
                :cd_time,
                :cd_funcao_integrante
            )"
        );

        $stmt->execute([
            ':usuario' => $idUsuario,
            ':cd_time' => $idTime,
            ':cd_funcao_integrante' => $idFuncaoIntegrante
        ]);
    }

}