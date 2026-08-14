<?php

class AlunoService
{
    private PDO $pdo;

    private Usuario $usuarioModel; 

    private Aluno $alunoModel;

    private VinculoUsuarioEscola $vinculoEscolaUsuarioModel;

    private Papel $papelModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->usuarioModel = new Usuario();

        $this->alunoModel = new Aluno();

        $this->vinculoEscolaUsuarioModel = new VinculoUsuarioEscola();

        $this->papelModel = new Papel();
    }

    public function cadastrar(array $dados): void
    {
        if (empty($dados['nome'])) {
            throw new Exception("Informe o nome do(a) aluno(a). ");
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

        if (empty(trim($dados['ra'] ?? ''))) {
            throw new Exception("Informe o RA do(a) aluno(a).");
        }

        if (mb_strlen(trim($dados['ra'])) > 20) {
            throw new Exception("O RA deve ter no máximo 20 caracteres.");
        }

        if (mb_strlen(trim($dados['telefone'] ?? '')) > 20) {
            throw new Exception("O telefone deve ter no máximo 20 caracteres.");
        }

        if (mb_strlen(trim($dados['cep'] ?? '')) > 9) {
            throw new Exception("O CEP deve ter no máximo 9 caracteres.");
        }

        if ($this->usuarioModel->buscarPorEmail($dados['email']) !== null) {
            throw new Exception("Já existe um usuário cadastrado com este e-mail.");
        }

        $senhaHash = password_hash(
            $dados['senha'],
            PASSWORD_DEFAULT
        );

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

            // validações adicionais
            if (empty($dados['escola'])) {
                throw new Exception('Selecione a escola do aluno.');
            }

            if (empty($dados['data_nascimento'])) {
                throw new Exception('Informe a data de nascimento.');
            }

            // tratar upload de foto_perfil (opcional) e enviar ao cadastro de usuário
            $arquivo = $_FILES['foto_perfil'] ?? null;
            $caminhoPublicoFoto = null;

            if ($arquivo !== null && (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $erroUpload = (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE);

                if ($erroUpload !== UPLOAD_ERR_OK) {
                    $mensagem = in_array($erroUpload, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                        ? 'A foto é muito grande. O tamanho máximo permitido é 5MB.'
                        : 'Não foi possível enviar a foto. Tente novamente.';
                    throw new Exception($mensagem);
                }

                if (empty($arquivo['tmp_name']) || !is_uploaded_file($arquivo['tmp_name'])) {
                    throw new Exception('O arquivo de foto enviado é inválido.');
                }

                $nomeArquivo = $arquivo['name'];
                $tamanhoArquivo = (int) $arquivo['size'];
                $tmpArquivo = $arquivo['tmp_name'];

                $extensaoArquivo = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
                $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($extensaoArquivo, $extensoesPermitidas, true)) {
                    throw new Exception('Tipo de arquivo inválido. Apenas JPG, JPEG, PNG e WEBP são permitidos.');
                }

                if ($tamanhoArquivo > 5 * 1024 * 1024) {
                    throw new Exception('A foto é muito grande. O tamanho máximo permitido é 5MB.');
                }

                $pastaUploads = __DIR__ . '/../../public/uploads';

                if (!is_dir($pastaUploads) && !mkdir($pastaUploads, 0755, true) && !is_dir($pastaUploads)) {
                    throw new Exception('Não foi possível criar a pasta de uploads.');
                }

                $novoNomeArquivo = uniqid('IMG_', true) . '.' . $extensaoArquivo;
                $caminhoCompleto = $pastaUploads . DIRECTORY_SEPARATOR . $novoNomeArquivo;
                $caminhoPublicoFoto = '/uploads/' . $novoNomeArquivo;

                if (!move_uploaded_file($tmpArquivo, $caminhoCompleto)) {
                    throw new Exception('Não foi possível salvar a imagem na pasta de uploads.');
                }

                ImagemService::otimizar($caminhoCompleto);
            }

            $idUsuario = $this->usuarioModel->cadastrar([
                'email' => $dados['email'],
                'senha' => $senhaHash,
                'foto_perfil' => $caminhoPublicoFoto
            ]);

            // Vincula usuário à escola com o papel ALUNO
            $this->vinculoEscolaUsuarioModel->vincularPapel(
                $idUsuario,
                (int) ($dados['escola'] ?? $dados['cd_escola'] ?? 0), // campo do formulário é 'escola'
                $papelAlunoId
            );

            $this->alunoModel->cadastrar([
                'usuario' => $idUsuario,
                'escola' => $dados['escola'] ?? null,
                'nome' => $dados['nome'],
                'ra' => trim($dados['ra']),
                'data_nascimento' => $dados['data_nascimento'] ?? null,
                'sexo' => $dados['sexo'] ?? null,
                'telefone' => $this->normalizarCampoOpcional($dados['telefone'] ?? null),
                'cep' => $this->normalizarCampoOpcional($dados['cep'] ?? null),
            ]);

            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }
    }

    public function listar(array $filtros = []): array
    {
        return $this->alunoModel->listar($filtros);
    }

    public function buscar(int $id): array
    {
        $aluno = $this->alunoModel->buscar($id);

        if ($aluno === null) {
            throw new Exception("Aluno não encontrada.");
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

        $this->pdo->beginTransaction();

        try {
            $arquivo = $_FILES['foto_perfil'] ?? null;
            $caminhoPublicoFoto = null;

            if ($arquivo !== null && (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $erroUpload = (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE);

                if ($erroUpload !== UPLOAD_ERR_OK) {
                    $mensagem = in_array($erroUpload, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                        ? 'A foto é muito grande. O tamanho máximo permitido é 5MB.'
                        : 'Não foi possível enviar a foto. Tente novamente.';
                    throw new Exception($mensagem);
                }

                if (empty($arquivo['tmp_name']) || !is_uploaded_file($arquivo['tmp_name'])) {
                    throw new Exception('O arquivo de foto enviado é inválido.');
                }

                $nomeArquivo = $arquivo['name'];
                $tamanhoArquivo = (int) $arquivo['size'];
                $tmpArquivo = $arquivo['tmp_name'];

                $extensaoArquivo = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
                $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($extensaoArquivo, $extensoesPermitidas, true)) {
                    throw new Exception('Tipo de arquivo inválido. Apenas JPG, JPEG, PNG e WEBP são permitidos.');
                }

                if ($tamanhoArquivo > 5 * 1024 * 1024) {
                    throw new Exception('A foto é muito grande. O tamanho máximo permitido é 5MB.');
                }

                $pastaUploads = __DIR__ . '/../../public/uploads';

                if (!is_dir($pastaUploads) && !mkdir($pastaUploads, 0755, true) && !is_dir($pastaUploads)) {
                    throw new Exception('Não foi possível criar a pasta de uploads.');
                }

                $novoNomeArquivo = uniqid('IMG_', true) . '.' . $extensaoArquivo;
                $caminhoCompleto = $pastaUploads . DIRECTORY_SEPARATOR . $novoNomeArquivo;
                $caminhoPublicoFoto = '/uploads/' . $novoNomeArquivo;

                if (!move_uploaded_file($tmpArquivo, $caminhoCompleto)) {
                    throw new Exception('Não foi possível salvar a imagem na pasta de uploads.');
                }

                ImagemService::otimizar($caminhoCompleto);

                $this->usuarioModel->atualizarFotoPerfil((int) $aluno['cd_usuario'], $caminhoPublicoFoto);
            }

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

        $stmt = $this->pdo->prepare(
            "SELECT up.cd_escola
            FROM vinculo_usuario_escola up
            WHERE up.cd_usuario = :cd_usuario
              AND up.ativo = TRUE
            ORDER BY up.criado_em DESC
            LIMIT 1"
        );

        $stmt->execute([':cd_usuario' => $cdUsuario]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res === false || $res === null) {
            return null;
        }

        return (int) ($res['cd_escola'] ?? 0) ?: null;
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
