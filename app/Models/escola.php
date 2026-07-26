<?php

class Escola extends Model
{

    /**
     * Lista todas as escolas
     */
    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT *
            FROM escola
            ORDER BY nome
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca uma escola
     */
    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM escola
            WHERE cd_escola = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);

        $escola = $stmt->fetch(PDO::FETCH_ASSOC);

        return $escola ?: null;
    }

    /**
     * Cadastra uma escola
     */
    public function cadastrar(array $dados): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO escola
            (
                cd_usuario,
                nome,
                telefone,
                cep,
                logradouro,
                numero,
                bairro,
                cidade,
                uf
            )
            VALUES
            (
                :usuario,
                :nome,
                :telefone,
                :cep,
                :logradouro,
                :numero,
                :bairro,
                :cidade,
                :uf
            )
        ");

        $stmt->execute([
            ':usuario'    => $dados['usuario'],
            ':nome'       => $dados['nome'],
            ':telefone'   => $dados['telefone'],
            ':cep'        => $dados['cep'],
            ':logradouro' => $dados['logradouro'],
            ':numero'     => $dados['numero'],
            ':bairro'     => $dados['bairro'],
            ':cidade'     => $dados['cidade'],
            ':uf'         => $dados['uf']
        ]);
    }

    /**
     * Atualiza uma escola
     */
    public function atualizar(int $id, array $dados): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE escola
            SET
                nome = :nome,
                telefone = :telefone,
                cep = :cep,
                logradouro = :logradouro,
                numero = :numero,
                bairro = :bairro,
                cidade = :cidade,
                uf = :uf
            WHERE cd_escola = :id
        ");

        $stmt->execute([
            ':nome'       => $dados['nome'],
            ':telefone'   => $dados['telefone'],
            ':cep'        => $dados['cep'],
            ':logradouro' => $dados['logradouro'],
            ':numero'     => $dados['numero'],
            ':bairro'     => $dados['bairro'],
            ':cidade'     => $dados['cidade'],
            ':uf'         => $dados['uf'],
            ':id'         => $id
        ]);
    }

    /**
     * Remove uma escola
     */
    public function remover(int $id): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM escola
            WHERE cd_escola = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);
    }
}