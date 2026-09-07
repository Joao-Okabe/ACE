<?php

class Auth extends Model 
{
    public function verificaPapelUsuario(int $id){
        $stmt = $this->pdo->prepare("
                SELECT 1
                FROM vinculo_usuario_escola
                WHERE cd_usuario = :cd_usuario_escola
                    AND ativo = TRUE

                UNION ALL

                SELECT 1
                FROM usuario_papel
                WHERE cd_usuario = :cd_usuario_global
                    AND ativo = TRUE

                LIMIT 1
        ");

        $stmt->execute([
            ':cd_usuario_escola' => $id,
            ':cd_usuario_global' => $id
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario === false) {
            throw new Exception("Usuário sem papel.");
        }
    }
}