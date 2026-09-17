<?php

class TimeService 
{
    private PDO $pdo;

    private Time $timeModel;

    private VinculoUsuarioEscola $vinculoModel;

    public function __construct()
    {
        $this->pdo = Database::connect();

        $this->timeModel = new Time();
        $this->vinculoModel = new VinculoUsuarioEscola();
    }

    public function cadastrar(array $dados): void
    {

        try {
            $this->pdo->beginTransaction();

            $idEscola = $this->resolverEscolaCadastro($dados);
            if ($idEscola <= 0) {
                throw new Exception('Selecione a escola do time.');
            }

            // tratar upload de foto_perfil (opcional)
            $arquivo = $_FILES['path_brasao'] ?? null;
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

            $idTime = $this->timeModel->criar([
                'nm_time' => $dados['nm_time'],
                'path_brasao' => $caminhoPublicoFoto
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

    private function resolverEscolaCadastro(array $dados): int
    {
        $papeis = $_SESSION['usuario']['papeis'] ?? [];
        if (in_array('ADM', $papeis, true)) {
            return (int) ($dados['escola'] ?? 0);
        }

        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);
        return (int) ($this->vinculoModel->escolaAtualPorPapeis($idUsuario, ['DIR', 'CRD']) ?? 0);
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
        $this->timeModel->atualizar($id, [
            'nm_time' => $nome,
            'path_brasao' => $dados['path_brasao'] ?? $time['path_brasao'],
        ]);
    }

    public function remover(int $id)
    {
        return $this->timeModel->remover($id);
    }


}