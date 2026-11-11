<?php

class AuthV1Controller
{
    private ?AuthService $service = null;
    private ?RefreshTokenService $refreshService = null;

    private function service(): AuthService
    {
        return $this->service ??= new AuthService();
    }

    private function refreshService(): RefreshTokenService
    {
        return $this->refreshService ??= new RefreshTokenService();
    }

    private function corpoJson(): ?array
    {
        $dados = json_decode(file_get_contents('php://input'), true);

        return is_array($dados) && json_last_error() === JSON_ERROR_NONE
            ? $dados
            : null;
    }

    public function login(): void
    {
        $dados = $this->corpoJson();

        if ($dados === null) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'O corpo da requisição contém JSON inválido.'
            ], 400);
            return;
        }

        try {
            $usuario = $this->service()->autenticar($dados);
            $tokens = $this->refreshService()->emitir((int) $usuario['id']);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Autenticado com sucesso.',
                ...$tokens,
                'dados' => [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nm_usuario'],
                    'email' => $usuario['email'],
                    'foto_perfil' => $usuario['foto_perfil'] ?? null,
                    'papeis' => $usuario['papeis']
                ]
            ]);
        } catch (InvalidArgumentException $e) {
            Response::json(['sucesso' => false, 'mensagem' => $e->getMessage()], 422);
        } catch (DomainException $e) {
            Response::json(['sucesso' => false, 'mensagem' => $e->getMessage()], 401);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::json(['sucesso' => false, 'mensagem' => 'Erro interno ao autenticar.'], 500);
        }
    }

    public function refresh(): void
    {
        $dados = $this->corpoJson();

        $refreshToken = $dados['refresh_token'] ?? null;

        if (!is_string($refreshToken) || trim($refreshToken) === '') {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Informe um refresh_token válido.'
            ], 400);
            return;
        }

        try {
            $tokens = $this->refreshService()->renovar($refreshToken);

            Response::json([
                'sucesso' => true,
                ...$tokens
            ]);
        } catch (DomainException $e) {
            Response::json([
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ], 401);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());

            Response::json(
                [
                    'sucesso' => false,
                    'mensagem' => 'Não foi possível renovar a sessão.'
                ],
                500);
        }
    }

    public function logout(): void
    {
        $dados = $this->corpoJson();

        try {
            $refresh = is_array($dados) && is_string($dados['refresh_token'] ?? null)
                ? $dados['refresh_token']
                : null;

            $this->refreshService()->revogar($refresh);

            Response::json([
                'sucesso' => true,
                'mensagem' => 'Sessão encerrada. O refresh token foi revogado.'
            ]);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::json(['sucesso' => false, 'mensagem' => 'Não foi possível encerrar a sessão.'], 500);
        }
    }

    public function me(): void
    {
        $usuario = AuthContext::usuario();

        if ($usuario === null) {
            Response::json(['sucesso' => false, 'mensagem' => 'Não autenticado.'], 401);
            return;
        }

        Response::json(['sucesso' => true, 'dados' => $usuario]);
    }
}
