<?php

/*
cadastrar();
    ->  Cadastra usuário + aluno com o vinculo da escola do usuário atual
        ou selecionado. (Model)

listar(); 
    ->  Lista todos os Alunos (Model)

listarUsuariosDisponiveis();
    ->  Lista os usuário ativos que não são alunos (Model)

buscar();
    ->  Busca aluno específico (Model)

atualizar();
    ->  Atualiza informações cadastrais de um aluno (Model)

remover();
    ->  Deleta aluno específico (Model)

obterEscolaDoAluno();
    -> Procura escola do aluno listado (Model)
*/

class AlunoService
{
    private PDO $pdo;

    private Usuario $usuarioModel; 

    private Aluno $alunoModel;

    private VinculoUsuarioEscola $vinculoEscolaUsuarioModel;

    private Papel $papelModel;

    private AlunoValidator $alunoValidator;

    private Permissoes $permissoes;

    private NormalizadorCampo $normalizadorCampo;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->alunoModel = new Aluno();
        $this->vinculoEscolaUsuarioModel = new VinculoUsuarioEscola();
        $this->papelModel = new Papel();
        $this->alunoValidator = new AlunoValidator();
        $this->permissoes = new Permissoes();
        $this->normalizadorCampo = new NormalizadorCampo();
    }

    public function cadastrar(array $dados): void
    {
        $idUsuarioExistente = (int) ($dados['cd_usuario'] ?? 0);
        $usuarioExistente = null;

        if ($idUsuarioExistente > 0) {
            $papeis = $_SESSION['usuario']['papeis'] ?? [];
            if (!in_array('DIR', $papeis, true)) {
                throw new Exception('Somente diretor pode vincular usuário existente.');
            }

            $usuarioExistente = $this->usuarioModel->buscar($idUsuarioExistente);
            if ($usuarioExistente === null) {
                throw new Exception('Usuário selecionado não encontrado.');
            }

            if ($this->alunoModel->existePorUsuario($idUsuarioExistente)) {
                throw new Exception('Este usuário já está cadastrado como aluno.');
            }
        } else {
            $this->alunoValidator->validarCadastroAluno($dados);
        }

        $papelAluno = $this->papelModel->buscarPapelPorNome('ALUNO');

        if ($papelAluno === null) {
            throw new Exception("Papel ALUNO não encontrado.");
        }

        $papelAlunoId = (int) ($papelAluno['cd_papel'] ?? 0);

        if ($papelAlunoId <= 0) {
            throw new Exception('ID do papel ALUNO inválido.');
        }

        try {
            $this->pdo->beginTransaction();

            $idEscola = $this->permissoes->resolverEscolaCadastro($dados);

            if ($idEscola <= 0) {
                throw new Exception('Selecione a escola do aluno.');
            }

            $idUsuarioAtual = (int) ($_SESSION['usuario']['id'] ?? 0);
            if (!$this->vinculoEscolaUsuarioModel->usuarioPodeGerenciarEscola($idUsuarioAtual, $idEscola)) {
                throw new Exception('Você não tem permissão para cadastrar aluno nesta escola.');
            }

            if (empty($dados['data_nascimento'])) {
                throw new Exception('Informe a data de nascimento.');
            }

            $caminhoPublicoFoto = $this->salvarFoto($_FILES['foto_perfil']);

            $idUsuario = $idUsuarioExistente > 0
                ? $idUsuarioExistente
                : $this->usuarioModel->cadastrar([
                    'nm_usuario' => $dados['nome'],
                    'email' => $dados['email'],
                    'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
                    'foto_perfil' => $caminhoPublicoFoto
                ]);

            // Vincula usuário à escola com o papel ALUNO
            $this->vinculoEscolaUsuarioModel->vincularPapelEscola(
                $idUsuario,
                $idEscola,
                $papelAlunoId
            );

            $this->alunoModel->cadastrar([
                'usuario' => $idUsuario,
                'escola' => $dados['escola'] ?? null,
                'nome' => $dados['nome'],
                'ra' => $dados['ra'] ?? null,
                'data_nascimento' => $dados['data_nascimento'] ?? null,
                'sexo' => $dados['sexo'] ?? null,
                'telefone' => $this->normalizadorCampo->normalizarCampoNulo($dados['telefone'] ?? null),
                'cep' => $this->normalizadorCampo->normalizarCampoNulo($dados['cep'] ?? null),
            ]);

            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }
    }

    // Lista todos os alunos
    public function listar(array $filtros = []): array
    {
        return $this->alunoModel->listar($filtros);
    }

    // Lista usuários disponiveis para se tornarem alunos
    public function listarUsuariosDisponiveis(): array
    {
        return $this->usuarioModel->listarDisponiveisParaAluno();
    }

    // Busca alunos com base no seu cd
    public function buscar(int $id): array
    {
        $aluno = $this->alunoModel->buscar($id);

        if ($aluno === null) {
            throw new Exception("Aluno(a) não encontrado(a).");
        }

        return $aluno;
    }

    //Atualiza Aluno
    public function atualizar(int $id, array $dados): void
    {
        $aluno = $this->alunoModel->buscar($id);

        if ($aluno === null) {
            throw new Exception('Aluno não encontrado.');
        }

        $idEscola = $this->obterEscolaDoAluno($id);
        if ($idEscola === null || !$this->vinculoEscolaUsuarioModel->usuarioPodeGerenciarEscola(
            (int) ($_SESSION['usuario']['id'] ?? 0),
            $idEscola
        )) {
            throw new Exception('Você não tem permissão para editar este aluno.');
        }

        $this->pdo->beginTransaction();

        try {
            $arquivo = $_FILES['foto_perfil'] ?? null;
            $caminhoPublicoFoto = null;

            if ($arquivo !== null && isset($arquivo['tmp_name']) && is_uploaded_file($arquivo['tmp_name'])) 
            {
                $caminhoPublicoFoto = $this->salvarFoto($_FILES['foto_perfil']);

                $this->usuarioModel->atualizarFotoPerfil((int) $aluno['cd_usuario'], $caminhoPublicoFoto);
            }

            $this->usuarioModel->atualizarNome((int) $aluno['cd_usuario'], (string) $dados['nome']);
            $this->alunoModel->atualizar($id, $dados);

            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    //Remove Aluno
    public function remover(int $id): void
    {
        $this->permissoes->exigirPermissaoGerenciarAluno($id);
        $this->alunoModel->remover($id);
    }

    // Retorna o id da escola em que o aluno está vinculado (vínculo ativo), ou null
    public function obterEscolaDoAluno(int $idAluno): ?int
    {
        $aluno = $this->alunoModel->buscar($idAluno);
        if ($aluno === null) {
            return null;
        }

        $cdUsuario = (int) ($aluno['cd_usuario'] ?? 0);
        if ($cdUsuario <= 0) {
            return null;
        }
        
        return $this->alunoModel->obterEscolaDoAluno($idAluno);
    }

    private function salvarFoto(?array $arquivo): ?string
    {
        if ($arquivo === null || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'] ?? '')) {
            throw new Exception('O arquivo enviado não é válido.');
        }

        if ((int) ($arquivo['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new Exception('Arquivo muito grande. Tamanho máximo de 2MB.');
        }

        $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
        if (!isset($tiposPermitidos[$tipo])) {
            throw new Exception('Tipo de arquivo inválido. Apenas JPG, PNG e WEBP são permitidos.');
        }

        $pasta = __DIR__ . '/../../public/uploads';
        if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
            throw new Exception('Não foi possível criar a pasta de uploads.');
        }

        $nome = uniqid('IMG_', true) . '.' . $tiposPermitidos[$tipo];
        if (!move_uploaded_file($arquivo['tmp_name'], $pasta . DIRECTORY_SEPARATOR . $nome)) {
            throw new Exception('Não foi possível salvar a imagem.');
        }

        return '/uploads/' . $nome;
    }

}