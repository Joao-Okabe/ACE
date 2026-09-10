<?php

class EscolaService
{
    private PDO $pdo;

    private Usuario $usuarioModel;

    private Escola $escolaModel;

    private NormalizadorCampo $normalizador;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->usuarioModel = new Usuario();

        $this->escolaModel = new Escola();

        $this->normalizador = new NormalizadorCampo();
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

        if (!in_array($dados['categoria_administrativa'], ['Escola Municipal', 'Escola Estadual', 'Privada'], true)) {
            throw new Exception("Categoria administrativa inválida.");
        }

        if ($this->usuarioModel->buscarPorEmail($dados['email']) !== null) {
            throw new Exception("Já existe um usuário cadastrado com este e-mail.");
        }

        $senhaHash = password_hash(
            $dados['senha'],
            PASSWORD_DEFAULT
        );

        $caminhoPublico = $this->salvarLogo($dados['img_logo_upload'] ?? null, true);

        try {

            $this->pdo->beginTransaction();

            $this->escolaModel->cadastrar([

                'nome' => $dados['nome'],

                'telefone' => $this->normalizador->normalizarCampoNulo($dados['telefone'] ?? null),

                'cep' => $this->normalizador->normalizarCampoNulo($dados['cep'] ?? null),

                'numero' => $this->normalizador->normalizarCampoNulo($dados['numero'] ?? null),

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
    public function listar(array $filtros = []): array
    {
        return $this->escolaModel->listar($filtros);
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
    public function atualizar(int $id, array $dados): void 
    {

        $escola = $this->escolaModel->buscar($id);

        if ($escola === null) {
            throw new Exception('Escola não encontrada.');
        }

        $novoLogo = $this->salvarLogo($dados['img_logo_upload'] ?? null, false);
        $dados['img_logo'] = $novoLogo ?? $escola['img_logo'];

        $this->escolaModel->atualizar(
            $id,
            $dados
        );

    }

    private function salvarLogo(?array $arquivo, bool $obrigatorio): ?string
    {
        if ($arquivo === null || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            if ($obrigatorio) {
                throw new Exception('Selecione uma imagem para o logo da escola.');
            }

            return null;
        }

        if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new Exception('Erro durante a transferência da imagem.');
        }

        $tmpArquivo = $arquivo['tmp_name'] ?? '';

        if (!is_uploaded_file($tmpArquivo)) {
            throw new Exception('O arquivo enviado não é válido.');
        }

        $extensaoArquivo = strtolower(pathinfo($arquivo['name'] ?? '', PATHINFO_EXTENSION));

        if (!in_array($extensaoArquivo, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new Exception('Tipo de arquivo inválido. Apenas JPG, JPEG, PNG e WEBP são permitidos.');
        }

        if ((int) ($arquivo['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new Exception('Arquivo muito grande. Tamanho máximo de 2MB.');
        }

        $pastaUploads = __DIR__ . '/../../public/uploads';

        if (!is_dir($pastaUploads) && !mkdir($pastaUploads, 0755, true) && !is_dir($pastaUploads)) {
            throw new Exception('Não foi possível criar a pasta de uploads.');
        }

        if (!is_writable($pastaUploads)) {
            throw new Exception('A pasta de uploads não possui permissão de escrita.');
        }

        $novoNomeArquivo = uniqid('IMG_', true) . '.' . $extensaoArquivo;
        $caminhoCompleto = $pastaUploads . DIRECTORY_SEPARATOR . $novoNomeArquivo;

        if (!move_uploaded_file($tmpArquivo, $caminhoCompleto)) {
            throw new Exception('Não foi possível salvar a imagem na pasta de uploads.');
        }

        return '/uploads/' . $novoNomeArquivo;
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

    
}
