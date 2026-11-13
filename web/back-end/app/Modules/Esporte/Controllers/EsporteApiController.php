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
}
