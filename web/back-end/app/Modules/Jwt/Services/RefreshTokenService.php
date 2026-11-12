<?php

class RefreshTokenService
{
    private const TTL_MINIMO = 86400;
    private const TTL_MAXIMO = 604800;

    private RefreshTokenModel $model;
    private PDO $pdo;
    private JwtService $jwt;
    private int $ttl;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->model = new RefreshTokenModel($this->pdo);
        $this->jwt = new JwtService();

        $ttlConfigurado = (int) ($_ENV['JWT_REFRESH_TTL'] ?? 604800);

        $this->ttl = min(
            self::TTL_MAXIMO,
            max(self::TTL_MINIMO, $ttlConfigurado)
        );
    }

    public function emitir(int $idUsuario): array
    {
        if ($idUsuario <= 0) {
            throw new InvalidArgumentException('Usuário inválido.');
        }

        $refresh = $this->novoToken();
        $familia = $this->uuid();

        $this->model->criar(
            $idUsuario,
            hash('sha256', $refresh),
            $familia,
            $this->dataExpiracao()
        );

        return $this->resposta($idUsuario, $refresh);
    }

    public function renovar(string $refreshToken): array
    {
        if (!$this->formatoValido($refreshToken)) {
            throw new DomainException(
                'Refresh token inválido ou expirado.'
            );
        }

        $hash = hash('sha256', $refreshToken);

        $this->pdo->beginTransaction();

        try {
            /*
             * 1. Busca inicial sem bloqueio para descobrir
             *    a família à qual o token pertence.
             */
            $inicial = $this->model->buscarPorHash($hash);

            if ($inicial === null) {
                $this->pdo->commit();

                throw new DomainException(
                    'Refresh token inválido ou expirado.'
                );
            }

            /*
             * 2. Bloqueia a família.
             *    Operações concorrentes da mesma família
             *    precisam aguardar a liberação deste bloqueio.
             */
            $this->model->bloquearFamilia($inicial['familia']);

            /*
             * 3. Busca novamente o registro, agora com
             *    bloqueio de linha.
             */
            $registro = $this->model
                ->buscarPorHashComBloqueio($hash);

            if ($registro === null) {
                $this->pdo->commit();

                throw new DomainException(
                    'Refresh token inválido ou expirado.'
                );
            }

            /*
             * 4. Detecta reutilização.
             *    A família inteira é revogada.
             */
            if ($registro['revogado_em'] !== null) {
                $this->model->revogarFamilia(
                    $registro['familia']
                );

                $this->pdo->commit();

                throw new DomainException(
                    'Refresh token reutilizado. Entre novamente.'
                );
            }

            /*
             * 5. Verifica expiração.
             */
            if (strtotime($registro['expira_em']) <= time()) {
                $this->model->revogarPorHash($hash);

                $this->pdo->commit();

                throw new DomainException(
                    'Refresh token inválido ou expirado.'
                );
            }

            /*
             * 6. Gera e registra o novo refresh token.
             */
            $novoRefresh = $this->novoToken();
            $novoHash = hash('sha256', $novoRefresh);

            $this->model->rotacionar(
                (int) $registro['cd_refresh_token'],
                $novoHash
            );

            $this->model->criar(
                (int) $registro['cd_usuario'],
                $novoHash,
                $registro['familia'],
                $this->dataExpiracao()
            );

            /*
             * 7. Gera a resposta antes do commit.
             *    Se a geração do JWT falhar, a transação
             *    será revertida e o refresh anterior continuará válido.
             */
            $resposta = $this->resposta(
                (int) $registro['cd_usuario'],
                $novoRefresh
            );

            $this->pdo->commit();

            return $resposta;

        } catch (DomainException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function revogar(string $refreshToken): void
    {
        if (!$this->formatoValido($refreshToken)) {
            throw new InvalidArgumentException(
                'Refresh token inválido.'
            );
        }

        $hash = hash('sha256', $refreshToken);

        $this->pdo->beginTransaction();

        try {
            /*
             * 1. Busca inicial para descobrir a família.
             */
            $inicial = $this->model->buscarPorHash($hash);

            if ($inicial === null) {
                $this->pdo->commit();
                return;
            }

            /*
             * 2. Bloqueia a família antes de bloquear registros.
             */
            $this->model->bloquearFamilia($inicial['familia']);

            /*
             * 3. Confirma o estado atual do token.
             */
            $registro = $this->model
                ->buscarPorHashComBloqueio($hash);

            if ($registro === null) {
                $this->pdo->commit();
                return;
            }

            /*
             * 4. Revoga todos os tokens da família.
             */
            $this->model->revogarFamilia(
                $registro['familia']
            );

            $this->pdo->commit();

        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    private function resposta(
        int $idUsuario,
        string $refreshToken
    ): array {
        return [
            'token_type' => 'Bearer',
            'access_token' => $this->jwt->gerar($idUsuario),
            'expires_in' => $this->jwt->getTtl(),
            'refresh_token' => $refreshToken,
            'refresh_expires_in' => $this->ttl,
        ];
    }

    private function novoToken(): string
    {
        return rtrim(
            strtr(base64_encode(random_bytes(64)), '+/', '-_'),
            '='
        );
    }

    private function formatoValido(string $token): bool
    {
        return strlen($token) >= 80
            && strlen($token) <= 200
            && preg_match('/^[A-Za-z0-9_-]+$/', $token) === 1;
    }

    private function dataExpiracao(): string
    {
        return gmdate('Y-m-d H:i:sP', time() + $this->ttl);
    }

    private function uuid(): string
    {
        $bytes = random_bytes(16);

        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }
}
