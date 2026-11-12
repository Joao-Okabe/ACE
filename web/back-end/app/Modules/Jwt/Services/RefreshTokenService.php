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
            $registro = $this->model->buscarPorHashComBloqueio($hash);

            if ($registro === null) {
                $this->pdo->commit();

                throw new DomainException(
                    'Refresh token inválido ou expirado.'
                );
            }

            if ($registro['revogado_em'] !== null) {
                $this->model->revogarFamilia($registro['familia']);

                $this->pdo->commit();

                throw new DomainException(
                    'Refresh token reutilizado. Entre novamente.'
                );
            }

            if (strtotime($registro['expira_em']) <= time()) {
                $this->model->revogarPorHash($hash);

                $this->pdo->commit();

                throw new DomainException(
                    'Refresh token inválido ou expirado.'
                );
            }

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

            $this->pdo->commit();

            return $this->resposta(
                (int) $registro['cd_usuario'],
                $novoRefresh
            );

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
             * O bloqueio serializa o logout com a renovação
             * que utiliza o mesmo refresh token.
             */
            $registro = $this->model->buscarPorHashComBloqueio($hash);

            if ($registro === null) {
                // Logout idempotente para tokens desconhecidos.
                $this->pdo->commit();
                return;
            }

            /*
             * Revoga todos os tokens da família, incluindo
             * um possível token filho criado por uma renovação.
             */
            $this->model->revogarFamilia($registro['familia']);

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
