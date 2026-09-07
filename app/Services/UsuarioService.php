<?php

class UsuarioService
{
    private PDO $pdo;

    private Usuario $usuarioModel;

    private Papel $papelModel;

    private VinculoUsuarioEscola $vinculoUsuarioEscolaModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->usuarioModel = new Usuario();

        $this->papelModel = new Papel();

        $this->vinculoUsuarioEscolaModel = new VinculoUsuarioEscola();
    }

    public function cadastrar(array $dados): int
    {
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

        try {

            $this->pdo->beginTransaction();

            // tratar upload de foto_perfil (opcional)
            $arquivo = $_FILES['foto_perfil'] ?? null;
            $caminhoPublicoFoto = null;

            if ($arquivo !== null && ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $nomeArquivo = $arquivo['name'];
                $tamanhoArquivo = (int) $arquivo['size'];
                $erroArquivo = (int) $arquivo['error'];
                $tmpArquivo = $arquivo['tmp_name'];

                if ($erroArquivo !== 0) {
                    throw new Exception('Erro durante o upload da imagem. Verifique o tamanho do arquivo e tente novamente.');
                }

                if (!is_uploaded_file($tmpArquivo)) {
                    throw new Exception('O arquivo enviado não é válido.');
                }

                if ($tamanhoArquivo > 2 * 1024 * 1024) {
                    throw new Exception('Arquivo muito grande. Tamanho máximo de 2MB.');
                }

                $tiposPermitidos = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];
                $tipoArquivo = (new finfo(FILEINFO_MIME_TYPE))->file($tmpArquivo);

                if (!isset($tiposPermitidos[$tipoArquivo])) {
                    throw new Exception('Tipo de arquivo inválido. Apenas JPG, PNG e WEBP são permitidos.');
                }

                $extensaoArquivo = $tiposPermitidos[$tipoArquivo];

                $pastaUploads = __DIR__ . '/../../public/uploads';

                if (!is_dir($pastaUploads) && !mkdir($pastaUploads, 0755, true) && !is_dir($pastaUploads)) {
                    throw new Exception('Não foi possível criar a pasta de uploads.');
                }

                if (!is_writable($pastaUploads)) {
                    throw new Exception('A pasta de uploads não possui permissão de escrita.');
                }

                $novoNomeArquivo = uniqid('IMG_', true) . '.' . $extensaoArquivo;
                $caminhoCompleto = $pastaUploads . DIRECTORY_SEPARATOR . $novoNomeArquivo;
                $caminhoPublicoFoto = '/uploads/' . $novoNomeArquivo;

                if (!move_uploaded_file($tmpArquivo, $caminhoCompleto)) {
                    throw new Exception('Não foi possível salvar a imagem na pasta de uploads.');
                }
            }

            $idUsuario = $this->usuarioModel->cadastrar([
                'nm_usuario' => $dados['nm_usuario'],
                'email' => $dados['email'],
                'senha' => $senhaHash,
                'foto_perfil' => $caminhoPublicoFoto
            ]);

            if (empty($dados['escola'])) {
                $papel = $this->papelModel->buscarPapelPorNome('VIS');

                if ($papel === null) {
                    throw new Exception('O papel de visitante não está cadastrado.');
                }

                $this->papelModel->vincularPapel(
                    $idUsuario,
                    (int) $papel['cd_papel']
                );
            } else {
                $this->vinculoUsuarioEscolaModel->vincularPapelEscola(
                    $idUsuario,
                    (int) $dados['escola'],
                    (int) $dados['papel']
                );
            }

            $this->pdo->commit();

            return $idUsuario;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}
