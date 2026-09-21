<?php

class VinculoTimeIntegrante extends Model{

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