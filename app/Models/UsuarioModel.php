<?php

class Usuario extends Model
{

    // Cadastra Usuário e retorna o ID para auth
    public function cadastrar(array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuario
            (
                email,
                senha
            )
            VALUES
            (
                :email,
                :senha
            )
            RETURNING cd_usuario
        ");

        $stmt->execute([
            ':email' => $dados['email'],
            ':senha' => $dados['senha']
        ]);

        return (int) $stmt->fetchColumn();
    }

    //Vincula Papel do usuário
    public function vincularPapel(int $idUsuario, int $idPapel): void
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

    //Busca usuario pelo email
    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM usuario
            WHERE email = :email
        ");

        $stmt->execute([
            ':email' => $email
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    //Lista Papéis
    public function listarPapeis(int $idUsuario): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.nome
            FROM papel p
            INNER JOIN usuario_papel up
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

    //Atualiza Senha
    public function atualizarSenha(int $id, string $senha): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuario
            SET senha = :senha
            WHERE cd_usuario = :id
        ");

        $stmt->execute([
            ':senha' => $senha,
            ':id' => $id
        ]);
    }

    //Remove usuario
    public function remover(int $id): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM usuario
            WHERE cd_usuario = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);
    }
}