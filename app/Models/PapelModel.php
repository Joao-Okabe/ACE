<?php

class Papel extends Model 
{
    //Lista Papéis
    public function listarPapeis(int $idUsuario): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.nome
            FROM papel p
            INNER JOIN vinculo_usuario_escola up
                ON up.cd_papel = p.cd_papel
            WHERE up.cd_usuario = :usuario
            ORDER BY p.nome
        ");

        $stmt->execute([
            ':usuario' => $idUsuario
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
            WHERE nome = :nome
        ");

        $stmt->execute([
            ':nome' => $nome
        ]);

        $papel = $stmt->fetch(PDO::FETCH_ASSOC);

        return $papel ?: null;
    }
}