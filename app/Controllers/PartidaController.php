<?php

class PartidaController 
{
    private ?PartidaService $service = null;

    private function service(): PartidaService
    {
        if ($this->service === null) {
            $this->service = new PartidaService();
        }

        return $this->service;
    }

    //Exibe o formulário de cadastro
    public function create(): void
    {
        $idCompeticao = (int) ($_GET['id_competicao'] ?? 0);

        $formatos = $this->service()->listarFormato();
        $esportes = $this->service()->listarEsporte();
        $modalidades = $this->service()->listarModalidade();

        renderView('partida/criar', [
            'idCompeticao' => $idCompeticao,
            'formatos' => $formatos,
            'esportes' => $esportes,
            'modalidades' => $modalidades,
        ]);
    }

    //Salva uma nova partida
    public function store(): void
    {
        try {

            $dados = $_POST;
            $idCompeticao = (int) ($dados['id_competicao'] ?? 0);

            if ($idCompeticao <= 0) {
                throw new Exception('Competição inválida.');
            }

            $this->service()->criar($dados, $idCompeticao);

            header("Location: /competicoes/visualizar?id={$idCompeticao}&sucesso=1");
            exit;

        } catch (Exception $e) {

            $erro = $e->getMessage();
            $dados = $_POST;

            renderView('partida/criar', ['erro' => $erro, 'dados' => $dados]);

        }
    }

    //Lista todas as escolas
    public function list(): void
    {
        $filtros = [
            'nome' => trim($_GET['nome'] ?? ''),
            'categoria' => trim($_GET['categoria'] ?? ''),
            'ordem' => strtolower($_GET['ordem'] ?? 'asc'),
        ];

        if (!in_array($filtros['ordem'], ['asc', 'desc'], true)) {
            $filtros['ordem'] = 'asc';
        }

        $escolas = $this->service()->listar($filtros);

        renderView('escola/listar', [
            'escolas' => $escolas,
            'filtros' => $filtros,
        ]);
    }
}