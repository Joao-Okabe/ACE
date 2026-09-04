<?php

class Permission
{
    public static function estaLogado(): bool
    {
        return true;
    }

    public static function temPapel(string $papel): bool
    {
        return in_array($papel, $_SESSION['usuario']['papeis'], true);
    }
}

/*

Verifica se tem permissão
if (!Permission::hasRole('ADMIN')) {
    exit('Acesso negado.');
}

Verifica se é dono do registro
if (!Permission::hasRole('ADMIN')) {

    if ($escola['usuario'] !== $_SESSION['usuario']['id']) {
        exit;
    }

}

*/