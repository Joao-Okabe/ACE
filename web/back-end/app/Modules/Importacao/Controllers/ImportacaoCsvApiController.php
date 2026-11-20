<?php

class ImportacaoCsvApiController
{
    private ?ImportacaoCsvService $service = null;

    private function service(): ImportacaoCsvService
    {
        if ($this->service === null) {
            $this->service = new ImportacaoCsvService();
        }

        return $this->service;
    }

    public function importar(string $tipo): void
    {
        $arquivo = $_FILES['arquivo'] ?? null;
        if (!is_array($arquivo)) {
            Response::error('VALIDATION_ERROR', 'Envie o arquivo no campo arquivo.', 422);
        }
        if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Response::error('VALIDATION_ERROR', 'Não foi possível receber o arquivo CSV.', 422);
        }
        if (!is_uploaded_file((string) ($arquivo['tmp_name'] ?? ''))) {
            Response::error('VALIDATION_ERROR', 'O arquivo enviado não é válido.', 422);
        }
        if ((int) ($arquivo['size'] ?? 0) <= 0) {
            Response::error('VALIDATION_ERROR', 'O arquivo CSV está vazio.', 422);
        }
        if ((int) ($arquivo['size'] ?? 0) > 5 * 1024 * 1024) {
            Response::error('VALIDATION_ERROR', 'O tamanho máximo do CSV é 5 MB.', 422);
        }
        if (strtolower(pathinfo((string) ($arquivo['name'] ?? ''), PATHINFO_EXTENSION)) !== 'csv') {
            Response::error('VALIDATION_ERROR', 'Envie um arquivo com extensão .csv.', 422);
        }

        $escola = $_POST['escola_id'] ?? null;
        if ($escola !== null && (!is_scalar($escola) || !ctype_digit((string) $escola))) {
            Response::error('VALIDATION_ERROR', 'escola_id deve ser um número inteiro positivo.', 400);
        }

        try {
            $resultado = $this->service()->importar(
                $tipo,
                (string) $arquivo['tmp_name'],
                $escola === null ? null : (int) $escola
            );
            Response::success($resultado, 'CSV analisado. Revise e edite as linhas antes de confirmar.', 201);
        } catch (Throwable $e) {
            $this->responderErro($e);
        }
    }

    public function escolasDisponiveis(): void
    {
        try {
            Response::success(
                $this->service()->escolasDisponiveis(),
                'Escolas disponíveis para importação.'
            );
        } catch (Throwable $e) {
            $this->responderErro($e);
        }
    }

    public function editarLinha(string $id, string $linha): void
    {
        if (!ctype_digit($linha) || (int) $linha < 1) {
            Response::error('VALIDATION_ERROR', 'Número de linha inválido.', 400);
        }
        $corpo = json_decode(file_get_contents('php://input'), true);
        if (!is_array($corpo) || !isset($corpo['dados']) || !is_array($corpo['dados'])) {
            Response::error('BAD_REQUEST', 'Envie um objeto JSON com o campo dados.', 400);
        }

        try {
            $resultado = $this->service()->editarLinha($id, (int) $linha, $corpo['dados']);
            Response::success($resultado, 'Linha atualizada.');
        } catch (Throwable $e) {
            $this->responderErro($e);
        }
    }

    public function confirmar(string $id): void
    {
        try {
            $resultado = $this->service()->confirmar($id);
            Response::success($resultado, 'Importação confirmada com sucesso.');
        } catch (Throwable $e) {
            $this->responderErro($e);
        }
    }

    private function responderErro(Throwable $e): void
    {
        if ($e instanceof ImportacaoCsvValidationException) {
            Response::error(
                'VALIDATION_ERROR',
                $e->getMessage(),
                422,
                ['linhas' => $e->linhas]
            );
        }
        if ($e instanceof DomainException) {
            Response::error('FORBIDDEN', $e->getMessage(), 403);
        }
        if ($e instanceof InvalidArgumentException) {
            Response::error('VALIDATION_ERROR', $e->getMessage(), 422);
        }
        if ($e instanceof OutOfBoundsException) {
            Response::error('NOT_FOUND', $e->getMessage(), 404);
        }
        if ($e instanceof LogicException) {
            Response::error('CONFLICT', $e->getMessage(), 409);
        }
        if ($e instanceof PDOException && $e->getCode() === '23505') {
            Response::error('CONFLICT', 'Um registro já foi cadastrado durante a importação. Revise os dados e tente novamente.', 409);
        }

        error_log(__METHOD__ . ': ' . $e->getMessage());
        Response::error('INTERNAL_ERROR', 'Não foi possível concluir a importação CSV.', 500);
    }
}
