<?php

class VinculoTimeController 
{
    private ?VinculoTimeService $service = null;

    private function service(): VinculoTimeService
    {
        if ($this->service === null) {
            $this->service = new VinculoTimeService();
        }

        return $this->service;
    }

    public function adicionarResponsavel(): void
    {
        $idTime = (int) ($_GET['id'] ?? $_POST['id_time'] ?? 0);
        if ($idTime <= 0) {
            http_response_code(400);
            echo 'ID do time inválido';
            return;
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
        } catch (Exception $e) {
            http_response_code(403);
            echo $e->getMessage();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idResponsavel = (int) ($_POST['cd_responsavel'] ?? 0);
            if ($idResponsavel <= 0) {
                http_response_code(400);
                echo 'Selecione um responsável';
                return;
            }

            try {
                $this->service()->VincularTimeResponsavel(
                    $idResponsavel,
                    $idTime
                );

                header('Location: /times/visualizar?id=' . $idTime);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo $e->getMessage();
                return;
            }
        }

        $time = (new TimeService())->buscar($idTime);
        $responsaveis = $this->service()->listarResponsaveis();

        renderView(
            'time/adicionar-responsavel',
            [
                'times' => $time,
                'responsaveis' => $responsaveis,
            ]
        );
    }

    public function adicionarIntegrante(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id_time'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($id);
        } catch (Exception $e) {
            http_response_code(403);
            echo $e->getMessage();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario = (int) ($_POST['cd_usuario'] ?? 0);
            $idFuncaoIntegrante = (int) ($_POST['cd_funcao_integrante'] ?? 0);
            $numeroCamisa = trim((string) ($_POST['numero_camisa'] ?? ''));
            $capitao = isset($_POST['capitao']);
            if ($idUsuario <= 0) {
                http_response_code(400);
                echo 'Selecione um aluno';
                return;
            }
            if ($idFuncaoIntegrante <= 0) {
                http_response_code(400);
                echo 'Função do integrante inválida';
                return;
            }

            if ($numeroCamisa !== '' && (!ctype_digit($numeroCamisa) || (int) $numeroCamisa <= 0)) {
                http_response_code(400);
                echo 'Número da camiseta inválido';
                return;
            }

            try {
                $this->service()->VincularTimeIntegrante(
                    $idUsuario,
                    $id,
                    $idFuncaoIntegrante,
                    $numeroCamisa === '' ? null : (int) $numeroCamisa,
                    $capitao
                );
                header('Location: /times/visualizar?id=' . $id);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo $e->getMessage();
                return;
            }
        }

        $times = (new TimeService())->buscar($id);
        $alunos = $this->service()->listarAlunosTime($id);
        $funcoes = $this->service()->listarFuncoesIntegranteTime($id);

        renderView(
            'time/adicionar-integrante', 
            [
                'times' => $times,
                'alunos' => $alunos,
                'funcoes' => $funcoes,
            ]
        );
    }

    public function removerIntegrante(): void
    {
        $idTime = (int) ($_POST['id_time'] ?? 0);
        $idUsuario = (int) ($_POST['cd_usuario'] ?? 0);

        if ($idTime <= 0 || $idUsuario <= 0) {
            http_response_code(400);
            echo 'Integrante ou time inválido';
            return;
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
            $this->service()->removerTimeIntegrante($idUsuario, $idTime);
            header('Location: /times/visualizar?id=' . $idTime);
            exit;
        } catch (Exception $e) {
            http_response_code(400);
            echo $e->getMessage();
        }
    }

    public function adicionarTecnico(): void
    {
        $idTime = (int) ($_GET['id'] ?? $_POST['id_time'] ?? 0);
        if ($idTime <= 0) {
            http_response_code(400);
            echo 'ID do time inválido';
            return;
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
        } catch (Exception $e) {
            http_response_code(403);
            echo $e->getMessage();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario = (int) ($_POST['cd_usuario'] ?? 0);
            if ($idUsuario <= 0) {
                http_response_code(400);
                echo 'Selecione um Técnico';
                return;
            }

            try {
                $this->service()->vincularTecnicoTime(
                    $idUsuario,
                    $idTime
                );

                header('Location: /times/visualizar?id=' . $idTime);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo $e->getMessage();
                return;
            }
        }

        $time = (new TimeService())->buscar($idTime);
        $usuarios = $this->service()->listarUsuarios();

        renderView(
            'time/adicionar-tecnico',
            [
                'times' => $time,
                'usuarios' => $usuarios,
            ]
        );
    }

    public function adicionarEscalacao(): void
    {
        $idTime = (int) ($_GET['id'] ?? $_POST['id_time'] ?? 0);
        if ($idTime <= 0) {
            http_response_code(400);
            echo 'ID do time inválido';
            return;
        }

        try {
            $this->service()->exigirPermissaoGerenciarTime($idTime);
        } catch (Exception $e) {
            http_response_code(403);
            echo $e->getMessage();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario = (int) ($_POST['cd_usuario'] ?? 0);
            if ($idUsuario <= 0) {
                http_response_code(400);
                echo 'Selecione um Técnico';
                return;
            }

            try {
                $this->service()->vincularTecnicoTime(
                    $idUsuario,
                    $idTime
                );

                header('Location: /times/visualizar?id=' . $idTime);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo $e->getMessage();
                return;
            }
        }

        $time = (new TimeService())->buscar($idTime);
        $usuarios = $this->service()->listarUsuarios();

        renderView(
            'time/adicionar-tecnico',
            [
                'times' => $time,
                'usuarios' => $usuarios,
            ]
        );
    }
}