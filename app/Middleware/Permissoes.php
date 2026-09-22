<?php

class Permissoes
{

    public static function estaLogado(): bool
    {
        return true;
    }

    public static function temPapel(string $papel): bool
    {
        $papeis = $_SESSION['usuario']['papeis'] ?? [];

        if (!is_array($papeis)) {
            return false;
        }

        return in_array($papel, $papeis, true);
    }

    public static function temPapelAdm(int $idUsuario): bool
    {
        $idUsuarioSessao = (int) ($_SESSION['usuario']['id'] ?? 0);

        if ($idUsuario <= 0 || $idUsuario !== $idUsuarioSessao) {
            return false;
        }

        return self::temPapel('ADM');
    }

    public static function temPapelDiretor(string $papel): bool
    {
        return self::temPapel($papel);
    }

    public static function temPapelCoordenador(string $papel): bool
    {
        return self::temPapel($papel);
    }

    public static function temPapelProfessor(string $papel): bool
    {
        return self::temPapel($papel);
    }

    public static function temPapelGremista(string $papel): bool
    {
        return self::temPapel($papel);
    }

    public static function temPapelAluno(string $papel): bool
    {
        return self::temPapel($papel);
    }

    public static function temPapelVisitante(string $papel): bool
    {
        return self::temPapel($papel);
    }

} 