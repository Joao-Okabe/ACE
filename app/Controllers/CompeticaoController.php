<?php

class CompeticaoController 
{

    private ?CompeticaoService $service = null;

    private function service(): CompeticaoService
    {
        if ($this->service === null) {
            $this->service = new CompeticaoService();
        }

        return $this->service;
    }

    //Passa sessão do usuário e exibe tela de criação de competição
    public function criar(): void
    {

        $formatoService = new FormatoService();
        $formatos = $formatoService->listar();

        $esporteService = new EsporteService();
        $esportes = $esporteService->listar();

        $modalidadeService = new ModalidadeService();
        $modalidades = $modalidadeService->listar();

        renderView('competicao/criar',
            [
                'formatos' => $formatos,
                'esportes' => $esportes,
                'modalidades' => $modalidades,
                'escolas' => (new EscolaService())->listar(),
                'ehAdministrador' => in_array('ADM', $_SESSION['usuario']['papeis'] ?? [], true),
            ] 
        );
    }

    //Salva um nova competição
    public function store(): void
    {
        try {

            $this->service()->criar($_POST);

            header("Location: /competicoes/criar?sucesso=1");
            exit;

        } catch (Exception $e) {

            $erro = $e->getMessage();
            $dados = $_POST;

            renderView('competicao/criar', [
                'erro' => $erro,
                'dados' => $dados,
                'formatos' => (new FormatoService())->listar(),
                'esportes' => (new EsporteService())->listar(),
                'modalidades' => (new ModalidadeService())->listar(),
                'escolas' => (new EscolaService())->listar(),
                'ehAdministrador' => in_array('ADM', $_SESSION['usuario']['papeis'] ?? [], true),
            ]);
        }
    }

    //Lista todos as competições
    public function listar(): void
    {
        $filtros = [
            'nome' => trim($_GET['nome'] ?? ''),
            'escola' => (int) ($_GET['escola'] ?? 0),
            'ordem' => strtolower($_GET['ordem'] ?? 'asc'),
        ];

        if (!in_array($filtros['ordem'], ['asc', 'desc'], true)) {
            $filtros['ordem'] = 'asc';
        }

        $competicao = $this->service()->listar($filtros);
        $escolas = (new EscolaService())->listar();

        renderView('competicao/listar', [
            'competicoes' => $competicao,
            'escolas' => $escolas,
            'filtros' => $filtros,
        ]);
    }

    //Exibe formulário de edição da Competição
    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        try {
            $competicao = $this->service()->buscar($id);
            $this->service()->exigirPermissao($competicao);
        } catch (Exception $e) {
            http_response_code(403);
            echo $e->getMessage();
            return;
        }

        renderView('competicao/editar', ['competicao' => $competicao]);
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
            $this->service()->exigirPermissao($this->service()->buscar($id));
            $this->service()->atualizar($id, $_POST);
            header('Location: /competicoes/visualizar?id=' . $id);
            exit;
        } catch (Exception $e) {
            renderView('competicao/editar', [
                'erro' => $e->getMessage(),
                'competicao' => array_merge($this->service()->buscar($id) ?? [], $_POST),
            ]);
        }
    }

    //Visualiza competicao
    public function visualizar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $competicao = $this->service()->buscar($id);

        $escolas = (new EscolaService())->listar();
        $periodoInscricao = $this->service()->buscarPeriodoInscricao($id);
        $timesInscritos = $this->service()->listarTimesInscritos($id);
        $timesDisponiveis = $this->service()->listarTimesDisponiveisInscricao($id);

        renderView('competicao/vizualizar', [
            'competicao' => $competicao,
            'escolas' => $escolas,
            'periodoInscricao' => $periodoInscricao,
            'timesInscritos' => $timesInscritos,
            'timesDisponiveis' => $timesDisponiveis,
        ]);
    }

    public function remover(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        try {
            $this->service()->remover($id);
            header('Location: /competicoes/listar');
            exit;
        } catch (Exception $e) {
            http_response_code(403);
            echo $e->getMessage();
        }
    }

    public function criarInscricao(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        try {
            $this->service()->criarInscricao($id, $_POST);

            header('Location: /competicoes/visualizar?id=' . $id . '&sucesso_inscricao=1');
            exit;

        } catch (Exception $e) {
            $competicao = $this->service()->buscar($id);

            renderView('competicao/editar', [
                'erro' => $e->getMessage(),
                'competicao' => array_merge($competicao, $_POST),
            ]);
        }
    }

    public function inscreverTime(): void
    {
        $idCompeticao = (int) ($_POST['id_competicao'] ?? 0);
        $idTime = (int) ($_POST['id_time'] ?? 0);

        if ($idCompeticao <= 0) {
            http_response_code(400);
            echo 'ID da competição inválido';
            return;
        }

        try {
            $this->service()->inscreverTime($idCompeticao, $idTime);
            header('Location: /competicoes/visualizar?id=' . $idCompeticao . '&time_inscrito=1');
            exit;
        } catch (Exception $e) {
            header('Location: /competicoes/visualizar?id=' . $idCompeticao . '&erro_inscricao=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function removerTimeInscrito(): void
    {
        $idCompeticao = (int) ($_POST['id_competicao'] ?? 0);
        $idTime = (int) ($_POST['id_time'] ?? 0);

        if ($idCompeticao <= 0) {
            http_response_code(400);
            echo 'ID da competição inválido';
            return;
        }

        try {
            $this->service()->removerTimeInscrito($idCompeticao, $idTime);
            header('Location: /competicoes/visualizar?id=' . $idCompeticao . '&time_removido=1');
            exit;
        } catch (Exception $e) {
            header('Location: /competicoes/visualizar?id=' . $idCompeticao . '&erro_inscricao=' . urlencode($e->getMessage()));
            exit;
        }
    }

}
