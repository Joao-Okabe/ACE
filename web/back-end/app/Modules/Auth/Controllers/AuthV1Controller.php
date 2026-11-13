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
        return is_array($dados) && json_last_error() === JSON_ERROR_NONE ? $dados : null;
    }

    public function login(): void
    {
        $dados = $this->corpoJson();
        if ($dados === null) {
            Response::error('BAD_REQUEST', 'O corpo da requisição contém JSON inválido.', 400);
        }

        try {
            $usuario = $this->service()->autenticar($dados);
            $tokens = $this->refreshService()->emitir((int) $usuario['id']);
            Response::success([
                ...$tokens,
                'user' => [
                    'id' => $usuario['id'],
                    'name' => $usuario['nm_usuario'],
                    'email' => $usuario['email'],
                    'profile_photo' => $usuario['foto_perfil'] ?? null,
                    'roles' => $usuario['papeis'],
                ],
            ], 'Autenticado com sucesso.');
        } catch (InvalidArgumentException $e) {
            Response::error('VALIDATION_ERROR', $e->getMessage(), 422);
        } catch (DomainException $e) {
            Response::error('UNAUTHENTICATED', 'E-mail ou senha inválidos.', 401);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Erro interno ao autenticar.', 500);
        }
    }

    public function refresh(): void
    {
        $dados = $this->corpoJson();
        if ($dados === null) {
            Response::error('BAD_REQUEST', 'O corpo da requisição contém JSON inválido.', 400);
        }
        $refreshToken = $dados['refresh_token'] ?? null;
        if (!is_string($refreshToken) || trim($refreshToken) === '') {
            Response::error('VALIDATION_ERROR', 'Informe um refresh_token válido.', 400);
        }

        try {
            Response::success($this->refreshService()->renovar($refreshToken), 'Token renovado.');
        } catch (DomainException $e) {
            Response::error('UNAUTHENTICATED', 'Refresh token inválido ou expirado.', 401);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível renovar a sessão.', 500);
        }
    }

    public function logout(): void
    {
        $dados = $this->corpoJson();
        if ($dados === null) {
            Response::error('BAD_REQUEST', 'O corpo da requisição contém JSON inválido.', 400);
        }
        $refreshToken = $dados['refresh_token'] ?? null;
        if (!is_string($refreshToken) || trim($refreshToken) === '') {
            Response::error('VALIDATION_ERROR', 'Informe um refresh_token válido.', 400);
        }

        try {
            $this->refreshService()->revogar($refreshToken);
            Response::success(null, 'Sessão encerrada com sucesso.');
        } catch (InvalidArgumentException $e) {
            Response::error('VALIDATION_ERROR', 'Informe um refresh_token válido.', 400);
        } catch (Throwable $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            Response::error('INTERNAL_ERROR', 'Não foi possível encerrar a sessão.', 500);
        }
    }

    public function me(): void
    {
        $usuario = AuthContext::usuario();
        if ($usuario === null) {
            Response::error('UNAUTHENTICATED', 'Autenticação necessária.', 401);
        }
        Response::success([
            'id' => $usuario['id'],
            'name' => $usuario['nome'] ?? $usuario['nm_usuario'] ?? null,
            'email' => $usuario['email'] ?? null,
            'profile_photo' => $usuario['foto_perfil'] ?? null,
            'roles' => $usuario['papeis'] ?? [],
        ]);
    }
}
