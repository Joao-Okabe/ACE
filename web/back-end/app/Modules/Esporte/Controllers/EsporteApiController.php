<?php

class EsporteApiController
{
    private ?EsporteService $service = null;

    private function service(): EsporteService
    {
        if ($this->service === null) {
            $this->service = new EsporteService();
        }

        return $this->service;
    }

    public function index(): void
    {
        try {
            Response::json([
                'sucesso' => true,
                'dados' => $this->service()->listar()
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível listar os esportes.'
            ], 500);
        }
    }

    public function indexV1(): void
    {
        try {
            Response::success($this->service()->listar(), 'Esportes listados.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível listar os esportes.', 500);
        }
    }

    public function modalidadesV1(): void
    {
        try {
            Response::success((new ModalidadeService())->listar(), 'Modalidades listadas.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível listar as modalidades.', 500);
        }
    }

    public function categoriasV1(): void
    {
        try {
            $modalidades = (new ModalidadeService())->listar();
            $categorias = array_map(
                static fn (array $modalidade): array => [
                    'id' => (int) $modalidade['cd_modalidade'],
                    'nome' => $modalidade['categoria'],
                    'sexo' => $modalidade['sexo'],
                    'modalidade_id' => (int) $modalidade['cd_modalidade'],
                ],
                $modalidades
            );
            Response::success($categorias, 'Categorias disponíveis por modalidade.');
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível listar as categorias.', 500);
        }
    }
}
