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
                cd_escola,
                inicio_em,
                fim_em
            )
            VALUES(
                :nm_competicao,
                :cd_criador,
                :cd_formato,
                :cd_esporte,
                :cd_modalidade,
                :cd_escola,
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
            ":cd_escola" => $dados["cd_escola"],
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

    public function usuarioPodeVisualizar(int $idUsuario, int $idCompeticao): bool
    {
        $stmt = $this->pdo->prepare("SELECT EXISTS (
            SELECT 1 FROM competicao c
            WHERE c.cd_competicao = :competicao
              AND (
                c.visibilidade = 'PUBLICA'
                OR c.cd_criador = :criador
                OR EXISTS (
                    SELECT 1 FROM participante_competicao pc
                    WHERE pc.cd_competicao = c.cd_competicao
                      AND pc.cd_usuario = :participante
                )
                OR EXISTS (
                    SELECT 1 FROM arbitro_competicao ac
                    WHERE ac.cd_competicao = c.cd_competicao
                      AND ac.cd_usuario = :arbitro
                )
                OR EXISTS (
                    SELECT 1
                    FROM usuario_papel up
                    JOIN papel p ON p.cd_papel = up.cd_papel
                    WHERE up.cd_usuario = :administrador
                      AND up.ativo = TRUE
                      AND p.nm_papel = 'ADM'
                )
                OR EXISTS (
                    SELECT 1
                    FROM vinculo_usuario_escola vue
                    JOIN papel p ON p.cd_papel = vue.cd_papel
                    WHERE vue.cd_usuario = :diretor
                      AND vue.cd_escola = c.cd_escola
                      AND vue.ativo = TRUE
                      AND p.nm_papel = 'DIR'
                )
              )
        )");
        $stmt->execute([
            ':competicao' => $idCompeticao,
            ':criador' => $idUsuario,
            ':participante' => $idUsuario,
            ':arbitro' => $idUsuario,
            ':administrador' => $idUsuario,
            ':diretor' => $idUsuario,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function usuarioEhArbitro(int $idUsuario, int $idCompeticao): bool
    {
        $stmt = $this->pdo->prepare('SELECT EXISTS (SELECT 1 FROM arbitro_competicao WHERE cd_usuario = :usuario AND cd_competicao = :competicao)');
        $stmt->execute([':usuario' => $idUsuario, ':competicao' => $idCompeticao]);
        return (bool) $stmt->fetchColumn();
    }

    //Lista COMPETIÇÕES
    public function listar(array $filtros): array
    {
        $sql = "
            SELECT
                c.cd_competicao,
                c.cd_criador,
                c.nm_competicao,
                c.cd_modalidade,
                c.status,
                c.visibilidade,
                c.criado_em,
                c.inicio_em,
                c.fim_em,
                c.inicio_em AS dt_inicio,
                c.fim_em AS dt_encerramento
            FROM competicao c";

        $filtrosSql = $this->filtro->filtrosCompeticao($filtros);

        if (isset($filtros['usuario'])) {
            $filtrosSql['onde'][] = "(
                c.visibilidade = 'PUBLICA'
                OR c.cd_criador = :usuario_visibilidade
                OR EXISTS (
                    SELECT 1 FROM participante_competicao pc
                    WHERE pc.cd_competicao = c.cd_competicao
                      AND pc.cd_usuario = :usuario_participante
                )
                OR EXISTS (
                    SELECT 1 FROM arbitro_competicao ac
                    WHERE ac.cd_competicao = c.cd_competicao
                      AND ac.cd_usuario = :usuario_arbitro
                )
                OR EXISTS (
                    SELECT 1
                    FROM usuario_papel up
                    JOIN papel p ON p.cd_papel = up.cd_papel
                    WHERE up.cd_usuario = :usuario_administrador
                      AND up.ativo = TRUE
                      AND p.nm_papel = 'ADM'
                )
                OR EXISTS (
                    SELECT 1
                    FROM vinculo_usuario_escola vue
                    JOIN papel p ON p.cd_papel = vue.cd_papel
                    WHERE vue.cd_usuario = :usuario_diretor
                      AND vue.cd_escola = c.cd_escola
                      AND vue.ativo = TRUE
                      AND p.nm_papel = 'DIR'
                )
            )";
            $filtrosSql['parametros'][':usuario_visibilidade'] = (int) $filtros['usuario'];
            $filtrosSql['parametros'][':usuario_participante'] = (int) $filtros['usuario'];
            $filtrosSql['parametros'][':usuario_arbitro'] = (int) $filtros['usuario'];
            $filtrosSql['parametros'][':usuario_administrador'] = (int) $filtros['usuario'];
            $filtrosSql['parametros'][':usuario_diretor'] = (int) $filtros['usuario'];
        }

        if (!empty($filtros['status'])) {
            $filtrosSql['onde'][] = 'c.status = :status_competicao';
            $filtrosSql['parametros'][':status_competicao'] = $filtros['status'];
        }
        if (!empty($filtros['modalidade_id'])) {
            $filtrosSql['onde'][] = 'c.cd_modalidade = :modalidade_competicao';
            $filtrosSql['parametros'][':modalidade_competicao'] = (int) $filtros['modalidade_id'];
        }
        if (!empty($filtros['de'])) {
            $filtrosSql['onde'][] = '(c.fim_em IS NULL OR c.fim_em >= :de_competicao)';
            $filtrosSql['parametros'][':de_competicao'] = $filtros['de'];
        }
        if (!empty($filtros['ate'])) {
            $filtrosSql['onde'][] = '(c.inicio_em IS NULL OR c.inicio_em <= :ate_competicao)';
            $filtrosSql['parametros'][':ate_competicao'] = $filtros['ate'];
        }

        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));
        $porPagina = min(100, max(1, (int) ($filtros['per_page'] ?? 30)));
        $sql .= !empty($filtrosSql['onde'])
            ? ' WHERE ' . implode(' AND ', $filtrosSql['onde'])
            : '';
        $ordem = $this->filtro->ordem($filtros);
        $sql .= " ORDER BY c.cd_competicao {$ordem}";

        if (!empty($filtros['paginado'])) {
            $sql .= ' LIMIT :limite OFFSET :deslocamento';
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($filtrosSql['parametros'] as $chave => $valor) {
            $stmt->bindValue($chave, $valor);
        }
        if (!empty($filtros['paginado'])) {
            $stmt->bindValue(':limite', $porPagina + 1, PDO::PARAM_INT);
            $stmt->bindValue(':deslocamento', ($pagina - 1) * $porPagina, PDO::PARAM_INT);
        }
        $stmt->execute();

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

    public function listarNome(string $nome): ?array
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

    public function listarDataInicio(string $dt_inicio): ?array
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

    public function listarCriador(string $criador): ?array
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

    public function criarInscricao(int $idCompeticao, array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO inscricao_competicao(
                cd_competicao,
                dt_inicio_inscricao,
                dt_encerramento_inscricao
            )
            VALUES(
                :cd_competicao,
                :dt_inicio_inscricao,
                :dt_encerramento_inscricao
            )
            RETURNING cd_inscricao_competicao
        ");

        $stmt->execute([
            ":cd_competicao" => $idCompeticao,
            ":dt_inicio_inscricao" => $dados["dt_inicio_inscricao"],
            ":dt_encerramento_inscricao" => $dados["dt_encerramento_inscricao"],
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function buscarPeriodoInscricao(int $idCompeticao): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM inscricao_competicao
            WHERE cd_competicao = :competicao
            ORDER BY cd_inscricao_competicao DESC
            LIMIT 1
        ");

        $stmt->execute([':competicao' => $idCompeticao]);
        $inscricao = $stmt->fetch(PDO::FETCH_ASSOC);

        return $inscricao ?: null;
    }

    public function listarTimesInscritos(int $idCompeticao): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                t.cd_time,
                t.nm_time,
                t.path_escudo
            FROM inscricao_competicao ic
            INNER JOIN vinculo_inscricao_time vic
                ON vic.cd_inscricao_competicao = ic.cd_inscricao_competicao
            INNER JOIN time t
                ON t.cd_time = vic.cd_time
            WHERE ic.cd_competicao = :cd_competicao
            AND vic.ativo = TRUE
            ORDER BY t.cd_time
        ");

        $stmt->execute([
            ':cd_competicao' => $idCompeticao
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTimesDisponiveisInscricao(int $idCompeticao): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT
                t.cd_time,
                t.nm_time,
                t.path_escudo
            FROM competicao c
            INNER JOIN time t
                ON t.cd_esporte = c.cd_esporte
                AND t.ativo = TRUE
            LEFT JOIN vinculo_time_escola vte
                ON vte.cd_time = t.cd_time
                AND vte.ativo = TRUE
            WHERE c.cd_competicao = :competicao
              AND (c.cd_escola IS NULL OR vte.cd_escola = c.cd_escola)
              AND NOT EXISTS (
                    SELECT 1
                    FROM inscricao_competicao ic
                    INNER JOIN vinculo_inscricao_time vit
                        ON vit.cd_inscricao_competicao = ic.cd_inscricao_competicao
                        AND vit.cd_time = t.cd_time
                        AND vit.ativo = TRUE
                    WHERE ic.cd_competicao = c.cd_competicao
              )
            ORDER BY t.nm_time ASC
        ");

        $stmt->execute([':competicao' => $idCompeticao]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inscreverTime(int $idInscricao, int $idTime): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO vinculo_inscricao_time(
                cd_inscricao_competicao,
                cd_time
            )
            VALUES(
                :inscricao,
                :time
            )
            ON CONFLICT (cd_inscricao_competicao, cd_time)
            DO UPDATE SET
                ativo = TRUE,
                inscrito_em = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            ':inscricao' => $idInscricao,
            ':time' => $idTime,
        ]);
    }

    public function removerTimeInscrito(int $idCompeticao, int $idTime): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE vinculo_inscricao_time vit
            SET ativo = FALSE
            FROM inscricao_competicao ic
            WHERE ic.cd_inscricao_competicao = vit.cd_inscricao_competicao
              AND ic.cd_competicao = :competicao
              AND vit.cd_time = :time
        ");

        $stmt->execute([
            ':competicao' => $idCompeticao,
            ':time' => $idTime,
        ]);
    }
}
