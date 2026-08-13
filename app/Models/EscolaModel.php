<?php

class Escola extends Model
{
    // Cadastro de escola
    public function cadastrar(array $dados): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO escola
            (
                nome,
                telefone,
                cep,
                numero,
                categoria_administrativa,
                img_logo
            )
            VALUES
            (
                :nome,
                :telefone,
                :cep,
                :numero,
                :categoria,
                :img_logo
            )
        ");

        $stmt->execute([
            ':nome' => $dados['nome'],
            ':telefone' => $dados['telefone'],
            ':cep' => $dados['cep'],
            ':numero' => $dados['numero'],
            ':categoria' => $dados['categoria_administrativa'],
            ':img_logo' => $dados['img_logo'] ?? null
        ]);
    }

    //Atualiza Escola
    public function atualizar(int $id, array $dados): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE escola
            SET
                nome = :nome,
                telefone = :telefone,
                cep = :cep,
                numero = :numero,
                categoria_administrativa = :categoria,
                img_logo = :img_logo
            WHERE cd_escola = :id
        ");

        $stmt->execute([
            ':nome' => $dados['nome'],
            ':telefone' => $dados['telefone'],
            ':cep' => $dados['cep'],
            ':numero' => $dados['numero'],
            ':categoria' => $dados['categoria_administrativa'],
            ':img_logo' => $dados['img_logo'] ?? null,
            ':id' => $id
        ]);
    }

    //Lista Escolas
    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                cd_escola,
                nome,
                telefone,
                cep,
                numero,
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


    //Remove Escola
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

    // Define o status ativo/inativo da escola
    public function setAtiva(int $id, bool $ativa): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE escola
            SET ativa = :ativa
            WHERE cd_escola = :id"
        );

        $stmt->execute([
            ':ativa' => $ativa,
            ':id' => $id
        ]);
    }
}
