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
        if (trim((string) ($dados['nm_usuario'] ?? '')) === '') {
            throw new Exception('Informe o nome do usuário.');
        }

        $papeisSessao = $_SESSION['usuario']['papeis'] ?? [];
        $podeVincular = is_array($papeisSessao)
            && (in_array('ADM', $papeisSessao, true) || in_array('DIR', $papeisSessao, true));

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

            if (!$podeVincular) {
                $papel = $this->papelModel->buscarPapelPorNome('VIS');

                if ($papel === null) {
                    throw new Exception('O papel de visitante não está cadastrado.');
                }

                $this->papelModel->vincularPapel(
                    $idUsuario,
                    (int) $papel['cd_papel']
                );
            } else {
                $papelCodigo = strtoupper(trim((string) ($dados['papel'] ?? '')));
                if (!in_array($papelCodigo, ['ALU', 'PRF', 'AGR'], true)) {
                    throw new Exception('Selecione um papel válido.');
                }

                $idEscola = (int) ($dados['escola'] ?? 0);
                if (in_array('DIR', $papeisSessao, true) && !in_array('ADM', $papeisSessao, true)) {
                    $idEscolaDiretor = $this->vinculoUsuarioEscolaModel->escolaAtualPorPapeis(
                        (int) ($_SESSION['usuario']['id'] ?? 0),
                        ['DIR']
                    );
                    if ($idEscolaDiretor === null || $idEscola !== $idEscolaDiretor) {
                        throw new Exception('O diretor só pode vincular usuários à sua escola.');
                    }
                }

                if ($idEscola <= 0) {
                    throw new Exception('Selecione uma escola.');
                }

                $papel = $this->papelModel->buscarPapelPorNome($papelCodigo);
                if ($papel === null) {
                    throw new Exception('Papel não encontrado.');
                }

                $this->vinculoUsuarioEscolaModel->vincularPapelEscola(
                    $idUsuario,
                    $idEscola,
                    (int) $papel['cd_papel']
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

    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuario WHERE cd_usuario = :id');
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    public function atualizar(int $id, array $dados): void
    {
        $nome = trim((string) ($dados['nm_usuario'] ?? ''));
        $email = trim((string) ($dados['email'] ?? ''));

        if ($nome === '') {
            throw new Exception('Informe seu nome.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Informe um e-mail válido.');
        }

        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM usuario WHERE email = :email AND cd_usuario <> :id'
        );
        $stmt->execute([':email' => $email, ':id' => $id]);
        if ($stmt->fetchColumn() !== false) {
            throw new Exception('Já existe um usuário cadastrado com este e-mail.');
        }

        $senha = trim((string) ($dados['senha'] ?? ''));
        $senhaHash = $senha === '' ? null : password_hash($senha, PASSWORD_DEFAULT);
        $foto = $this->salvarFoto($dados['foto_perfil'] ?? null);

        $this->pdo->beginTransaction();
        try {
            $this->usuarioModel->atualizarPerfil($id, $nome, $email, $foto, $senhaHash);
            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
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