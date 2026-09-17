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
        renderView('time/criar', [
            'escolas' => (new EscolaService())->listar(),
            'ehAdministrador' => $this->ehAdministrador(),
            'escolaVinculada' => $this->escolaVinculada(),
        ]);
    }

    public function store(): void
    {
        try {

            $dados = $_POST;
            $dados['path_brasao'] = $_FILES['path_brasao'] ?? null;

            $this->service()->cadastrar($dados);

            header("Location: /times/criar?sucesso=1");
            exit;

        } catch (Exception $e) {

            $erro = $e->getMessage();
            $dados = $_POST;

            renderView('time/criar', [
                'erro' => $erro,
                'dados' => $dados,
                'escolas' => (new EscolaService())->listar(),
                'ehAdministrador' => $this->ehAdministrador(),
                'escolaVinculada' => $this->escolaVinculada(),
            ]);
        }
    }

    private function ehAdministrador(): bool
    {
        return in_array('ADM', $_SESSION['usuario']['papeis'] ?? [], true);
    }

    private function escolaVinculada(): ?int
    {
        return (new VinculoUsuarioEscola())->escolaAtualPorPapeis(
            (int) ($_SESSION['usuario']['id'] ?? 0),
            ['DIR', 'CRD']
        );
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

        $times = $this->service()->listar($filtros);

        renderView('time/listar', [
            'times' => $times,
            'filtros' => $filtros,
        ]);
    }

    public function visualizar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        renderView('time/visualizar', ['time' => $this->service()->buscar($id)]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        renderView('time/editar', ['time' => $this->service()->buscar($id)]);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        try {
            $this->service()->atualizar($id, $_POST);
            header('Location: /times/visualizar?id=' . $id);
            exit;
        } catch (Exception $e) {
            renderView('time/editar', [
                'erro' => $e->getMessage(),
                'time' => array_merge($this->service()->buscar($id), $_POST),
            ]);
        }
    }

    //Remove escola
    public function destroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        try {

            $this->service()->remover($id);

            header("Location: /times/listar");
            exit;

        } catch (Exception $e) {

            echo $e->getMessage();

        }
    }

}