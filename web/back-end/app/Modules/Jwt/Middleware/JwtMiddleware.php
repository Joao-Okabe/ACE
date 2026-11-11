<?php

class JwtMiddleware
{
    public static function autenticar(): bool
    {
        AuthContext::limpar();

        $cabecalho = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (
            !is_string($cabecalho) ||
            !preg_match(
                '/^Bearer\s+([^\s]+)$/i',
                trim($cabecalho),
                $matches
            )
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Token de autenticação não informado.'
            ], 401);

            return false;
        }

        try {
            $jwtService = new JwtService();

            $payload = $jwtService->validar(
                trim($matches[1])
            );

            $idUsuario = (int) $payload->sub;

            $usuarioModel = new Usuario();
            $papelModel = new Papel();

            // A consulta já filtra usuários inativos.
            $usuario = $usuarioModel->buscar($idUsuario);

            if ($usuario === null) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Usuário inexistente ou inativo.'
                ], 401);

                return false;
            }

            // Os papéis são sempre consultados no banco.
            $papeis = $papelModel->listarPapeis($idUsuario);

            AuthContext::definir([
                'id' => (int) $usuario['cd_usuario'],
                'nome' => $usuario['nm_usuario'],
                'nm_usuario' => $usuario['nm_usuario'],
                'email' => $usuario['email'],
                'foto_perfil' => $usuario['path_ft_usuario'] ?? null,
                'papeis' => $papeis
            ]);

            return true;

        } catch (Throwable $e) {
            error_log(
                __METHOD__ . ': ' . $e->getMessage()
            );

            AuthContext::limpar();

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Token inválido ou expirado.'
            ], 401);

            return false;
        }
    }
}
