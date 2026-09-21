<?php

class VinculoTimeIntegranteController 
{
    private ?VinculoTimeIntegranteService $service = null;

    private function service(): VinculoTimeIntegranteService
    {
        if ($this->service === null) {
            $this->service = new VinculoTimeIntegranteService();
        }

        return $this->service;
    }

    public function adicionarIntegrante(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $times = (new TimeService())->buscar($id);

        renderView(
            'time/adicionar-integrante', 
            ['times' => $times]
        );
    }
}