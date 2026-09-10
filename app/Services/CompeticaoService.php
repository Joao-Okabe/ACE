<?php

class CompeticaoService
{
    private PDO $pdo;

    private Permissoes $permissoes;

    private usuario $usuarioModel;

    private papel $papelModel;

    private competicao $competicaoModel;

    public function __construct()
    {
        $this->pdo = Database::connect();
        
        $this->permissoes = new Permissoes();

        $this->usuarioModel = new Usuario();

        $this->papelModel =  new Papel();

        $this->competicaoModel = new Competicao();
    }

    public function criar(array $dados):void { 

        if(
            $this->permissoes->temPapel('ADM') || 
            $this->permissoes->temPapel('DIR') || 
            $this->permissoes->temPapel('PRF') === true
        ){
            try {
            $this->pdo->beginTransaction();

            $this->competicaoModel->criar([
                "nm_competicao" => $dados['nm_competicao'],
                "dt_inicio" => $dados['dt_inicio'] ?? null,
                "dt_encerramento"  => $dados['dt_encerramento'] ?? null
            ],
                (int) $_SESSION['cd_usuario'],
            );

            } catch (Exception $e) {

                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }

                throw $e;

            }
        }
    }

    // Busca competições
    public function buscar(int $id): array
    {
        $competicao = $this->competicaoModel->buscar($id);

        if ($competicao === null) {
            throw new Exception("Aluno não encontrada.");
        }

        return $competicao;
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
   
    public function listarNome(string $nome): array
    {
        return $this->competicaoModel->listarNome($nome);
    }

    public function listarDataInicio(string $dt_inicio): array
    {
        return $this->competicaoModel->listarDataInicio($dt_inicio);
    }

    public function listarParticipacao(int $cd_usuario)
    {
        return $this->competicaoModel->listarParticipacao($cd_usuario);
    }

    public function listarCriador(int $cd_usuario): array
    {
        return $this->competicaoModel->listarCriador($cd_usuario);
    }



     //Remove Aluno
    public function remover(int $id): void
    {
        $this->competicaoModel->remover($id);
    }
}