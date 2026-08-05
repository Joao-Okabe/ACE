<?php

class AuthService
{
    private Usuario $usuarioModel;

    private Papel $papel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();

        $this->papel = new Papel();

    }

    public function autenticar(array $dados): array
    {
        if (empty($dados['email'])) {
            throw new Exception("Informe o e-mail.");
        }

        if (empty($dados['senha'])) {
            throw new Exception("Informe a senha.");
        }

        $usuario = $this->usuarioModel->buscarPorEmail($dados['email']);

        if ($usuario === null || !password_verify($dados['senha'], $usuario['senha'])) {
            throw new Exception("E-mail ou senha inválidos.");
        }

        if (isset($usuario['ativo']) && $usuario['ativo'] === false) {
            throw new Exception("Usuário inativo.");
        }

        return [
            'id' => (int) $usuario['cd_usuario'],
            'email' => $usuario['email'],
            'papeis' => $this->papel->listarPapeis((int) $usuario['cd_usuario'])
        ];
    }
}