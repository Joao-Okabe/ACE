<?php

class Permissoes
{
    public static function estaLogado(): bool
    {
        return true;
    }

    public static function temPapelNecessario(string $papel): bool
    {
        return in_array($papel, $_SESSION['usuario']['papeis'], true);

        // puxar o papel do usuario
        
        // conferir, caso ele possua, qual o seu papel na escola do usuario atual

        // switch que puxa o nivelDeAcesso com o valor de temPapelNecessário

        switch ($papel) {
             
            case "ADM":
                
                break;

            case "DIR":
                
                break;

            case "CRD":
                
                break;

            case "PRF":
                
                break;

            case "ARB":
                
                break;

            case "AGR":
                
                break;

            case "ALU":
                
                break;

            case "VIS":
                
                break;
        }

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