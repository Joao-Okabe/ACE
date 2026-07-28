<?php

class Escola extends Model
{

    //Lista Escolas
    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                cd_escola,
                cd_usuario,
                nome,
                telefone,
                cep,
                logradouro,
                numero,
                bairro,
                cidade,
                uf,
                categoria_administrativa,
                img_logo,
                criada_em,
                ativa
            FROM escola
            ORDER BY nome
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Busca Escola
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
                uf,
                categoria_administrativa,
                img_logo
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
                :uf,
                :categoria,
                :img_logo
            )
        ");

        $stmt->execute([
            ':usuario' => $dados['usuario'],
            ':nome' => $dados['nome'],
            ':telefone' => $dados['telefone'],
            ':cep' => $dados['cep'],
            ':logradouro' => $dados['logradouro'],
            ':numero' => $dados['numero'],
            ':bairro' => $dados['bairro'],
            ':cidade' => $dados['cidade'],
            ':uf' => $dados['uf'],
            ':categoria' => $dados['categoria_administrativa'],
            ':img_logo' => $dados['img_logo'] ?? null
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
                uf = :uf,
                categoria_administrativa = :categoria,
                img_logo = :img_logo
            WHERE cd_escola = :id
        ");

        $stmt->execute([
            ':nome' => $dados['nome'],
            ':telefone' => $dados['telefone'],
            ':cep' => $dados['cep'],
            ':logradouro' => $dados['logradouro'],
            ':numero' => $dados['numero'],
            ':bairro' => $dados['bairro'],
            ':cidade' => $dados['cidade'],
            ':uf' => $dados['uf'],
            ':categoria' => $dados['categoria_administrativa'],
            ':img_logo' => $dados['img_logo'] ?? null,
            ':id' => $id
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
