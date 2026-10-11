<?php

class EscolaValidator
{
    public function validarCadastroEscola(array $dados)
    {
        $this->validarNome($dados);
        $this->validarEmail($dados);
        $this->validarSenha($dados);
        $this->validarCategoriaAdministrativa($dados);
    }

    private function validarNome(array $dados): void
    {
        if (empty(trim($dados['nome'] ?? ''))) {
            throw new InvalidArgumentException(
                'Informe o nome do(a) aluno(a).'
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

    private function validarCategoriaAdministrativa(array $dados): void
    {
        if (!in_array($dados['categoria_administrativa'], ['Escola Municipal', 'Escola Estadual', 'Privada'], true ?? '')) {
            throw new InvalidArgumentException(
                'Categoria administrativa inválida..'
            );
        } else { 
            if (empty($dados['categoria_administrativa'] ?? '')) {
            throw new InvalidArgumentException(
                'Informe uma Categoria Administrativa.'
            );
        }
        }
    }
}