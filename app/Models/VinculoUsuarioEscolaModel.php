<?php

class VinculoUsuarioEscola extends Model
{
    //Vincula Papel do usuário
    public function vincularPapel(int $idUsuario, int $idEscola, int $idPapel): void
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
}