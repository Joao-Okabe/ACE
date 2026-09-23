<?php

class Competicao extends Model
{

    private Filtro $filtro;

    public function __construct()
    {
        parent::__construct();
        $this->filtro = new Filtro();
    }

    //Cria uma COMPETIÇÃO nova
    public function criar(array $dados, int $id):void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO competicao(
                nm_competicao,
                cd_criador,
                cd_formato,
                cd_esporte,
                cd_modalidade,
                inicio_em,
                fim_em
            )
            VALUES(
                :nm_competicao,
                :cd_criador,
                :cd_formato,
                :cd_esporte,
                :cd_modalidade,
                :inicio_em,
                :fim_em
            )
        ");

        $stmt->execute([
            ":nm_competicao" => $dados["nm_competicao"],
            ":cd_criador" => $id,
            ":cd_formato" => $dados["cd_formato"],
            ":cd_esporte" => $dados["cd_esporte"],
            ":cd_modalidade" => $dados["cd_modalidade"],
            ":inicio_em" => $dados["inicio_em"] ?? null,
            ":fim_em" => $dados["fim_em"] ?? null
        ]);
    }

    // Busca competições
    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM competicao
            WHERE cd_competicao = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);

        $competicao = $stmt->fetch(PDO::FETCH_ASSOC);

        return $competicao ?: null;
    }

    //Lista COMPETIÇÕES
    public function listar(array $filtros): array
    {
        $stmt = "
            SELECT
                c.cd_competicao,
                c.cd_criador,
                c.nm_competicao,
                c.criado_em,
                c.inicio_em,
                c.fim_em,
                c.inicio_em AS dt_inicio,
                c.fim_em AS dt_encerramento
            FROM competicao c";

        $filtrosSql = $this->filtro->filtrosCompeticao($filtros);

        if (!empty($filtrosSql['onde'])) {
            $stmt .= ' WHERE ' . implode(' AND ', $filtrosSql['onde']);
        }

        $ordem = $this->filtro->ordem($filtros);
        $stmt .= " ORDER BY c.cd_competicao {$ordem}";

        $stmt = $this->pdo->prepare($stmt);
        $stmt->execute($filtrosSql['parametros']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function atualizar(int $id, array $dados): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE competicao
            SET nm_competicao = :nm_competicao,
                inicio_em = :inicio_em,
                fim_em = :fim_em
            WHERE cd_competicao = :id
        ');

        $stmt->execute([
            ':id' => $id,
            ':nm_competicao' => $dados['nm_competicao'],
            ':inicio_em' => $dados['inicio_em'] ?? null,
            ':fim_em' => $dados['fim_em'] ?? null,
        ]);
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
            WHERE inicio_em = :inicio
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

    public function remover(int $id)
    {
        $stmt = $this->pdo->prepare("
        DELETE FROM competicao 
        WHERE cd_competicao = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);
    }
}