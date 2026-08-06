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

    public function listar(): array
    {
        return $this->alunoModel->listar();
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
        $this->alunoModel->atualizar($id, $dados);
    }

    //Remove Aluno
    public function remover(int $id): void
    {
        $this->alunoModel->remover($id);
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