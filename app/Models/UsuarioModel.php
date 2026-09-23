<?php

class Usuario extends Model
{
    // Cadastra Usuário e retorna o ID para auth
    public function cadastrar(array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuario
            (
                nm_usuario,
                email,
                senha,
                path_ft_usuario
            )
            VALUES
            (
                :nm_usuario,
                :email,
                :senha,
                :foto_perfil
            )
            RETURNING cd_usuario
        ");

        $stmt->execute([
            ':nm_usuario' => $dados['nm_usuario'],
            ':email' => $dados['email'],
            ':senha' => $dados['senha'],
            ':foto_perfil' => $dados['foto_perfil'] ?? null,
        ]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res === false || !isset($res['cd_usuario'])) {
            throw new Exception('Não foi possível cadastrar o usuário.');
        }

        return (int) $res['cd_usuario'];
    }

    //Busca usuario pelo email
    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                u.*,
                u.path_ft_usuario AS foto_perfil
            FROM usuario u
            WHERE u.email = :email
        ");

        $stmt->execute([
            ':email' => $email
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
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

    //Atualiza foto de perfil
    public function atualizarFotoPerfil(int $id, ?string $fotoPerfil): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuario
            SET path_ft_usuario = :foto_perfil
            WHERE cd_usuario = :id
        ");

        $stmt->execute([
            ':foto_perfil' => $fotoPerfil,
            ':id' => $id
        ]);
    }

    public function atualizarNome(int $id, string $nome): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuario
            SET nm_usuario = :nome
            WHERE cd_usuario = :id
        ");

        $stmt->execute([
            ':nome' => $nome,
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