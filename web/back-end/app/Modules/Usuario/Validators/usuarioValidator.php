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
        $nome = trim((string) ($dados['nm_usuario'] ?? ''));

        if ($nome === '') {
            throw new InvalidArgumentException(
                'Informe o nome do(a) usuário(a).'
            );
        }

        if (mb_strlen($nome) < 3) {
            throw new InvalidArgumentException(
                'Digite seu nome corretamente.'
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
        $senha = (string) ($dados['senha'] ?? '');

        if ($senha === '') {
            throw new InvalidArgumentException(
                'Informe uma senha.'
            );
        }

        if (mb_strlen($senha) < 8) {
            throw new InvalidArgumentException(
                'A senha deve possuir pelo menos 8 caracteres.'
            );
        }
    }
}