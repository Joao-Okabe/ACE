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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idFuncaoIntegrante = (int) ($_POST['cd_funcao_integrante'] ?? 0);
            if ($idFuncaoIntegrante <= 0) {
                http_response_code(400);
                echo 'Função do integrante inválida';
                return;
            }

            try {
                $this->service()->VincularTimeIntegrante($id, $idFuncaoIntegrante);
                header('Location: /times/visualizar?id=' . $id);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo $e->getMessage();
                return;
            }
        }

        $times = (new TimeService())->buscar($id);

        renderView(
            'time/adicionar-integrante', 
            ['times' => $times]
        );
    }
}