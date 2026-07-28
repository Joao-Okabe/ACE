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

    //Lista Escola
    public function listar(): array
    {
        return $this->escolaModel->listar();
    }

    //Busca escola
    public function buscar(int $id): array
    {
        $escola = $this->escolaModel->buscar($id);

        if ($escola === null) {
            throw new Exception("Escola não encontrada.");
        }

        return $escola;
    }

    //Cadastro completo
    public function cadastrar(array $dados): void
    {

        if (empty($dados['nome'])) {
            throw new Exception("Informe o nome da escola.");
        }

        if (empty($dados['email'])) {
            throw new Exception("Informe um e-mail.");
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Informe um e-mail válido.");
        }

        if (empty($dados['senha'])) {
            throw new Exception("Informe uma senha.");
        }

        if (empty($dados['categoria_administrativa'])) {
            throw new Exception("Informe a categoria administrativa.");
        }

        if (!in_array($dados['categoria_administrativa'], ['PUBLICA', 'PRIVADA'], true)) {
            throw new Exception("Categoria administrativa inválida.");
        }

        if ($this->usuarioModel->buscarPorEmail($dados['email']) !== null) {
            throw new Exception("Já existe um usuário cadastrado com este e-mail.");
        }

        $senhaHash = password_hash(
            $dados['senha'],
            PASSWORD_DEFAULT
        );

        $papelEscola = $this->usuarioModel->buscarPapelPorNome('ESCOLA');

        if ($papelEscola === null) {
            throw new Exception("Papel ESCOLA não encontrado.");
        }

        try {

            $this->pdo->beginTransaction();

            $idUsuario = $this->usuarioModel->cadastrar([
                'email' => $dados['email'],
                'senha' => $senhaHash
            ]);

            $this->usuarioModel->vincularPapel(
                $idUsuario,
                (int) $papelEscola['cd_papel']
            );

            $this->escolaModel->cadastrar([

                'usuario' => $idUsuario,

                'nome' => $dados['nome'],

                'telefone' => $this->normalizarCampoOpcional($dados['telefone'] ?? null),

                'cep' => $this->normalizarCampoOpcional($dados['cep'] ?? null),

                'numero' => $this->normalizarCampoOpcional($dados['numero'] ?? null),

                'categoria_administrativa' => $dados['categoria_administrativa'],

                'img_logo' => $this->normalizarCampoOpcional($dados['img_logo'] ?? null)

            ]);

            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }

    }

    private function normalizarCampoOpcional(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim($valor);

        return $valor === '' ? null : $valor;
    }

    private function normalizarUf(?string $uf): ?string
    {
        $uf = $this->normalizarCampoOpcional($uf);

        return $uf === null ? null : strtoupper(substr($uf, 0, 2));
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
