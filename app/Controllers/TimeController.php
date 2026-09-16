<?php 

class TimeController 
{
    private ?TimeService $service = null;

    private function service(): TimeService
    {
        if ($this->service === null) {
            $this->service = new TimeService();
        }

        return $this->service;
    }

    public function create(): void
    {
        renderView('time/criar');
    }

    public function store(): void
    {
        try {

            $dados = $_POST;
            $dados['path_brasao'] = $_FILES['path_brasao'] ?? null;

            $this->service()->cadastrar($dados);

            header("Location: /time/criar?sucesso=1");
            exit;

        } catch (Exception $e) {

            $erro = $e->getMessage();
            $dados = $_POST;

            $this->service()->listar();

            renderView('time/criar', ['erro' => $erro, 'dados' => $dados]);
        }
    }

    public function list(): void{
        $filtros = [
            'nome' => trim($_GET['nome'] ?? ''),
            'categoria' => trim($_GET['categoria'] ?? ''),
            'ordem' => strtolower($_GET['ordem'] ?? 'asc'),
        ];

        if (!in_array($filtros['ordem'], ['asc', 'desc'], true)) {
            $filtros['ordem'] = 'asc';
        }

        $escolas = $this->service()->listar($filtros);

        renderView('time/listar', [
            'escolas' => $escolas,
            'filtros' => $filtros,
        ]);
    }

}