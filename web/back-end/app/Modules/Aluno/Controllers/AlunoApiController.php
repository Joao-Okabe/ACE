<?php

class AlunoApiController
{
    private ?AlunoService $service = null;

    private function service(): AlunoService
    {
        if ($this->service === null) {
            $this->service = new AlunoService();
        }

        return $this->service;
    }

    public function index(): void
    {
        try {
            $alunos = $this->service()->listar();

            Response::json([
                'sucesso' => true,
                'dados' => $alunos
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não foi possível listar os alunos.'
            ], 500);
        }
    }
}
