<?php

class AuthContext
{
    private static ?array $usuario = null;

    public static function definir(array $usuario): void
    {
        self::$usuario = $usuario;
    }

    public static function usuario(): ?array
    {
        return self::$usuario;
    }

    public static function id(): ?int
    {
        return isset(self::$usuario['id'])
            ? (int) self::$usuario['id']
            : null;
    }

    public static function papeis(): array
    {
        $papeis = self::$usuario['papeis'] ?? [];

        return is_array($papeis) ? $papeis : [];
    }

    public static function possuiPapel(string $papel): bool
    {
        return in_array($papel, self::papeis(), true);
    }

    public static function autenticado(): bool
    {
        return self::id() !== null && self::id() > 0;
    }

    public static function limpar(): void
    {
        self::$usuario = null;
    }
}