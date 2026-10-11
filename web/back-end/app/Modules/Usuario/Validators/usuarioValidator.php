<?php

class UsuarioValidator 
{
    public function validarCadastroUsuario(array $dados): void
    {
        $this->validarNome($dados);
        $this->validarEmail($dados);
        $this->validarSenha($dados);
    }

    private function validarNome(array $dados): void
    {
        if (empty(trim($dados['nm_usuario'] ?? ''))) {
            throw new InvalidArgumentException(
                'Informe o nome do(a) usuário(a).'
            );
        }
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