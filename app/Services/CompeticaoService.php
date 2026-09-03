<?php

class Competicao
{
    private PDO $pdo;

    private usuario $usuarioModel;

    private papel $papelModel;

    private competicao $competicaoModel;

    public function __construct()
    {
       $this->pdo = Database::connect();

       $this->usuarioModel = new Usuario();

       $this->papelModel =  new Papel();

       $this->competicaoModel = new Competicao();
    }

    public function criar(array $dados):void {
        
        // Aqui vai chamar o MiddleWare para conferir a permissão
        // Apenas administradores e professores poderão criar
    
        try {
            $this->pdo->beginTransaction();

            $this->competicaoModel->criar([
                "nm_competicao" => $this->$dados['nm_competicao'],
                "dt_inicio" => $this->$dados['dt_inicio'] ?? null,
                "dt_encerramento"  => $this->$dados['dt_encerramento'] ?? null
            ]);

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }
    }

    //lista Competições
    public function listar(): array
    {
        return $this->competicaoModel->listar();
    }

    //FILTRAGENS respectivamente
    /*
        Por nome da competição
        Por data de inicio da competição
        Por usuário que participou da competição 
        Por criador da competição
    */
   
    public function listarNome(): array
    {
        return $this->competicaoModel->listarNome();
    }

    public function listarDataInicio(): array
    {
        return $this->competicaoModel->listarDataInicio();
    }

    public function listarParticipacao(): array
    {
        return $this->competicaoModel->listarParticipacao();
    }

    public function listarCriador(): array
    {
        return $this->competicaoModel->listarCriador();
    }
}