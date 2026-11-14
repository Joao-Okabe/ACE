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
        $this->pdo = Database::connect();

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
        $idUsuarioAtual = AuthContext::id();
        if (!$idUsuarioAtual) {
            throw new Exception('Usuário não autenticado.');
        }

        $idUsuarioExistente = (int) ($dados['cd_usuario'] ?? 0);
        $usuarioExistente = null;

        if ($idUsuarioExistente > 0) {
            $papeis = AuthContext::papeis();
            if (!in_array('DIR', $papeis, true)) {
                throw new Exception('Somente diretor pode vincular usuário existente.');
            }

            $usuarioExistente = $this->usuarioModel->buscar($idUsuarioExistente);
            if ($usuarioExistente === null) {
                throw new Exception('Usuário selecionado não encontrado.');
            }

            $this->alunoValidator->validarRa($dados);
        } else {
            $this->alunoValidator->validarCadastroAluno($dados);
        }

        $papelAluno = $this->papelModel->buscarPapelPorNome('ALU');

        if ($papelAluno === null) {
            throw new Exception("Papel ALUNO não encontrado.");
        }

        $papelAlunoId = (int) ($papelAluno['cd_papel'] ?? 0);

        if ($papelAlunoId <= 0) {
            throw new Exception('ID do papel ALUNO inválido.');
        }

        $caminhoPublicoFoto = null;
        try {
            // Valida o upload ANTES de abrir a transação, para que uma
            // foto inválida não dispare rollback desnecessário.
            if ($idUsuarioExistente <= 0) {
                $caminhoPublicoFoto = $this->salvarFoto($_FILES['foto_perfil'] ?? null);
            }

            $this->pdo->beginTransaction();

            $idEscola = $this->permissoes->resolverEscolaCadastro($dados);

            if ($idEscola <= 0) {
                throw new Exception('Selecione a escola do aluno.');
            }

            if (!Permissoes::podeGerenciarEscola($idEscola)) {
                throw new Exception('Você não tem permissão para cadastrar aluno nesta escola.');
            }

            if (
                $idUsuarioExistente > 0
                && !$this->usuarioModel->possuiVinculoEscolarAtivo($idUsuarioExistente, $idEscola)
            ) {
                throw new Exception('O usuário selecionado não possui vínculo ativo com esta escola.');
            }

            $idUsuario = $idUsuarioExistente > 0
                ? $idUsuarioExistente
                : $this->usuarioModel->cadastrar([
                    'nm_usuario' => $dados['nome'],
                    'email' => $dados['email'],
                    'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
                    'foto_perfil' => $caminhoPublicoFoto
                ]);

            if (
                $idUsuarioExistente > 0
                && $this->alunoModel->possuiAlunoAtivoNaEscola($idUsuario, $idEscola)
            ) {
                throw new Exception('Este usuário já está vinculado como aluno nesta escola.');
            }

            if (
                $idUsuarioExistente > 0
                && $this->vinculoEscolaUsuarioModel->possuiVinculoAtivo(
                    $idUsuario,
                    $idEscola,
                    $papelAlunoId
                )
            ) {
                throw new Exception('Este usuário já está vinculado como aluno nesta escola.');
            }

            // Vincula usuário à escola com o papel ALUNO
            $this->vinculoEscolaUsuarioModel->vincularPapelEscola(
                $idUsuario,
                $idEscola,
                $papelAlunoId
            );

            $dadosAluno = [
                'usuario' => $idUsuario,
                'escola' => $dados['escola'] ?? null,
                'nome' => $dados['nome'],
                'ra' => $dados['ra'] ?? null,
                'data_nascimento' => $dados['data_nascimento'] ?? null,
                'sexo' => $dados['sexo'] ?? null,
                'telefone' => $this->normalizadorCampo->normalizarCampoNulo($dados['telefone'] ?? null),
                'cep' => $this->normalizadorCampo->normalizarCampoNulo($dados['cep'] ?? null),
            ];
            $alunoExistente = $this->alunoModel->buscarPorUsuario($idUsuario);
            if ($alunoExistente !== null) {
                $this->alunoModel->reativar((int) $alunoExistente['cd_aluno'], $dadosAluno);
            } else {
                $this->alunoModel->cadastrar($dadosAluno);
            }

            $this->pdo->commit();

        } catch (Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $this->removerFotoUploadLocal($caminhoPublicoFoto);

            throw $e;

        }
    }

    // Lista todos os alunos
    public function listar(array $filtros = []): array
    {
        if (Permissoes::temPapel('ADM')) {
            return $this->alunoModel->listar($filtros);
        }
        if (!Permissoes::temPapel('DIR')) {
            throw new DomainException('Você não tem permissão para listar alunos.');
        }
        $idUsuario = AuthContext::id();
        $escolasAutorizadas = $idUsuario === null ? [] : $this->vinculoEscolaUsuarioModel->escolasAtivasPorPapeis($idUsuario, ['DIR']);
        if ($escolasAutorizadas === []) {
            return [];
        }
        if (isset($filtros['escolas']) && is_array($filtros['escolas'])) {
            $escolasAutorizadas = array_values(array_intersect($escolasAutorizadas, array_map('intval', $filtros['escolas'])));
        }
        if ($escolasAutorizadas === []) {
            return [];
        }
        $filtros['escolas'] = $escolasAutorizadas;
        return $this->alunoModel->listar($filtros);
    }

    // Lista usuários disponiveis para se tornarem alunos
    public function listarUsuariosDisponiveis(): array
    {
        if (Permissoes::temPapel('ADM')) {
            return $this->usuarioModel->listarDisponiveisParaAluno();
        }
        if (!Permissoes::temPapel('DIR')) {
            throw new DomainException('Você não tem permissão para listar usuários disponíveis.');
        }
        $idUsuario = AuthContext::id();
        $escolas = $idUsuario === null ? [] : $this->vinculoEscolaUsuarioModel->escolasAtivasPorPapeis($idUsuario, ['DIR']);
        return $escolas === [] ? [] : $this->usuarioModel->listarDisponiveisParaAluno($escolas);
    }

    // Busca alunos com base no seu cd
    public function buscar(int $id): array
    {
        $aluno = $this->alunoModel->buscar($id);

        if ($aluno === null) {
            throw new Exception("Aluno(a) não encontrado(a).");
        }

        $this->exigirAcessoAoAluno($aluno);

        if (!Permissoes::temPapel('ADM')) {
            $idUsuario = AuthContext::id() ?? 0;
            $escolasDiretor = $this->vinculoEscolaUsuarioModel->escolasAtivasPorPapeis($idUsuario, ['DIR']);
            $escolasAluno = $this->alunoModel->listarEscolasAtivasDoAlunoComNome((int) $aluno['cd_usuario']);
            $escolasVisiveis = array_values(array_filter(
                $escolasAluno,
                static fn (array $escola): bool => in_array((int) $escola['cd_escola'], $escolasDiretor, true)
            ));
            $aluno['escolas_vinculadas'] = $escolasVisiveis;
            $aluno['cd_escola'] = count($escolasVisiveis) === 1 ? (int) $escolasVisiveis[0]['cd_escola'] : null;
            $aluno['escola'] = count($escolasVisiveis) === 1 ? $escolasVisiveis[0]['nm_escola'] : null;
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

        $this->exigirAcessoAoAluno($aluno);

        $caminhoPublicoFoto = null;
        $fotoAnterior = (string) ($aluno['foto_perfil'] ?? '');
        try {
            // Validação dos campos enviados na atualização.
            $this->alunoValidator->validarAtualizacaoAluno($dados);

            $arquivo = $_FILES['foto_perfil'] ?? null;
            if ($arquivo !== null && ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $caminhoPublicoFoto = $this->salvarFoto($arquivo);
            }

            $this->pdo->beginTransaction();

            if ($caminhoPublicoFoto !== null) {

                $this->usuarioModel->atualizarFotoPerfil((int) $aluno['cd_usuario'], $caminhoPublicoFoto);
            }

            $this->usuarioModel->atualizarNome((int) $aluno['cd_usuario'], (string) $dados['nome']);
            $this->alunoModel->atualizar($id, $dados);$novaEscola = (int) ($dados['escola'] ?? $idEscola);

            if ($novaEscola !== $idEscola) {
                $papelAluno = $this->papelModel->buscarPapelPorNome('ALU');
                $papelAlunoId = (int) $papelAluno['cd_papel'];

                $this->vinculoEscolaUsuarioModel->desativarVinculosPapel((int) $aluno['cd_usuario'],$papelAlunoId);
                $this->vinculoEscolaUsuarioModel->vincularPapelEscola((int) $aluno['cd_usuario'],$novaEscola,$papelAlunoId);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $this->removerFotoUploadLocal($caminhoPublicoFoto);
            throw $e;
        }

        if ($caminhoPublicoFoto !== null && $fotoAnterior !== $caminhoPublicoFoto) {
            $this->removerFotoUploadLocal($fotoAnterior);
        }
    }

    //Remove Aluno
    public function remover(int $id, ?int $idEscola = null): void
    {
        $aluno = $this->alunoModel->buscar($id);
        if ($aluno === null) {
            throw new Exception('Aluno não encontrado.');
        }

        $idUsuario = AuthContext::id();
        if ($idUsuario === null) {
            throw new DomainException('Autenticação necessária.');
        }
        if (Permissoes::temPapel('ADM')) {
            try {
                $this->pdo->beginTransaction();
                $this->alunoModel->desativar($id);
                $this->vinculoEscolaUsuarioModel->desativarPapelAluno((int) $aluno['cd_usuario']);
                $this->pdo->commit();
            } catch (Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                throw $e;
            }
            return;
        }
        if (!Permissoes::temPapel('DIR')) {
            throw new DomainException('Você não tem permissão para remover este aluno.');
        }

        $escolasDiretor = $this->vinculoEscolaUsuarioModel->escolasAtivasPorPapeis($idUsuario, ['DIR']);
        $escolasAluno = $this->alunoModel->listarEscolasAtivasDoAluno((int) $aluno['cd_usuario']);
        $escolasGerenciadasDoAluno = array_values(array_intersect($escolasDiretor, $escolasAluno));
        if ($escolasGerenciadasDoAluno === []) {
            throw new DomainException('Você não tem permissão para remover este aluno.');
        }
        if ($idEscola === null) {
            if (count($escolasGerenciadasDoAluno) !== 1) {
                throw new InvalidArgumentException('Informe escola quando o aluno possuir mais de um vínculo escolar autorizado.');
            }
            $idEscola = $escolasGerenciadasDoAluno[0];
        }
        if (!in_array($idEscola, $escolasGerenciadasDoAluno, true)) {
            throw new DomainException('Você não tem permissão para remover este aluno desta escola.');
        }

        try {
            $this->pdo->beginTransaction();
            $this->vinculoEscolaUsuarioModel->desativarPapelAlunoNaEscola((int) $aluno['cd_usuario'], $idEscola);
            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
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

        return $this->alunoModel->obterEscolaDoAluno($cdUsuario);
    }

    public function obterEscolasDoAluno(int $idAluno): array
    {
        $aluno = $this->alunoModel->buscar($idAluno);
        if ($aluno === null) {
            return [];
        }
        return $this->alunoModel->listarEscolasAtivasDoAluno((int) $aluno['cd_usuario']);
    }

    private function exigirAcessoAoAluno(array $aluno): void
    {
        if (Permissoes::temPapel('ADM')) {
            return;
        }
        if (!Permissoes::temPapel('DIR')) {
            throw new DomainException('Você não tem permissão para acessar este aluno.');
        }
        $idUsuario = AuthContext::id();
        $escolasDiretor = $idUsuario === null ? [] : $this->vinculoEscolaUsuarioModel->escolasAtivasPorPapeis($idUsuario, ['DIR']);
        $escolasAluno = $this->alunoModel->listarEscolasAtivasDoAluno((int) $aluno['cd_usuario']);
        if (array_intersect($escolasDiretor, $escolasAluno) === []) {
            throw new DomainException('Você não tem permissão para acessar este aluno.');
        }
    }


    private function salvarFoto(?array $arquivo): ?string
    {
        if ($arquivo === null || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $erro = (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($erro !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'] ?? '')) {
            // Mensagens específicas por código de erro do PHP.
            $mensagens = [
                UPLOAD_ERR_INI_SIZE => 'O arquivo excede o limite de upload do servidor.',
                UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o limite do formulário.',
                UPLOAD_ERR_PARTIAL => 'O upload do arquivo foi interrompido. Tente novamente.',
                UPLOAD_ERR_NO_TMP_DIR => 'Erro de configuração do servidor (pasta temporária ausente).',
                UPLOAD_ERR_CANT_WRITE => 'Erro de configuração do servidor (falha ao gravar).',
                UPLOAD_ERR_EXTENSION => 'O upload foi bloqueado por uma extensão do PHP.'
            ];

            throw new Exception(
                $mensagens[$erro] ?? 'O arquivo enviado não é válido.'
            );
        }

        $tamanho = (int) ($arquivo['size'] ?? 0);

        if ($tamanho <= 0) {
            throw new Exception('O arquivo enviado está vazio.');
        }

        if ($tamanho > 2 * 1024 * 1024) {
            throw new Exception('Arquivo muito grande. Tamanho máximo de 2MB.');
        }

        $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipo = $finfo->file($arquivo['tmp_name']);

        if ($tipo === false || !isset($tiposPermitidos[$tipo])) {
            throw new Exception('Tipo de arquivo inválido. Apenas JPG, PNG e WEBP são permitidos.');
        }

        $pasta = __DIR__ . '/../../../../public/uploads';
        if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
            throw new Exception('Não foi possível criar a pasta de uploads.');
        }

        $nome = 'IMG_' . bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$tipo];
        $destino = $pasta . DIRECTORY_SEPARATOR . $nome;

        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            throw new Exception('Não foi possível salvar a imagem.');
        }

        chmod($destino, 0644);

        return '/uploads/' . $nome;
    }

    private function removerFotoUploadLocal(?string $caminhoPublico): void
    {
        if (
            $caminhoPublico === null
            || !preg_match('#^/uploads/[A-Za-z0-9_.-]+$#', $caminhoPublico)
        ) {
            return;
        }

        $pasta = realpath(__DIR__ . '/../../../../public/uploads');
        if ($pasta === false) {
            return;
        }

        $arquivo = realpath($pasta . DIRECTORY_SEPARATOR . basename($caminhoPublico));
        if ($arquivo !== false && dirname($arquivo) === $pasta && is_file($arquivo)) {
            @unlink($arquivo);
        }
    }

}
