<?php

class AuthValidator
{
    public function validarAutenticacao(array $dados)
    {
        $this->validarEmail($dados);
        $this->validarSenha($dados);
    }

    private function validarEmail(array $dados): void
    {
        if (
            empty($dados['email']) ||
            !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)
        ) {
            throw new InvalidArgumentException(
                'Informe um e-mail válido.'
            );
        }
    }

    private function validarSenha(array $dados): void
    {
        if (empty($dados['senha'] ?? '')) {
            throw new InvalidArgumentException(
                'Informe uma senha.'
            );
        }
    }
}