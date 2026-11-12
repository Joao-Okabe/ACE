<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private const TTL_MINIMO = 60;
    private const TTL_MAXIMO = 900;

    private string $secret;
    private string $issuer;
    private string $audience;
    private int $ttl;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? '';
        $this->issuer = $_ENV['JWT_ISSUER'] ?? 'ace-api';
        $this->audience = $_ENV['JWT_AUDIENCE'] ?? 'ace-mobile';

        $ttlConfigurado = (int) ($_ENV['JWT_TTL'] ?? 900);

        $this->ttl = min(
            self::TTL_MAXIMO,
            max(self::TTL_MINIMO, $ttlConfigurado)
        );

        if (strlen($this->secret) < 64) {
            throw new RuntimeException(
                'JWT_SECRET não configurado ou muito curto.'
            );
        }

        if ($this->issuer === '' || $this->audience === '') {
            throw new RuntimeException(
                'JWT_ISSUER e JWT_AUDIENCE devem ser configurados.'
            );
        }
    }

    public function gerar(int $idUsuario): string
    {
        if ($idUsuario <= 0) {
            throw new InvalidArgumentException(
                'ID de usuário inválido.'
            );
        }

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

        return JWT::encode(
            $payload,
            $this->secret,
            'HS256'
        );
    }

    public function validar(string $token): object
    {
        if ($token === '') {
            throw new UnexpectedValueException(
                'Token não informado.'
            );
        }

        $dados = JWT::decode(
            $token,
            new Key($this->secret, 'HS256')
        );

        $agora = time();

        $claimsObrigatorios = [
            'iss',
            'aud',
            'iat',
            'nbf',
            'exp',
            'sub',
            'jti'
        ];

        foreach ($claimsObrigatorios as $claim) {
            if (!property_exists($dados, $claim)) {
                throw new UnexpectedValueException(
                    "Claim obrigatório ausente: {$claim}."
                );
            }
        }

        if (
            !is_string($dados->iss) ||
            $dados->iss !== $this->issuer ||
            !is_string($dados->aud) ||
            $dados->aud !== $this->audience
        ) {
            throw new UnexpectedValueException(
                'Emissor ou audiência inválidos.'
            );
        }

        if (
            !is_int($dados->iat) ||
            !is_int($dados->nbf) ||
            !is_int($dados->exp)
        ) {
            throw new UnexpectedValueException(
                'Claims temporais inválidos.'
            );
        }

        if (
            $dados->iat > $agora ||
            $dados->nbf > $agora ||
            $dados->exp <= $agora ||
            $dados->exp <= $dados->iat
        ) {
            throw new UnexpectedValueException(
                'Token fora do período de validade.'
            );
        }

        // Impede access tokens com duração superior a 15 minutos,
        // inclusive tokens emitidos antes desta alteração.
        if (
            ($dados->exp - $dados->iat) > self::TTL_MAXIMO
        ) {
            throw new UnexpectedValueException(
                'Duração máxima do token excedida.'
            );
        }

        if (
            !is_string($dados->sub) ||
            !ctype_digit($dados->sub) ||
            (int) $dados->sub <= 0
        ) {
            throw new UnexpectedValueException(
                'Identificador do usuário inválido.'
            );
        }

        if (
            !is_string($dados->jti) ||
            !preg_match('/^[a-f0-9]{32}$/', $dados->jti)
        ) {
            throw new UnexpectedValueException(
                'Identificador do token inválido.'
            );
        }

        return $dados;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }
}
