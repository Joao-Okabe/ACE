<?php

class Competicao extends Model
{
    //Cria uma COMPETIÇÃO nova
    public function criar(array $dados, int $id):void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO competicao(
                nm_competicao,
                cd_criador,
                dt_inicio,
                dt_encerramento
            )
            VALUES(
                :nm_competicao,
                :cd_criador,
                :dt_inicio,
                :dt_encerramento
            )
        ");

        $stmt->execute([
            ":nm_competicao" => $dados["nm_competicao"],
            ":dt_inicio" => $dados["dt_inicio"],
            ":dt_encerramento" => $dados["dt_encerramento"]
        ]);
    }

    //Lista COMPETIÇÕES
    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                cd_competicao,
                nm_competicao,
                criado_em,
                dt_inicio,
                dt_encerramento,
            FROM competicao
            ORDER BY cd_competicao
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //FILTRAGENS respectivamente
    /*
        Por nome da competição
        Por data de inicio da competição
        Por usuário que participou da competição 
        Por criador da competição
    */

    public function listarNome(string $nome): array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM competicao
            WHERE nm_competicao = :nome
        ");

        $stmt->execute([
            ':nome' => $nome
        ]);

        $competicao = $stmt->fetch(PDO::FETCH_ASSOC);

        return $competicao ?: null;
    }

    public function listarDataInicio(string $dt_inicio): array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM competicao
            WHERE dt_inicio = :inicio
        ");

        $stmt->execute([
            ':inicio' => $dt_inicio
        ]);

        $competicao = $stmt->fetch(PDO::FETCH_ASSOC);

        return $competicao ?: null;
    }

    public function listarParticipacao(int $id_user)
    {
        // Vai ter que chamar por partida que o usuário participou
    }

    public function listarCriador(string $criador): array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM competicao
            WHERE cd_criador = :cd_criador
        ");

        $stmt->execute([
            ':cd_criador' => $criador
        ]);

        $competicao = $stmt->fetch(PDO::FETCH_ASSOC);

        return $competicao ?: null;
    }
}