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

    // Verifica se o usuário é Diretor da escola (vínculo ativo)
    public function isUsuarioDiretor(int $idUsuario, int $idEscola): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1
            FROM vinculo_usuario_escola v
            INNER JOIN papel p ON p.cd_papel = v.cd_papel
            WHERE v.cd_usuario = :usuario
              AND v.cd_escola = :escola
              AND p.nome = 'DIRETOR'
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

    }