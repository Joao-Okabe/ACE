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

        $possuiPapel = $stmt->fetchColumn();

        if ($possuiPapel === false) {
            // Garante que o usuário exista antes de vincular o papel padrão (VIS).
            $stmt = $this->pdo->prepare(
                "SELECT 1 FROM usuario WHERE cd_usuario = :id"
            );
            $stmt->execute([':id' => $id]);

            if ($stmt->fetchColumn() === false) {
                return;
            }

            $papelVisitante = $this->pdo->prepare(
                "SELECT cd_papel FROM papel WHERE nm_papel = 'VIS'"
            );
            $papelVisitante->execute();
            $cdPapel = $papelVisitante->fetchColumn();

            if ($cdPapel === false) {
                throw new Exception('O papel de visitante não está cadastrado.');
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO usuario_papel(
                    cd_usuario,
                    cd_papel
                ) VALUES (
                    :cd_usuario_global,
                    :cd_papel
                )
            ");

            $stmt->execute([
                ':cd_usuario_global' => $id,
                ':cd_papel' => (int) $cdPapel
            ]);
        }
    }
}