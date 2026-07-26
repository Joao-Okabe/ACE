<?php

class Usuario extends Model
{

    /**
     * Cadastra um novo usuário
     *
     * @param array $dados
     * @return int ID do usuário criado
     */
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

    /**
     * Vincula um papel ao usuário
     */
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

    /**
     * Busca um papel pelo nome
     */
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

    /**
     * Busca um usuário pelo e-mail
     */
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

    /**
     * Atualiza a senha
     */
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

    /**
     * Remove um usuário
     */
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
