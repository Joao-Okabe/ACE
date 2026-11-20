<?php

class NormalizadorCampo
{
    public function normalizarCampoNulo(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim($valor);

        return $valor === '' ? null : $valor;
    }
}