<?php

class VinculoTimeApiController
{
    private ?VinculoTimeService $service = null;

    private function service(): VinculoTimeService
    {
        if ($this->service === null) {
            $this->service = new VinculoTimeService();
        }

        return $this->service;
    }

    private function lerCorpo(): array
    {
        $contentType = strtolower(
            $_SERVER['CONTENT_TYPE'] ?? ''
        );

        if (str_contains($contentType, 'application/json')) {
            $corpo = file_get_contents('php://input');
            $dados = json_decode($corpo, true);

            if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'O corpo da requisição contém JSON inválido.'
                ], 400);
            }

            return $dados;
        }

        // Aceita multipart/form-data e application/x-www-form-urlencoded.
        return $_POST;
    }

    public function definirCapitaoV1(int $idTime): void
    {
        $dados = $this->lerCorpo();
        $idAluno = (int) ($dados['alunoId'] ?? $dados['cd_aluno'] ?? 0);
        $idUsuario = $this->usuarioDoAlunoV1($idAluno);
        $this->tornarCapitao($idTime, $idUsuario);
    }

    public function vincularAtletaV1(int $idTime, int $idAluno): void
    {
        $dados = $this->lerCorpo();
        $idUsuario = $this->usuarioDoAlunoV1($idAluno);
        $dados['cd_usuario'] = $idUsuario;
        $dados['cd_funcao_integrante'] = $this->funcaoIntegranteV1(
            $idTime,
            $dados['cd_funcao_integrante'] ?? $dados['funcaoId'] ?? $dados['posicao'] ?? $dados['funcao'] ?? null
        );
        $dados['numero_camisa'] = $dados['numero_camisa'] ?? $dados['numeroCamisa'] ?? null;
        $dados['capitao'] = $dados['capitao'] ?? false;
        $this->vincularIntegranteComDadosV1($idTime, $dados);
    }

    public function atualizarAtletaV1(int $idTime, int $idAluno): void
    {
        $dados = $this->lerCorpo();
        $idUsuario = $this->usuarioDoAlunoV1($idAluno);
        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
            $campos = [];
            $params = [':time' => $idTime, ':usuario' => $idUsuario];
            if (isset($dados['cd_funcao_integrante']) || isset($dados['funcaoId']) || isset($dados['posicao']) || isset($dados['funcao'])) {
                $campos[] = 'cd_funcao_integrante = :funcao';
                $params[':funcao'] = $this->funcaoIntegranteV1(
                    $idTime,
                    $dados['cd_funcao_integrante'] ?? $dados['funcaoId'] ?? $dados['posicao'] ?? $dados['funcao']
                );
            }
            if (isset($dados['numero_camisa']) || isset($dados['numeroCamisa'])) {
                $campos[] = 'numero_camisa = :numero';
                $params[':numero'] = (int) ($dados['numero_camisa'] ?? $dados['numeroCamisa']);
            }
            if (isset($dados['capitao'])) {
                $campos[] = 'capitao = :capitao';
                $params[':capitao'] = (bool) $dados['capitao'];
            }
            if ($campos === []) {
                Response::error('VALIDATION_ERROR', 'Informe ao menos um campo para atualizar.', 400);
            }
            $stmt = Database::connect()->prepare(
                'UPDATE vinculo_time_integrante SET ' . implode(', ', $campos) . ' WHERE cd_time = :time AND cd_usuario = :usuario AND ativo = TRUE'
            );
            $stmt->execute($params);
            if ($stmt->rowCount() !== 1) {
                Response::error('NOT_FOUND', 'Atleta não encontrado no time.', 404);
            }
            if (array_key_exists('titular', $dados)) {
                $pdo = Database::connect();
                $stmt = $pdo->prepare('SELECT cd_vinculo_time_integrante FROM vinculo_time_integrante WHERE cd_time = :time AND cd_usuario = :usuario AND ativo = TRUE');
                $stmt->execute([':time' => $idTime, ':usuario' => $idUsuario]);
                $idVinculo = $stmt->fetchColumn();
                $stmt = $pdo->prepare('INSERT INTO escalacao_time (cd_vinculo_time_integrante, titular, ativo) VALUES (:vinculo, :titular, TRUE) ON CONFLICT (cd_vinculo_time_integrante) DO UPDATE SET titular = EXCLUDED.titular, ativo = TRUE');
                $stmt->execute([':vinculo' => (int) $idVinculo, ':titular' => (bool) $dados['titular']]);
            }
            Response::success(null, 'Vínculo do atleta atualizado.');
        } catch (Throwable $e) {
            if (str_contains(mb_strtolower($e->getMessage()), 'permissão')) {
                Response::error('FORBIDDEN', $e->getMessage(), 403);
            }
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('VALIDATION_ERROR', 'Não foi possível atualizar o vínculo do atleta.', 422);
        }
    }

    public function removerAtletaV1(int $idTime, int $idAluno): void
    {
        $this->removerIntegrante($idTime, $this->usuarioDoAlunoV1($idAluno));
    }

    public function salvarEscalacaoV1(int $idTime): void
    {
        $dados = $this->lerCorpo();
        $atletas = $dados['atletas'] ?? null;
        if (!is_array($atletas) || !array_is_list($atletas)) {
            Response::error('VALIDATION_ERROR', 'Informe a lista atletas.', 400);
        }
        $titulares = [];
        foreach ($atletas as $atleta) {
            if (!is_array($atleta)) {
                Response::error('VALIDATION_ERROR', 'Cada atleta da escalação deve ser um objeto.', 400);
            }
            $idAluno = (int) ($atleta['alunoId'] ?? $atleta['cd_aluno'] ?? 0);
            $titulares[] = $this->usuarioDoAlunoV1($idAluno);
        }
        $this->service()->salvarEscalacao($idTime, $titulares);
        Response::success(null, 'Escalação atualizada.');
    }

    private function usuarioDoAlunoV1(int $idAluno): int
    {
        if ($idAluno <= 0) {
            Response::error('VALIDATION_ERROR', 'ID do atleta inválido.', 400);
        }
        $stmt = Database::connect()->prepare('SELECT cd_usuario FROM aluno WHERE cd_aluno = :id');
        $stmt->execute([':id' => $idAluno]);
        $idUsuario = $stmt->fetchColumn();
        if ($idUsuario === false) {
            Response::error('NOT_FOUND', 'Atleta não encontrado.', 404);
        }
        return (int) $idUsuario;
    }

    private function funcaoIntegranteV1(int $idTime, mixed $valor): int
    {
        if (filter_var($valor, FILTER_VALIDATE_INT) !== false && (int) $valor > 0) {
            return (int) $valor;
        }
        if (!is_string($valor) || trim($valor) === '') {
            Response::error('VALIDATION_ERROR', 'Informe uma função ACE válida para o atleta.', 400);
        }
        $stmt = Database::connect()->prepare("
            SELECT fi.cd_funcao_integrante
            FROM funcao_integrante fi
            JOIN time t ON t.cd_esporte = fi.cd_esporte
            WHERE t.cd_time = :time AND LOWER(fi.nm_funcao) = LOWER(:nome)
            LIMIT 1
        ");
        $stmt->execute([':time' => $idTime, ':nome' => trim($valor)]);
        $id = $stmt->fetchColumn();
        if ($id === false) {
            Response::error('VALIDATION_ERROR', 'A posição informada não corresponde a uma função cadastrada no esporte do time.', 422);
        }
        return (int) $id;
    }

    private function vincularIntegranteComDadosV1(int $idTime, array $dados): void
    {
        if ($idTime <= 0 || (int) $dados['cd_funcao_integrante'] <= 0) {
            Response::error('VALIDATION_ERROR', 'Informe uma função válida para o atleta.', 400);
        }
        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
            $this->service()->vincularTimeIntegrante(
                (int) $dados['cd_usuario'], $idTime, (int) $dados['cd_funcao_integrante'],
                isset($dados['numero_camisa']) ? (int) $dados['numero_camisa'] : null,
                (bool) $dados['capitao']
            );
            Response::success(null, 'Atleta vinculado ao time.', 201);
        } catch (Throwable $e) {
            $status = str_contains(mb_strtolower($e->getMessage()), 'permissão') ? 403 : 422;
            Response::error($status === 403 ? 'FORBIDDEN' : 'VALIDATION_ERROR', $e->getMessage(), $status);
        }
    }

    public function listarIntegrantes(int $idTime): void
    {
        if ($idTime <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        try {
            $integrantes = $this->service()->listarIntegrantesTime($idTime);

            Response::json([
                'sucesso' => true,
                'dados' => $integrantes
            ]);
        } catch (Throwable $e) {
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 422);
        }
    }

    public function listarResponsaveis(int $idTime): void
    {
        if ($idTime <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        try {
            $responsaveis = $this->service()->listarResponsaveisTime($idTime);

            Response::json([
                'sucesso' => true,
                'dados' => $responsaveis
            ]);
        } catch (Throwable $e) {
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 422);
        }
    }   


    public function listarResponsaveisDisponiveis(int $idTime): void
    {
        if ($idTime <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);

            $responsaveis = $this->service()->listarResponsaveis();

            Response::json([
                'sucesso' => true,
                'dados' => $responsaveis
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if (str_contains(mb_strtolower($mensagem), 'permissão')) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        }
    }


    public function listarTecnicos(int $idTime): void
    {
        if ($idTime <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        try {
            $tecnicos = $this->service()->listarTecnicosTime($idTime);

            Response::json([
                'sucesso' => true,
                'dados' => $tecnicos
            ]);
        } catch (Throwable $e) {
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 422);
        }
    }

    public function listarTecnicosDisponiveis(int $idTime): void
    {
        if ($idTime <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);

            $tecnicos = $this->service()->listarTecnicosDisponiveis($idTime);

            Response::json([
                'sucesso' => true,
                'dados' => $tecnicos
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if (str_contains(mb_strtolower($mensagem), 'permissão')) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        }
    }

    public function listarFuncoesIntegrante(int $idTime): void
    {
        if ($idTime <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        try {
            $funcoes = $this->service()->listarFuncoesIntegranteTime($idTime);

            Response::json([
                'sucesso' => true,
                'dados' => $funcoes
            ]);
        } catch (Throwable $e) {
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 422);
        }
    }

    public function vincularResponsavel(int $idTime): void
    {
        if ($idTime <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        $dados = $this->lerCorpo();
        $idResponsavel = (int) ($dados['cd_responsavel'] ?? 0);

        if ($idResponsavel <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Selecione um responsável.'
            ], 400);
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
            $this->service()->vincularTimeResponsavel($idResponsavel, $idTime);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Responsável vinculado ao time com sucesso.'
            ], 201);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        }
    }

    public function vincularIntegrante(int $idTime): void
    {
        if ($idTime <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        $dados = $this->lerCorpo();
        $idUsuario = (int) ($dados['cd_usuario'] ?? 0);
        $idFuncaoIntegrante = (int) ($dados['cd_funcao_integrante'] ?? 0);
        $numeroCamisa = trim((string) ($dados['numero_camisa'] ?? ''));
        $capitao = !empty($dados['capitao']);

        if ($idUsuario <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Selecione um aluno.'
            ], 400);
        }

        if ($idFuncaoIntegrante <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Função do integrante inválida.'
            ], 400);
        }

        if ($numeroCamisa !== '' && (!ctype_digit($numeroCamisa) || (int) $numeroCamisa <= 0)) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Número da camiseta inválido.'
            ], 400);
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);

            $this->service()->vincularTimeIntegrante(
                $idUsuario,
                $idTime,
                $idFuncaoIntegrante,
                $numeroCamisa === '' ? null : (int) $numeroCamisa,
                $capitao
            );

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Integrante vinculado ao time com sucesso.'
            ], 201);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        }
    }

    public function removerIntegrante(int $idTime, int $idUsuario): void
    {
        if ($idTime <= 0 || $idUsuario <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Integrante ou time inválido.'
            ], 400);
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
            $this->service()->removerTimeIntegrante($idUsuario, $idTime);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Integrante removido do time com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        }
    }

    public function tornarCapitao(int $idTime, int $idUsuario): void
    {
        if ($idTime <= 0 || $idUsuario <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Integrante ou time inválido.'
            ], 400);
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
            $this->service()->tornarCapitao($idUsuario, $idTime);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Integrante promovido a capitão com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        }
    }

    public function removerCapitao(int $idTime, int $idUsuario): void
    {
        if ($idTime <= 0 || $idUsuario <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Integrante ou time inválido.'
            ], 400);
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
            $this->service()->removerCapitao($idUsuario, $idTime);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Capitania removida do integrante com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        }
    }

    public function vincularTecnico(int $idTime): void
    {
        if ($idTime <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        $dados = $this->lerCorpo();
        $idUsuario = (int) ($dados['cd_usuario'] ?? 0);

        if ($idUsuario <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Selecione um técnico.'
            ], 400);
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
            $this->service()->vincularTecnicoTime($idUsuario, $idTime);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Técnico vinculado ao time com sucesso.'
            ], 201);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        }
    }

    public function salvarEscalacao(int $idTime): void
    {
        if ($idTime <= 0) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'ID do time inválido.'
            ], 400);
        }

        $dados = $this->lerCorpo();

        $titulares = $dados['titulares'] ?? [];

        if (!is_array($titulares) || !array_is_list($titulares)) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'titulares deve ser uma lista de IDs, por exemplo [12, 15].'
            ], 400);
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
            $this->service()->salvarEscalacao($idTime, $titulares);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Escalação salva com sucesso.'
            ]);
        } catch (Throwable $e) {
            $mensagem = $e->getMessage();

            if (
                str_contains(mb_strtolower($mensagem), 'permissão')
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => $mensagem
                ], 403);
            }

            Response::json([
                'sucesso' => false,
                'mensagem' => $mensagem
            ], 422);
        }
    }
}
