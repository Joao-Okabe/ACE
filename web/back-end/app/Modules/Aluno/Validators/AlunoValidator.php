<?php 

class AlunoValidator
{
    public function validarCadastroAluno(array $dados): void
    {
        $this->validarNome($dados);
        $this->validarRa($dados);
        $this->validarEmail($dados);
        $this->validarSenha($dados);
        $this->validarDataNascimento($dados);
        $this->validarSexo($dados);
        $this->validarTelefone($dados);
        $this->validarCep($dados);
    }

    public function validarAtualizacaoAluno(array $dados): void
    {
        // Na atualização a senha não é alterada por este fluxo;
        // os demais campos seguem as mesmas regras do cadastro.
        $this->validarNome($dados);
        $this->validarRa($dados);
        $this->validarDataNascimento($dados);
        $this->validarSexo($dados);
        $this->validarTelefone($dados);
        $this->validarCep($dados);
    }

    private function validarNome(array $dados): void
    {
        $nome = trim((string) ($dados['nome'] ?? ''));

        if ($nome === '') {
            throw new InvalidArgumentException(
                'Informe o nome do(a) aluno(a).'
            );
        }

        if (mb_strlen($nome) > 150) {
            throw new InvalidArgumentException(
                'O nome deve ter no máximo 150 caracteres.'
            );
        }
    }

    public function validarRa(array $dados): void
    {
        $ra = trim((string) ($dados['ra'] ?? ''));
        if ($ra === '' || mb_strlen($ra) > 20) {
            throw new InvalidArgumentException(
                'Informe um RA com até 20 caracteres.'
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
                'A senha deve ter no mínimo 8 caracteres.'
            );
        }
    }

    private function validarDataNascimento(array $dados): void
    {
        $data = trim((string) ($dados['data_nascimento'] ?? ''));

        if ($data === '') {
            throw new InvalidArgumentException(
                'Informe a data de nascimento.'
            );
        }

        $info = date_parse_from_format('Y-m-d', $data);
        if (
            $info === false
            || $info['error_count'] > 0
            || $info['warning_count'] > 0
            || !checkdate((int) $info['month'], (int) $info['day'], (int) $info['year'])
        ) {
            throw new InvalidArgumentException(
                'Informe uma data de nascimento válida (AAAA-MM-DD).'
            );
        }

        // Não permitir datas no futuro nem pessoas com mais de 120 anos.
        $timestamp = strtotime($data);
        $hoje = strtotime('today');
        $limite = strtotime('-120 years', $hoje);

        if ($timestamp === false || $timestamp > $hoje || $timestamp < $limite) {
            throw new InvalidArgumentException(
                'Informe uma data de nascimento plausível.'
            );
        }
    }

    private function validarSexo(array $dados): void
    {
        $sexo = strtoupper(trim((string) ($dados['sexo'] ?? '')));

        if ($sexo === '') {
            return; // Campo opcional.
        }

        if (!in_array($sexo, ['M', 'F', 'O'], true)) {
            throw new InvalidArgumentException(
                'Sexo inválido. Valores aceitos: M, F ou O.'
            );
        }
    }

    private function validarTelefone(array $dados): void
    {
        $telefone = preg_replace('/\D/', '', (string) ($dados['telefone'] ?? ''));

        if ($telefone === '' || $telefone === null) {
            return; // Campo opcional.
        }

        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            throw new InvalidArgumentException(
                'Informe um telefone válido com DDD (10 ou 11 dígitos).'
            );
        }
    }

    private function validarCep(array $dados): void
    {
        $cep = preg_replace('/\D/', '', (string) ($dados['cep'] ?? ''));

        if ($cep === '' || $cep === null) {
            return; // Campo opcional.
        }

        if (strlen($cep) !== 8) {
            throw new InvalidArgumentException(
                'Informe um CEP válido com 8 dígitos.'
            );
        }
    }
}
