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

    //Cadastro de escola
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

        $arquivo = $_FILES['img_logo'] ?? null;

        if ($arquivo === null || !isset($arquivo['tmp_name']) || !is_uploaded_file($arquivo['tmp_name'])) {
            throw new Exception('Selecione uma imagem para o logo da escola.');
        }

        $nomeArquivo = $arquivo['name'];
        $tamanhoArquivo = (int) $arquivo['size'];
        $erroArquivo = (int) $arquivo['error'];
        $tmpArquivo = $arquivo['tmp_name'];

        $extensaoArquivo = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extensaoArquivo, $extensoesPermitidas, true)) {
            throw new Exception('Tipo de arquivo inválido. Apenas JPG, JPEG, PNG e WEBP são permitidos.');
        }

        if ($erroArquivo !== 0) {
            throw new Exception('Erro durante a transferência do arquivo, tente novamente.');
        }

        if ($tamanhoArquivo > 2 * 1024 * 1024) {
            throw new Exception('Arquivo muito grande. Tamanho máximo de 2MB.');
        }

        $pastaUploads = __DIR__ . '/../../public/uploads';

        if (!is_dir($pastaUploads) && !mkdir($pastaUploads, 0755, true) && !is_dir($pastaUploads)) {
            throw new Exception('Não foi possível criar a pasta de uploads.');
        }

        $novoNomeArquivo = uniqid('IMG_', true) . '.' . $extensaoArquivo;
        $caminhoCompleto = $pastaUploads . DIRECTORY_SEPARATOR . $novoNomeArquivo;
        $caminhoPublico = '/uploads/' . $novoNomeArquivo;

        if (!move_uploaded_file($tmpArquivo, $caminhoCompleto)) {
            throw new Exception('Não foi possível salvar a imagem na pasta de uploads.');
        }

        try {

            $this->pdo->beginTransaction();

            $this->escolaModel->cadastrar([

                'nome' => $dados['nome'],

                'telefone' => $this->normalizarCampoOpcional($dados['telefone'] ?? null),

                'cep' => $this->normalizarCampoOpcional($dados['cep'] ?? null),

                'numero' => $this->normalizarCampoOpcional($dados['numero'] ?? null),

                'categoria_administrativa' => $dados['categoria_administrativa'],

                'img_logo' => $caminhoPublico

            ]);

            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }

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

    //Atualiza Escola
    public function atualizar(
        int $id,
        array $dados
    ): void {

        $this->escolaModel->atualizar(
            $id,
            $dados
        );

    }

    //Remove escola
    public function remover(int $id): void
    {
        $this->escolaModel->remover($id);
    }

    // Define o status ativo/inativo da escola
    public function definirAtiva(int $id, bool $ativa): void
    {
        $this->escolaModel->setAtiva($id, $ativa);
    }

    private function normalizarCampoOpcional(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim($valor);

        return $valor === '' ? null : $valor;
    }
}
