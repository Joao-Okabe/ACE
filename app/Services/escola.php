<?php

class EscolaService
{
    private PDO $pdo;

    private Usuario $usuarioModel;

    private Escola $escolaModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->usuarioModel = new Usuario();

        $this->escolaModel = new Escola();
    }

    /**
     * Lista escolas
     */
    public function listar(): array
    {
        return $this->escolaModel->listar();
    }

    /**
     * Busca uma escola
     */
    public function buscar(int $id): array
    {
        return $this->escolaModel->buscar($id);
    }

    /**
     * Cadastro completo
     */
    public function cadastrar(array $dados): void
    {

        if (empty($dados['nome'])) {
            throw new Exception("Informe o nome da escola.");
        }

        if (empty($dados['email'])) {
            throw new Exception("Informe um e-mail.");
        }

        if (empty($dados['senha'])) {
            throw new Exception("Informe uma senha.");
        }

        $senhaHash = password_hash(
            $dados['senha'],
            PASSWORD_DEFAULT
        );

        try {

            $this->pdo->beginTransaction();

            $idUsuario = $this->usuarioModel->cadastrar([
                'email' => $dados['email'],
                'senha' => $senhaHash,
                'papel' => 2 // Escola
            ]);

            $this->escolaModel->cadastrar([

                'usuario' => $idUsuario,

                'nome' => $dados['nome'],

                'telefone' => $dados['telefone'],

                'cep' => $dados['cep'],

                'logradouro' => $dados['logradouro'],

                'numero' => $dados['numero'],

                'bairro' => $dados['bairro'],

                'cidade' => $dados['cidade'],

                'uf' => $dados['uf']

            ]);

            $this->pdo->commit();

        } catch (Exception $e) {

            $this->pdo->rollBack();

            throw $e;

        }

    }

    /**
     * Atualiza escola
     */
    public function atualizar(
        int $id,
        array $dados
    ): void {

        $this->escolaModel->atualizar(
            $id,
            $dados
        );

    }

    /**
     * Remove escola
     */
    public function remover(int $id): void
    {
        $this->escolaModel->remover($id);
    }
}