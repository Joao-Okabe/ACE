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

        // Aceita tanto uma lista JSON quanto um array de checkbox
        // de formulário (titulares[]).
        $titulares = $dados['titulares'] ?? [];

        if (!is_array($titulares)) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'A lista de titulares é inválida.'
            ], 400);
        }

        $idsTitulares = array_map('intval', array_keys($titulares));

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
            $this->service()->salvarEscalacao($idTime, $idsTitulares);

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
