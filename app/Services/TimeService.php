<?php

class TimeService 
{
    private PDO $pdo;

    private Time $timeModel;

    private VinculoUsuarioEscola $vinculoModel;
    
    private VinculoUsuarioEscolaService $vinculoUsuarioEscolaService;
    
    private Permissoes $permissoes;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->timeModel = new Time();
        $this->vinculoModel = new VinculoUsuarioEscola();
        $this->permissoes = new Permissoes();
    }

    public function cadastrar(array $dados): void
    {

        try {
            $this->pdo->beginTransaction();

            if (empty($dados['cd_esporte'])) {
                throw new Exception('Selecione o esporte do time.');
            }

            $idEscola = $this->permissoes->resolverEscolaCadastro($dados);
            if ($idEscola <= 0) {
                throw new Exception('Selecione a escola do time.');
            }

            $caminhoPublicoFoto = $this->salvarEscudo($dados['path_escudo']);
            $idTime = $this->timeModel->criar([
                'nm_time' => $dados['nm_time'],
                'cd_esporte' => $dados['cd_esporte'],
                'path_escudo' => $caminhoPublicoFoto,
            ]);
            $this->vinculoModel->vincularTimeEscola($idTime, $idEscola);

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
        return $this->timeModel->listar($filtros);
    }

    public function buscar(int $id): array
    {
        $time = $this->timeModel->buscar($id);

        if ($time === null) {
            throw new Exception('Time não encontrado.');
        }

        return $time;
    }

    public function atualizar(int $id, array $dados): void
    {
        $nome = trim((string) ($dados['nm_time'] ?? ''));
        if ($nome === '') {
            throw new Exception('Informe o nome do time.');
        }

        $time = $this->buscar($id);
        $novoEscudo = $this->salvarEscudo($dados['path_escudo'] ?? null);

        try {
            $this->timeModel->atualizar($id, [
                'nm_time' => $nome,
                'path_escudo' => $novoEscudo ?? $time['path_escudo'],
            ]);
        } catch (Throwable $e) {
            if ($novoEscudo !== null) {
                unlink(__DIR__ . '/../../public' . $novoEscudo);
            }
            throw $e;
        }
    }

    private function salvarEscudo(?array $arquivo): ?string
    {
        if ($arquivo === null || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new Exception('Erro durante o upload da imagem. Verifique o tamanho do arquivo e tente novamente.');
        }

        $tmpArquivo = $arquivo['tmp_name'] ?? '';
        if (!is_uploaded_file($tmpArquivo)) {
            throw new Exception('O arquivo enviado não é válido.');
        }

        $extensoesPorTipo = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $tipo = mime_content_type($tmpArquivo);
        if (!isset($extensoesPorTipo[$tipo]) || getimagesize($tmpArquivo) === false) {
            throw new Exception('Tipo de arquivo inválido. Apenas JPG, PNG e WEBP são permitidos.');
        }
        $extensao = $extensoesPorTipo[$tipo];

        if ((int) ($arquivo['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new Exception('Arquivo muito grande. Tamanho máximo de 2MB.');
        }

        $pastaUploads = __DIR__ . '/../../public/uploads';
        if (!is_dir($pastaUploads) && !mkdir($pastaUploads, 0755, true) && !is_dir($pastaUploads)) {
            throw new Exception('Não foi possível criar a pasta de uploads.');
        }

        $nome = uniqid('IMG_', true) . '.' . $extensao;
        if (!move_uploaded_file($tmpArquivo, $pastaUploads . DIRECTORY_SEPARATOR . $nome)) {
            throw new Exception('Não foi possível salvar a imagem na pasta de uploads.');
        }

        return '/uploads/' . $nome;
    }

    public function remover(int $id)
    {
        return $this->timeModel->remover($id);
    }

}