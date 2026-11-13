<?php
/*
    autenticar();
        ->  realiza login e salva os dados em $_SESSION
*/
class AuthService
{
    private Auth $authModel;
    private Usuario $usuarioModel;
    private Papel $papel;
    private AuthValidator $authValidator;

    public function __construct()
    {
        $this->authModel = new Auth();
        $this->usuarioModel = new Usuario();
        $this->papel = new Papel();
        $this->authValidator = new AuthValidator();
    }

    public function autenticar(array $dados): array
    {
        $this->authValidator->validarAutenticacao($dados);

        $usuario = $this->usuarioModel->buscarPorEmail($dados['email']);

        // Mensagem genérica para não revelar se o e-mail existe.
        $mensagemCredenciais = 'E-mail ou senha inválidos.';

        if ($usuario === null) {
            // Consumir tempo comparando com um hash fictício,
            // para mitigar ataques de enumeração por tempo de resposta.
            password_verify($dados['senha'],
                '$2y$10$dummyhashdummyhashdummyhashdummyhashdummyhashdummy');
            throw new DomainException($mensagemCredenciais);
        }

        // Bloqueia usuários inativos antes de validar a senha,
        // cobrindo também o caso de senha nula/vazia no banco.
        if (!isset($usuario['ativo']) || (bool) $usuario['ativo'] !== true) {
            throw new DomainException($mensagemCredenciais);
        }

        if (
            empty($usuario['senha'])
            || !password_verify($dados['senha'], $usuario['senha'])
        ) {
            throw new DomainException($mensagemCredenciais);
        }

        $this->authModel->verificaPapelUsuario($usuario['cd_usuario']);        

        return [
            'id' => (int) $usuario['cd_usuario'],
            'nm_usuario' => $usuario['nm_usuario'],
            'email' => $usuario['email'],
            'foto_perfil' => $usuario['foto_perfil'] ?? null,
            'papeis' => $this->papel->listarPapeis((int) $usuario['cd_usuario'])
        ];
    }
}
