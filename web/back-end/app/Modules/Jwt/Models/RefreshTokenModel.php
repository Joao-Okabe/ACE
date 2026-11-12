<?php

class RefreshTokenModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function criar(
        int $idUsuario,
        string $hash,
        string $familia,
        string $expiraEm
    ): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO refresh_token
                (cd_usuario, token_hash, familia, expira_em)
            VALUES
                (:usuario, :hash, :familia, :expira)
        ");

        $stmt->execute([
            ':usuario' => $idUsuario,
            ':hash' => $hash,
            ':familia' => $familia,
            ':expira' => $expiraEm,
        ]);
    }

    /**
     * Busca o token sem bloquear a linha.
     * Utilizado inicialmente para descobrir a família.
     */
    public function buscarPorHash(string $hash): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                cd_refresh_token,
                cd_usuario,
                token_hash,
                familia,
                expira_em,
                revogado_em
            FROM refresh_token
            WHERE token_hash = :hash
        ");

        $stmt->execute([':hash' => $hash]);

        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    /**
     * Bloqueia todas as operações cooperantes da mesma família
     * durante a transação atual.
     */
    public function bloquearFamilia(string $familia): void
    {
        $stmt = $this->pdo->prepare("
            SELECT pg_advisory_xact_lock(
                hashtextextended(:familia, 0)
            )
        ");

        $stmt->execute([
            ':familia' => $familia,
        ]);
    }

    /**
     * Busca e bloqueia a linha do refresh token.
     * Deve ser chamado somente depois de bloquear a família.
     */
    public function buscarPorHashComBloqueio(string $hash): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                cd_refresh_token,
                cd_usuario,
                token_hash,
                familia,
                expira_em,
                revogado_em
            FROM refresh_token
            WHERE token_hash = :hash
            FOR UPDATE
        ");

        $stmt->execute([':hash' => $hash]);

        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        return $registro ?: null;
    }

    public function rotacionar(int $id, string $novoHash): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE refresh_token
            SET revogado_em = CURRENT_TIMESTAMP,
                usado_em = CURRENT_TIMESTAMP,
                substituido_por_hash = :novo_hash
            WHERE cd_refresh_token = :id
              AND revogado_em IS NULL
        ");

        $stmt->execute([
            ':novo_hash' => $novoHash,
            ':id' => $id,
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Não foi possível rotacionar o refresh token.'
            );
        }
    }

    public function revogarFamilia(string $familia): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE refresh_token
            SET revogado_em = COALESCE(
                revogado_em,
                CURRENT_TIMESTAMP
            )
            WHERE familia = :familia
        ");

        $stmt->execute([
            ':familia' => $familia,
        ]);
    }

    public function revogarPorHash(string $hash): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE refresh_token
            SET revogado_em = COALESCE(
                revogado_em,
                CURRENT_TIMESTAMP
            )
            WHERE token_hash = :hash
        ");

        $stmt->execute([
            ':hash' => $hash,
        ]);
    }
}
