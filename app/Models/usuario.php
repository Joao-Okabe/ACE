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
                senha,
                cd_papel
            )
            VALUES
            (
                :email,
                :senha,
                :papel
            )
            RETURNING cd_usuario
        ");

        $stmt->execute([
            ':email' => $dados['email'],
            ':senha' => $dados['senha'],
            ':papel' => $dados['papel']
        ]);

        return (int) $stmt->fetchColumn();
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