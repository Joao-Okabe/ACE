<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $secret;
    private string $issuer;
    private string $audience;
    private int $ttl;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? '';
        $this->issuer = $_ENV['JWT_ISSUER'] ?? 'ace-api';
        $this->audience = $_ENV['JWT_AUDIENCE'] ?? 'ace-mobile';
        $this->ttl = max(60, (int) ($_ENV['JWT_TTL'] ?? 900));

        if (strlen($this->secret) < 64) {
            throw new RuntimeException(
                'JWT_SECRET não configurado ou muito curto.'
            );
        }
    }

    public function gerar(int $idUsuario): string
    {
        $agora = time();

        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $agora,
            'nbf' => $agora,
            'exp' => $agora + $this->ttl,
            'sub' => (string) $idUsuario,
            'jti' => bin2hex(random_bytes(16))
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validar(string $token): object
    {
        $dados = JWT::decode(
            $token,
            new Key($this->secret, 'HS256')
        );

        if (
            ($dados->iss ?? null) !== $this->issuer ||
            ($dados->aud ?? null) !== $this->audience ||
            !isset($dados->sub) ||
            !ctype_digit((string) $dados->sub)
        ) {
            throw new UnexpectedValueException('Token inválido.');
        }

        return $dados;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }
}