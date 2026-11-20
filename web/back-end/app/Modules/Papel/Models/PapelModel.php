<?php

class Papel extends Model 
{
    public function vincularPapel(int $idUsuario, int $idPapel)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuario_papel
            (
                cd_usuario,
                cd_papel
            )
            VALUES
            (
                :usuario,
                :papel
            )
        ");

        $stmt->execute([
            ':usuario' => $idUsuario,
            ':papel' => $idPapel
        ]);
    }

    //Lista Papéis
    public function listarPapeis(int $idUsuario): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.nm_papel AS nome
            FROM papel p
            INNER JOIN vinculo_usuario_escola v
                ON v.cd_papel = p.cd_papel
            WHERE v.cd_usuario = :usuario
            AND v.ativo = TRUE

            UNION

            SELECT p.nm_papel AS nome
            FROM papel p
            INNER JOIN usuario_papel up
                ON up.cd_papel = p.cd_papel
            WHERE up.cd_usuario = :usuario_global
            AND up.ativo = TRUE
            ORDER BY nome
        ");

        $stmt->execute([
            ':usuario' => $idUsuario,
            ':usuario_global' => $idUsuario
        ]);

        return array_column(
            $stmt->fetchAll(PDO::FETCH_ASSOC),
            'nome'
        );
    }

    //Busca papel por nome
    public function buscarPapelPorNome(string $nome): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM papel
            WHERE nm_papel = :nome
        ");

        $stmt->execute([
            ':nome' => $nome
        ]);

        $papel = $stmt->fetch(PDO::FETCH_ASSOC);

        return $papel ?: null;
    }
}