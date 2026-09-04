<?php

class Auth extends Model 
{
    public function verificaPapelUsuario(int $id){
        $stmt = $this->pdo->prepare("
        SELECT *
        FROM vinculo_usuario_escola
                WHERE cd_usuario = :cd_usuario
                    AND ativo = TRUE
                LIMIT 1
        ");

        $stmt->execute([
            ':cd_usuario' => $id
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario === false) {
            throw new Exception("Usuário sem papel.");
        }
    }
}