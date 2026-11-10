
<?php

class AuthV1Controller
{
    private ?AuthService $service = null;

    private function service(): AuthService
    {
        if ($this->service === null) {
            $this->service = new AuthService();
        }

        return $this->service;
    }

    public function login(): void
    {
        $corpo = file_get_contents('php://input');
        $dados = json_decode($corpo, true);

        if (!is_array($dados) || json_last_error() !== JSON_ERROR_NONE) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'O corpo da requisição contém JSON inválido.'
            ], 400);
            return;
        }

        try {
            $usuario = $this->service()->autenticar($dados);

            $jwtService = new JwtService();
            $token = $jwtService->gerar($usuario['id']);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Autenticado com sucesso.',
                'token_type' => 'Bearer',
                'access_token' => $token,
                'expires_in' => $jwtService->getTtl(),
                'dados' => [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nm_usuario'],
                    'email' => $usuario['email'],
                    'foto_perfil' => $usuario['foto_perfil'] ?? null,
                    'papeis' => $usuario['papeis']
                ]
            ]);

        } catch (InvalidArgumentException $e) {
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 422);

        } catch (DomainException $e) {
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 401);

        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Erro interno ao autenticar.'
            ], 500);
        }
    }

    public function me(): void
    {
        $usuario = AuthContext::usuario();
        $idUsuario = AuthContext::id();
        $papeis = AuthContext::papeis();

        if ($usuario === null) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Não autenticado.'
            ], 401);
            return;
        }

        Response::json([
            'sucesso' => true,
            'dados' => $usuario
        ]);
    }
}
