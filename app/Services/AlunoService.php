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
            if (empty($dados['nome'])) {
                throw new Exception("Informe o nome do(a) aluno(a). ");
            }

            if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Informe um e-mail válido.");
            }

            if (empty($dados['senha'])) {
                throw new Exception("Informe uma senha.");
            }

            if ($this->usuarioModel->buscarPorEmail($dados['email']) !== null) {
                throw new Exception("Já existe um usuário cadastrado com este e-mail.");
            }
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

            $idEscola = $this->resolverEscolaCadastro($dados);
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

            // tratar upload de foto_perfil (opcional) e enviar ao cadastro de usuário
            $arquivo = $_FILES['foto_perfil'] ?? null;
            $caminhoPublicoFoto = null;

            if ($arquivo !== null && isset($arquivo['tmp_name']) && is_uploaded_file($arquivo['tmp_name'])) {
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
                $caminhoPublicoFoto = '/uploads/' . $novoNomeArquivo;

                if (!move_uploaded_file($tmpArquivo, $caminhoCompleto)) {
                    throw new Exception('Não foi possível salvar a imagem na pasta de uploads.');
                }
            }

            $idUsuario = $idUsuarioExistente > 0
                ? $idUsuarioExistente
                : $this->usuarioModel->cadastrar([
                    'nm_usuario' => $dados['nome'],
                    'email' => $dados['email'],
                    'senha' => password_hash($dados['senha'], PASSWORD_DEFAULT),
                    'foto_perfil' => $caminhoPublicoFoto
                ]);

            // Vincula usuário à escola com o papel ALUNO
            $this->vinculoEscolaUsuarioModel->vincularPapelEscola($idUsuario, $idEscola, $papelAlunoId);

            $this->alunoModel->cadastrar([
                'usuario' => $idUsuario,
                'escola' => $dados['escola'] ?? null,
                'nome' => $dados['nome'],
                'ra' => $dados['ra'] ?? null,
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

    public function listarUsuariosDisponiveis(): array
    {
        return $this->usuarioModel->listarDisponiveisParaAluno();
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

            if ($arquivo !== null && isset($arquivo['tmp_name']) && is_uploaded_file($arquivo['tmp_name'])) {
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
                $caminhoPublicoFoto = '/uploads/' . $novoNomeArquivo;

                if (!move_uploaded_file($tmpArquivo, $caminhoCompleto)) {
                    throw new Exception('Não foi possível salvar a imagem na pasta de uploads.');
                }

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

    public function exigirPermissaoGerenciarAluno(int $id): void
    {
        $idEscola = $this->obterEscolaDoAluno($id);
        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);

        if ($idEscola === null || !$this->vinculoEscolaUsuarioModel->usuarioPodeGerenciarEscola($idUsuario, $idEscola)) {
            throw new Exception('Você não tem permissão para gerenciar este aluno.');
        }
    }

    //Remove Aluno
    public function remover(int $id): void
    {
        $this->exigirPermissaoGerenciarAluno($id);
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

    private function resolverEscolaCadastro(array $dados): int
    {
        $papeis = $_SESSION['usuario']['papeis'] ?? [];
        if (in_array('ADM', $papeis, true)) {
            return (int) ($dados['escola'] ?? 0);
        }

        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);
        return (int) ($this->vinculoEscolaUsuarioModel->escolaGerenciavelPorUsuario($idUsuario) ?? 0);
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