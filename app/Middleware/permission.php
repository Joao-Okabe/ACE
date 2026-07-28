<?php

class Permission
{
    public static function hasRole(string $role): bool
    {
        return in_array($role, $_SESSION['usuario']['papeis'], true);
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